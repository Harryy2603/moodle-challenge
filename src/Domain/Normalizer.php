<?php

namespace App\Domain;

class Normalizer
{
    public static function normalizeName(string $name): string
    {
        return ucfirst(strtolower(trim($name)));
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}