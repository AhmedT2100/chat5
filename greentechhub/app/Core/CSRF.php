<?php
namespace App\Core;

class CSRF {
    const KEY = '_csrf';
    public static function generate(): string {
        if (!session_id()) session_start();
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }
    public static function validate(?string $token): bool {
        if (!session_id()) session_start();
        if (empty($token) || empty($_SESSION[self::KEY])) return false;
        return hash_equals($_SESSION[self::KEY], $token);
    }
}
