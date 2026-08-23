<?php

namespace App\Repositories;

use PDO;
use App\Infrastructure\Database;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createTable(): void
    {
        // The PDF requires at least name, surname, and a UNIQUE email[cite: 1].
        $sql = "
            DROP TABLE IF EXISTS users;
            CREATE TABLE users (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE
            );
        ";
        
        $this->db->exec($sql);
    }
}