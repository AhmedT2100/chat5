<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $pdo = null;
    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$pdo === null) {
            $host = '127.0.0.1';
            $db   = 'greentechhub';    // DB name — change if needed
            $user = 'root';            // XAMPP default
            $pass = '';                // XAMPP default: empty
            $charset = 'utf8mb4';
            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try {
                self::$pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                die('DB connection error: '.$e->getMessage());
            }
        }
        return self::$pdo;
    }
}
