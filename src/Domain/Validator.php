<?php

namespace App\Domain;

class Validator
{
    public static function validate(UserRecord $record): void
    {
        if (empty($record->name)) {
            $record->addError("Name is required");
        }

        if (empty($record->surname)) {
            $record->addError("Surname is required");
        }

        if (empty($record->email)) {
            $record->addError("Email is required");
        } elseif (!filter_var($record->email, FILTER_VALIDATE_EMAIL)) {
            $record->addError("Invalid email format");
        }
    }
}