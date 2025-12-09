<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\User;
use App\Core\CSRF;

class AuthController extends Controller {
    private $userModel;
    private const ADMIN_PASSCODE = '985774';

    public function __construct() {
        $this->userModel = new User();
        if (!session_id()) session_start();
    }

    public function showLogin(array $data = []) {
        $this->view('auth/login.php', $data);
    }

    public function login() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // CSRF optional if your form includes token; skip for quick login or include check if you used CSRF token
        $user = $this->userModel->findByEmail($email);
        if (!$user || !password_verify($password, $user['mot_de_passe'])) {
            $this->showLogin(['error' => 'Email ou mot de passe incorrect.']);
            return;
        }

        // set session
        $_SESSION['user_id'] = (int)$user['id_user'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nom'] = $user['nom'];

        // redirect: if admin go to admin list else user list
        if ($user['role'] === 'admin') {
            header('Location: /greentechhub/public/index.php?route=admin/reclamation');
            exit;
        }
        header('Location: /greentechhub/public/index.php?route=reclamation');
        exit;
    }

    public function showRegister(array $data = []) {
        $this->view('auth/register.php', $data);
    }

    public function register() {
        $validator = new Validator();
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = ($_POST['role'] ?? 'user') === 'admin' ? 'admin' : 'innovateur';
        $admin_check = $_POST['admin_check'] ?? '';
        $admin_passcode = $_POST['admin_passcode'] ?? '';

        $validator->required('nom', $nom, 2);
        $validator->required('email', $email, 5);
        $validator->required('password', $password, 3);

        // admin creation check
        if ($role === 'admin') {
            if (empty($admin_passcode) || $admin_passcode !== self::ADMIN_PASSCODE) {
                $errors = $validator->getErrors();
                $errors['admin'] = 'Code administrateur incorrect.';
                $this->showRegister(['errors'=>$errors, 'old'=>$_POST]);
                return;
            }
        }

        if ($validator->hasErrors()) {
            $this->showRegister(['errors'=>$validator->getErrors(), 'old'=>$_POST]);
            return;
        }

        // check existing email
        $exists = $this->userModel->findByEmail($email);
        if ($exists) {
            $this->showRegister(['errors'=>['email'=>'Email déjà utilisé.'], 'old'=>$_POST]);
            return;
        }

        $createdId = $this->userModel->create($nom, $email, $password, $role === 'admin' ? 'admin' : 'innovateur');

        // auto login
        $_SESSION['user_id'] = $createdId;
        $_SESSION['role'] = $role === 'admin' ? 'admin' : 'innovateur';
        $_SESSION['nom'] = $nom;

        if ($_SESSION['role'] === 'admin') {
            header('Location: /greentechhub/public/index.php?route=admin/reclamation');
        } else {
            header('Location: /greentechhub/public/index.php?route=reclamation');
        }
        exit;
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header('Location: /greentechhub/public/index.php');
        exit;
    }
}
