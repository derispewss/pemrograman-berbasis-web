<?php
/**
 * Database Initialization Script
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'my_article');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '" . DB_NAME . "' created.<br>";
    
    $pdo->exec("USE `" . DB_NAME . "`");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Table 'users' created.<br>";
    
    // Create articles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `articles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `content` TEXT NOT NULL,
        `image` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Table 'articles' created.<br>";
    
    // Insert default admin user
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)")
            ->execute(['admin', $password, 'Administrator']);
        echo "✅ Default admin user created (username: admin, password: admin123).<br>";
    }
    
    // Insert sample articles
    $stmt = $pdo->query("SELECT COUNT(*) FROM articles");
    if ($stmt->fetchColumn() == 0) {
        $articles = [
            ['Perpustakaan Kampus', 'Perpustakaan kampus adalah tempat yang sempurna untuk belajar dan mencari referensi.', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400'],
            ['Ruang Kelas Modern', 'Ruang kelas yang dilengkapi dengan teknologi modern membantu proses pembelajaran.', 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400'],
            ['Kelompok Belajar', 'Belajar bersama teman-teman dalam kelompok belajar sangat efektif.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400'],
            ['Kantin Kampus', 'Kantin kampus menyediakan berbagai pilihan makanan dengan harga terjangkau.', 'https://images.unsplash.com/photo-1567521464027-f127ff144326?w=400'],
        ];
        
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, image) VALUES (?, ?, ?)");
        foreach ($articles as $a) {
            $stmt->execute($a);
        }
        echo "✅ Sample articles inserted.<br>";
    }
    
    echo "<br><strong>🎉 Database ready!</strong><br>";
    echo "<a href='index.php'>Homepage</a> | <a href='admin/login.php'>Login</a>";
    
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
