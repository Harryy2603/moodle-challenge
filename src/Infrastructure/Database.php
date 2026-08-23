<?php

namespace App\Infrastructure;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Fallbacks match our .env.example / docker-compose setup
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '5432';
            $db   = $_ENV['DB_DATABASE'] ?? 'user_import';
            $user = $_ENV['DB_USERNAME'] ?? 'moodle_user';
            $pass = $_ENV['DB_PASSWORD'] ?? 'secretpassword';

            $dsn = "pgsql:host=$host;port=$port;dbname=$db";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // If this fails, we want a clean error in the CLI
                echo "\n[ERROR] Database connection failed: " . $e->getMessage() . "\n";
                exit(1);
            }
        }

        return self::$instance;
    }
}