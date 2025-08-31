<?php

use Kurama\Database\Migration;
use Kurama\Core\Database;

return new class extends Migration 
{
    public function up(Database $database): void 
    {
        $sql = "
            CREATE TABLE categories (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                description TEXT,
                parent_id INT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        ";
        $database->query($sql);
    }
    
    public function down(Database $database): void 
    {
        $database->query("DROP TABLE IF EXISTS categories");
    }
};