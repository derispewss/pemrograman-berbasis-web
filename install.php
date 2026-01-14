<?php
/**
 * Full Database Installation & Reset Script (install.php)
 * WARNING: This will WIPE all data and reset the database!
 */

require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='utf-8'>
    <title>Database Installation (Reset)</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; line-height: 1.6; background: #f8f9fa; }
        .container { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 15px; }
        .step { margin-bottom: 15px; padding: 10px; border-left: 4px solid #ddd; background: #f8f9fa; }
        .success { border-left-color: #27ae60; background: #eafaf1; color: #1e8449; }
        .error { border-left-color: #e74c3c; background: #fdedec; color: #c0392b; }
        .info { border-left-color: #3498db; background: #eaf2f8; color: #2980b9; }
        code { background: #eee; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🚀 Database Initialization</h1>
    <p class='error'><strong>WARNING:</strong> This process deletes ALL existing data!</p>";

try {
    // 1. Drop Tables
    echo "<div class='step info'>🔄 Resetting database tables...</div>";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS `gallery`");
    $pdo->exec("DROP TABLE IF EXISTS `articles`");
    $pdo->exec("DROP TABLE IF EXISTS `users`");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<div class='step success'>✅ Existing tables dropped.</div>";

    // 2. Create Users Table
    $pdo->exec("CREATE TABLE `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `photo` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='step success'>✅ Table <code>users</code> created.</div>";

    // 3. Create Articles Table (with FK)
    $pdo->exec("CREATE TABLE `articles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `content` TEXT NOT NULL,
        `image` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='step success'>✅ Table <code>articles</code> created.</div>";

    // 4. Create Gallery Table (with FK)
    $pdo->exec("CREATE TABLE `gallery` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `image` VARCHAR(255) NOT NULL,
        `sort_order` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<div class='step success'>✅ Table <code>gallery</code> created.</div>";

    // 5. Seed Users (Admin)
    echo "<div class='step info'>🌱 Seeding data...</div>";
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $password, 'Administrator']);
    $adminId = $pdo->lastInsertId();
    echo "<div class='step success'>✅ Admin user created (User ID: $adminId).<br><small>Username: admin | Password: admin123</small></div>";

    // 6. Seed Articles
    $articles = [
        ['Perpustakaan Kampus', 'Perpustakaan kampus adalah tempat yang sempurna untuk belajar dan mencari referensi.', 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=400'],
        ['Ruang Kelas Modern', 'Ruang kelas yang dilengkapi dengan teknologi modern membantu proses pembelajaran.', 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=400'],
        ['Kelompok Belajar', 'Belajar bersama teman-teman dalam kelompok belajar sangat efektif.', 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=400'],
        ['Kantin Kampus', 'Kantin kampus menyediakan berbagai pilihan makanan dengan harga terjangkau.', 'https://images.unsplash.com/photo-1567521464027-f127ff144326?w=400'],
    ];

    $stmt = $pdo->prepare("INSERT INTO articles (user_id, title, content, image) VALUES (?, ?, ?, ?)");
    foreach ($articles as $a) {
        $stmt->execute([$adminId, $a[0], $a[1], $a[2]]);
    }
    echo "<div class='step success'>✅ Sample articles inserted (Owner: Admin).</div>";

    // 7. Seed Gallery
    $galleries = [
        ['Campus Library', 'Perpustakaan kampus yang nyaman untuk belajar', 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=1200', 1],
        ['Study Group', 'Belajar bersama teman-teman', 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1200', 2],
        ['Campus Life', 'Kehidupan kampus yang menyenangkan', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1200', 3],
    ];

    $stmt = $pdo->prepare("INSERT INTO gallery (user_id, title, description, image, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($galleries as $g) {
        $stmt->execute([$adminId, $g[0], $g[1], $g[2], $g[3]]);
    }
    echo "<div class='step success'>✅ Sample gallery items inserted (Owner: Admin).</div>";

    // 8. Create Directories
    $dirs = ['uploads/gallery', 'uploads/articles', 'uploads/profiles'];
    foreach ($dirs as $dir) {
        if (!file_exists(__DIR__ . '/' . $dir)) {
            mkdir(__DIR__ . '/' . $dir, 0755, true);
        }
    }
    echo "<div class='step success'>✅ Upload directories checked/created.</div>";

    echo "<h2 style='color: #27ae60; margin-top: 20px;'>🎉 Installation Successful!</h2>";
    echo "<p><a href='index.php' style='text-decoration:none; background:#3498db; color:white; padding:10px 20px; border-radius:5px;'>Open Homepage</a> ";
    echo "<a href='admin/login.php' style='text-decoration:none; background:#2c3e50; color:white; padding:10px 20px; border-radius:5px;'>Login to Admin</a></p>";

} catch (PDOException $e) {
    echo "<div class='step error'>❌ Database Error: " . htmlspecialchars($e->getMessage()) . "</div>";
} catch (Exception $e) {
    echo "<div class='step error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "</div></body></html>";
?>
