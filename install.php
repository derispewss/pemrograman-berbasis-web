<?php
/**
 * Installation Script
 * Run this once to set up the database
 * DELETE THIS FILE AFTER INSTALLATION
 */

// Security check
$installKey = $_GET['key'] ?? '';
$expectedKey = 'install_' . date('Ymd'); // Key format: install_20260108

if ($installKey !== $expectedKey) {
    die("
    <h2>Installation Script</h2>
    <p>For security, please access with the correct key:</p>
    <code>install.php?key=" . $expectedKey . "</code>
    <p><strong>Delete this file after installation!</strong></p>
    ");
}

// Load environment
$env = require __DIR__ . '/env.php';

echo "<h2>🔧 Installing " . $env['site_name'] . "</h2>";

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=" . $env['db_host'], $env['db_user'], $env['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $env['db_name'] . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Database '" . $env['db_name'] . "' created<br>";
    
    $pdo->exec("USE `" . $env['db_name'] . "`");
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Table 'users' created<br>";
    
    // Create articles table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `articles` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `content` TEXT NOT NULL,
        `image` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✅ Table 'articles' created<br>";
    
    // Create logs directory
    $logsDir = __DIR__ . '/logs';
    if (!is_dir($logsDir)) {
        mkdir($logsDir, 0755, true);
        file_put_contents($logsDir . '/.htaccess', 'Deny from all');
        echo "✅ Logs directory created<br>";
    }
    
    // Create uploads directory
    $uploadsDir = __DIR__ . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0755, true);
    }
    
    // Insert default admin
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)")
            ->execute(['admin', $password, 'Administrator']);
        echo "✅ Default admin created<br>";
        echo "<br><strong>Login credentials:</strong><br>";
        echo "Username: <code>admin</code><br>";
        echo "Password: <code>admin123</code><br>";
        echo "<br><strong>⚠️ CHANGE THE PASSWORD AFTER LOGIN!</strong><br>";
    }
    
    echo "<br><h3>🎉 Installation Complete!</h3>";
    echo "<p><strong style='color:red'>⚠️ DELETE THIS FILE (install.php) NOW!</strong></p>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/login.php'>Go to Admin</a></p>";
    
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>
