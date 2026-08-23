<?php

namespace App\Domain;

class UserRecord
{
    public string $name = '';
    public string $surname = '';
    public string $email = '';
    public bool $isValid = true;
    public array $errors = [];

    public function addError(string $message): void
    {
        $this->isValid = false;
        $this->errors[] = $message;
    }
}