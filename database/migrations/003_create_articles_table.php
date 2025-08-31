<?php

use Kurama\Database\Migration;
use Kurama\Core\Database;

return new class extends Migration 
{
    public function up(Database $database): void 
    {
        $sql = "
            CREATE TABLE articles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content LONGTEXT NOT NULL,
                excerpt TEXT,
                category_id INT,
                author_id INT,
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                featured_image VARCHAR(255),
                published_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
                FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE SET NULL
            )
        ";
        $database->query($sql);
    }
    
    public function down(Database $database): void 
    {
        $database->query("DROP TABLE IF EXISTS articles");
    }
};