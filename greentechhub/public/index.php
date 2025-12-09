<?php
// public/index.php

// show errors for dev
ini_set('display_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../app/Core/Controller.php';
require __DIR__ . '/../app/Core/CSRF.php';
require __DIR__ . '/../app/Core/Validator.php';
require __DIR__ . '/../app/Models/Reclamation.php';
require __DIR__ . '/../app/Controllers/ReclamationController.php';
require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Models/User.php';

use App\Controllers\ReclamationController;

session_start();

// ===============================
// NOTE: auto-login removed
// ===============================

$route = $_GET['route'] ?? '';

$ctl = new ReclamationController();

// (Optional debugging) Uncomment to see the evaluated route:
// echo "<pre>ROUTE='$route' REQUEST_URI=" . htmlspecialchars($_SERVER['REQUEST_URI']) . "</pre>";

switch ($route) {

    /* ---------------- Reclamation (User) ---------------- */

    case 'reclamation':
        $ctl->index();
        break;
   

    case 'reclamation/create':
        $ctl->create();
        break;

    case 'reclamation/store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctl->store();
        } else {
            header('Location: /greentechhub/public/index.php?route=reclamation');
        }
        break;

    case 'reclamation/show':
        $ctl->show((int)($_GET['id'] ?? 0));
        break;

    case 'reclamation/edit':
        $ctl->edit((int)($_GET['id'] ?? 0));
        break;

    case 'reclamation/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctl->update((int)($_GET['id'] ?? 0));
        } else {
            header('Location: /greentechhub/public/index.php?route=reclamation');
        }
        break;

    case 'reclamation/delete':
        $ctl->delete((int)($_GET['id'] ?? 0));
        break;

    /* ---------------- Admin ---------------- */

    case 'admin/reclamation':
        $ctl->adminIndex();
        break;
     case 'admin/reclamation/stats':
        $ctl->adminStats();
        break;

    case 'admin/reclamation/changeStatus':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctl->changeStatus((int)($_GET['id'] ?? 0));
        } else {
            header('Location: /greentechhub/public/index.php?route=admin/reclamation');
        }
        break;

    // Add admin alias routes for delete/show so admin links work
    case 'admin/reclamation/delete':
        $ctl->delete((int)($_GET['id'] ?? 0));
        break;

    case 'admin/reclamation/show':
        $ctl->show((int)($_GET['id'] ?? 0));
        break;

    /* ---------------- Auth ---------------- */

    case 'auth/login':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new \App\Controllers\AuthController())->login();
        } else {
            (new \App\Controllers\AuthController())->showLogin();
        }
        break;

    case 'auth/register':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new \App\Controllers\AuthController())->register();
        } else {
            (new \App\Controllers\AuthController())->showRegister();
        }
        break;

    case 'auth/logout':
        (new \App\Controllers\AuthController())->logout();
        break;
            case 'admin/reclamation/respond':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ctl->respond((int)($_GET['id'] ?? 0));
        } else {
            header('Location: /greentechhub/public/index.php?route=admin/reclamation');
        }
        break;


    /* ---------------- Default / Home ---------------- */

    default:
        require __DIR__ . '/../views/templates/header.php';
        ?>
        <h1>GreenTechHub — Home</h1>
        <p><a href="/greentechhub/public/index.php?route=reclamation">Mes réclamations</a></p>
        <?php
        require __DIR__ . '/../views/templates/footer.php';
        break;
}
