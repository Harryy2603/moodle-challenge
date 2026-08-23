<?php

namespace App\Infrastructure;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = $_SERVER['DB_HOST'] ?? '127.0.0.1';
            $port = $_SERVER['DB_PORT'] ?? '5432';
            $db   = $_SERVER['DB_DATABASE'] ?? 'user_import';
            $user = $_SERVER['DB_USERNAME'] ?? 'moodle_user';
            $pass = $_SERVER['DB_PASSWORD'] ?? 'secretpassword';

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}