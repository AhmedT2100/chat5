<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Core\CSRF;
use App\Models\Reclamation;

class ReclamationController extends Controller {
    private $model;

    public function __construct() {
        $this->model = new Reclamation();
        if (!session_id()) session_start();
    }

    // FrontOffice - list user's reclamations (for regular users)
    public function index() {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) { header('Location: /greentechhub/public/index.php'); exit; }

        $recs = $this->model->allByUser((int)$userId);
        $this->view('reclamation/index.php', ['recs' => $recs]);
    }

    public function create(array $old = [], array $errors = []) {
        if (($_SESSION['role'] ?? '') === 'admin') {
            header('Location: /greentechhub/public/index.php?route=reclamation');
            exit;
        }

        $token = CSRF::generate();

        $siteKey = getenv('RECAPTCHA_SITEKEY') ?: (defined('RECAPTCHA_SITEKEY') ? RECAPTCHA_SITEKEY : '');

        $this->view('reclamation/create.php', [
            '_csrf' => $token,
            'old' => $old,
            'errors' => $errors,
            'siteKey' => $siteKey
        ]);
    }

    public function store() {
        if (($_SESSION['role'] ?? '') === 'admin') {
            header('Location: /greentechhub/public/index.php?route=reclamation');
            exit;
        }

        if (!session_id()) session_start();

        $validator = new Validator();
        $old = [
            'sujet' => $_POST['sujet'] ?? '',
            'description' => $_POST['description'] ?? '',
            'full_name' => $_POST['full_name'] ?? '',
            'mobile_phone' => $_POST['mobile_phone'] ?? '',
            'priority' => $_POST['priority'] ?? 'normal'
        ];

        $validator->required('sujet', $old['sujet'], 3);
        $validator->required('description', $old['description'], 10);
        $validator->required('full_name', $old['full_name'], 2);

        if (!CSRF::validate($_POST['_csrf'] ?? null)) {
            $errors = array_merge($validator->getErrors(), ['csrf' => 'Requête invalide.']);
            $this->create($old, $errors);
            return;
        }

        $recaptchaToken = trim((string)($_POST['g-recaptcha-response'] ?? ''));
        $secret = getenv('RECAPTCHA_SECRET') ?: (defined('RECAPTCHA_SECRET') ? RECAPTCHA_SECRET : '');

        $recapData = null;
        $recapSuccess = false;

        if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1']) &&
            (isset($_POST['dev_bypass_recaptcha']) && $_POST['dev_bypass_recaptcha'] === '1')
        ) {
            error_log('reCAPTCHA dev bypass used from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $recapSuccess = true;
        }

        if (!$recapSuccess) {
            if (!empty($recaptchaToken) && !empty($secret)) {
                $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
                $postData = http_build_query([
                    'secret' => $secret,
                    'response' => $recaptchaToken,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);

                if (function_exists('curl_version')) {
                    $ch = curl_init($verifyUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    $resp = curl_exec($ch);
                    $curlErr = curl_error($ch);
                    curl_close($ch);
                    if ($resp === false) {
                        error_log('reCAPTCHA curl error: ' . $curlErr);
                    } else {
                        $recapData = json_decode($resp, true);
                    }
                } else {
                    $opts = ['http' => [
                        'method' => 'POST',
                        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                        'content' => $postData,
                        'timeout' => 5
                    ]];
                    $context = stream_context_create($opts);
                    $resp = @file_get_contents($verifyUrl, false, $context);
                    if ($resp === false) {
                        error_log('reCAPTCHA file_get_contents failed (allow_url_fopen?)');
                    } else {
                        $recapData = json_decode($resp, true);
                    }
                }

                error_log('reCAPTCHA token length: ' . strlen($recaptchaToken));
                error_log('reCAPTCHA raw response: ' . ($resp ?? 'NULL'));
                error_log('reCAPTCHA decoded: ' . print_r($recapData, true));

                if (is_array($recapData) && !empty($recapData['success'])) {
                    $recapSuccess = true;
                } else {
                    $recapSuccess = false;
                }
            } else {
                error_log('reCAPTCHA verification skipped: token or secret empty. token_empty=' . (empty($recaptchaToken) ? '1' : '0') . ' secret_empty=' . (empty($secret) ? '1' : '0'));
                $recapSuccess = false;
            }
        }

        if (!$recapSuccess) {
            $msg = 'Échec du reCAPTCHA. Veuillez réessayer.';
            if (is_array($recapData) && !empty($recapData['error-codes'])) {
                $msg .= ' (' . implode(', ', $recapData['error-codes']) . ')';
                error_log('reCAPTCHA verification failed: ' . implode(', ', $recapData['error-codes']));
            } else {
                error_log('reCAPTCHA verification failed: unknown reason.');
            }
            $errors = array_merge($validator->getErrors(), ['captcha' => $msg]);
            $this->create($old, $errors);
            return;
        }

        if ($validator->hasErrors()) {
            $this->create($old, $validator->getErrors());
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) { header('Location: /greentechhub/public/index.php'); exit; }

        $this->model->create(
            (int)$userId,
            htmlspecialchars($old['sujet']),
            htmlspecialchars($old['description']),
            htmlspecialchars($old['full_name']),
            htmlspecialchars($old['mobile_phone']),
            in_array($old['priority'], ['low','normal','high']) ? $old['priority'] : 'normal'
        );

        header('Location: /greentechhub/public/index.php?route=reclamation&created=1');
        exit;
    }

    public function show(int $id) {
        $rec = $this->model->find($id);
        $token = \App\Core\CSRF::generate();
        $this->view('reclamation/show.php', ['rec' => $rec, '_csrf' => $token]);
    }

    public function edit(int $id) {
        $rec = $this->model->find($id);
        if (!$rec) { header('Location: /greentechhub/public/index.php?route=reclamation'); exit; }
        $userId = $_SESSION['user_id'] ?? null;
        if ($rec['user_id'] != $userId && ($_SESSION['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); exit; }
        $this->view('reclamation/edit.php', ['rec' => $rec, '_csrf' => CSRF::generate()]);
    }

    public function update(int $id) {
        $rec = $this->model->find($id);
        if (!$rec) { header('Location: /greentechhub/public/index.php?route=reclamation'); exit; }
        $userId = $_SESSION['user_id'] ?? null;
        if ($rec['user_id'] != $userId && ($_SESSION['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); exit; }

        $validator = new Validator();
        $sujet = $_POST['sujet'] ?? '';
        $description = $_POST['description'] ?? '';
        $full_name = $_POST['full_name'] ?? '';
        $mobile_phone = $_POST['mobile_phone'] ?? '';
        $priority = $_POST['priority'] ?? 'normal';

        $validator->required('sujet', $sujet, 3);
        $validator->required('description', $description, 10);
        $validator->required('full_name', $full_name, 2);

        if (!CSRF::validate($_POST['_csrf'] ?? null)) {
            $this->edit($id);
            return;
        }

        if ($validator->hasErrors()) {
            $this->view('reclamation/edit.php', ['rec' => $rec, 'errors' => $validator->getErrors(), '_csrf' => CSRF::generate()]);
            return;
        }

        $this->model->update($id, [
            'sujet' => htmlspecialchars($sujet),
            'description' => htmlspecialchars($description),
            'full_name' => htmlspecialchars($full_name),
            'mobile_phone' => htmlspecialchars($mobile_phone),
            'priority' => in_array($priority, ['low','normal','high']) ? $priority : 'normal'
        ]);

        header('Location: /greentechhub/public/index.php?route=reclamation&updated=1');
        exit;
    }

    public function delete(int $id) {
        $rec = $this->model->find($id);
        if (!$rec) { header('Location: /greentechhub/public/index.php?route=reclamation'); exit; }
        $userId = $_SESSION['user_id'] ?? null;
        if ($rec['user_id'] != $userId && ($_SESSION['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); exit; }
        $this->model->delete($id);
        header('Location: /greentechhub/public/index.php?route=reclamation&deleted=1');
        exit;
    }

    public function adminIndex() {
        if (($_SESSION['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); exit; }

        // read search query (q), page, limit
        $q = trim((string)($_GET['q'] ?? ''));
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50; // change if you want
        $offset = ($page - 1) * $limit;

        // fetch matching records and total count
        $recs = $this->model->all($limit, $offset, $q);
        $total = $this->model->countAll($q);

        // generate CSRF token for inline admin forms (respond / changeStatus)
        $token = CSRF::generate();

        $this->adminView('reclamation/admin_index.php', [
            'recs' => $recs,
            'search_query' => $q,
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            '_csrf' => $token
        ]);
    }

    public function adminStats() {
        if (($_SESSION['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); exit; }
        $stats = $this->model->priorityStats();
        $this->adminView('reclamation/stats.php', [
            'stats' => $stats
        ]);
    }

    public function respond(int $id) {
        if (($_SESSION['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); exit; }

        $response_text = $_POST['response_text'] ?? '';
        if (!CSRF::validate($_POST['_csrf'] ?? null)) {
            header('Location: /greentechhub/public/index.php?route=admin/reclamation&error=csrf');
            exit;
        }

        if (trim($response_text) === '') {
            $this->adminView('reclamation/admin_index.php', ['recs' => $this->model->all(), 'errors' => ['response' => 'La réponse ne peut pas être vide.']]);
            return;
        }

        $adminId = (int)($_SESSION['user_id'] ?? 0);
        $this->model->respond($id, htmlspecialchars($response_text), $adminId);

        header('Location: /greentechhub/public/index.php?route=admin/reclamation&status_changed=1');
        exit;
    }

    public function changeStatus(int $id) {
        if (($_SESSION['role'] ?? '') !== 'admin') { header('HTTP/1.0 403 Forbidden'); exit; }

        // validate CSRF
        if (!CSRF::validate($_POST['_csrf'] ?? null)) {
            header('Location: /greentechhub/public/index.php?route=admin/reclamation&error=csrf');
            exit;
        }

        $newStatus = $_POST['statut'] ?? '';
        if (!in_array($newStatus, ['en attente', 'en cours', 'résolue'])) {
            header('Location: /greentechhub/public/index.php?route=admin/reclamation&error=status');
            exit;
        }
        $this->model->changeStatus($id, $newStatus);
        header('Location: /greentechhub/public/index.php?route=admin/reclamation&status_changed=1');
        exit;
    }
}
