<?php

use Kurama\Database\Migration;
use Kurama\Core\Database;

return new class extends Migration 
{
    public function up(Database $database): void 
    {
        $sql = "
            CREATE TABLE authors (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                bio TEXT,
                avatar VARCHAR(255),
                website VARCHAR(255),
                twitter VARCHAR(255),
                github VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ";
        $database->query($sql);
    }
    
    public function down(Database $database): void 
    {
        $database->query("DROP TABLE IF EXISTS authors");
    }
};