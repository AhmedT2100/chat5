<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }

    public function create(string $nom, string $email, string $password, string $role = 'visiteur'): int {
        $sql = "INSERT INTO `user` (nom, email, mot_de_passe, role) VALUES (:nom, :email, :mot_de_passe, :role)";
        $stmt = $this->pdo->prepare($sql);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->execute([':nom'=>$nom, ':email'=>$email, ':mot_de_passe'=>$hash, ':role'=>$role]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findByEmail(string $email) {
        $sql = "SELECT * FROM `user` WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email'=>$email]);
        return $stmt->fetch();
    }

    public function findById(int $id) {
        $sql = "SELECT * FROM `user` WHERE id_user = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id'=>$id]);
        return $stmt->fetch();
    }
}
