<?php
require_once '../../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';

// Validation
if (empty($username) || empty($password) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
    exit;
}

try {
    // Check if username exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
        exit;
    }

    // Handle Image Upload
    $imagePath = null;
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['image_file']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Tipe file tidak didukung (Gunakan JPG, PNG, GIF, atau WebP)']);
            exit;
        }

        // Limit size (2MB)
        if ($_FILES['image_file']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar (Maksimal 2MB)']);
            exit;
        }

        $uploadDir = '../../uploads/profiles/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $extension = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . time() . '_' . uniqid() . '.' . $extension;
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            // Save path relative to root (uploads/profiles/...)
            // Since we moved it to ../../uploads/profiles/
            // The DB path should be uploads/profiles/filename
            $imagePath = 'uploads/profiles/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupload gambar']);
            exit;
        }
    }

    // Hash Password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert to DB
    $stmt = $pdo->prepare("INSERT INTO users (username, password, name, photo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $hashedPassword, $name, $imagePath]);

    echo json_encode(['success' => true, 'message' => 'User berhasil dibuat']);

} catch (PDOException $e) {
    if (IS_PRODUCTION) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan database']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
