<?php

use Kurama\Database\Migration;
use Kurama\Core\Database;

return new class extends Migration 
{
    public function up(Database $database): void 
    {
        $sql = "
            CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'editor', 'author') DEFAULT 'author',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        $database->query($sql);
    }
    
    public function down(Database $database): void 
    {
        $database->query("DROP TABLE IF EXISTS users");
    }
};