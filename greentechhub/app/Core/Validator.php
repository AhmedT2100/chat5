<?php
namespace App\Core;

class Validator {
    private $errors = [];

    public function required(string $field, $value, int $min = 1) {
        if (trim((string)$value) === '' || mb_strlen(trim((string)$value)) < $min) {
            $this->errors[$field] = "Champ requis ou trop court (min $min).";
        }
    }

    public function getErrors(): array { return $this->errors; }
    public function hasErrors(): bool { return !empty($this->errors); }
}
