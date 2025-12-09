<?php
namespace App\Core;

abstract class Controller {
    protected function view(string $path, array $data = []) {
        extract($data);
        require __DIR__ . '/../../views/templates/header.php';
        require __DIR__ . '/../../views/' . $path;
        require __DIR__ . '/../../views/templates/footer.php';
    }

    protected function adminView(string $path, array $data = []) {
        extract($data);
        require __DIR__ . '/../../views/templates/header.php';
        require __DIR__ . '/../../views/templates/admin_sidebar.php';
        require __DIR__ . '/../../views/' . $path;
        require __DIR__ . '/../../views/templates/footer.php';
    }
}
