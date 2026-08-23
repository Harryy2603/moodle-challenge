<?php

namespace App\Repositories;

use PDO;
use PDOException;
use App\Infrastructure\Database;
use App\Domain\UserRecord;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function createTable(): void
    {
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

    public function insert(UserRecord $record): bool
    {
        try {
            $sql = "INSERT INTO users (name, surname, email) VALUES (:name, :surname, :email)";
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':name'    => $record->name,
                ':surname' => $record->surname,
                ':email'   => $record->email
            ]);
            
            return true;
        } catch (PDOException $e) {
             if ($e->getCode() === '23505') {
                return false;
            }
            
            throw $e;
        }
    }
}