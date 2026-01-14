<?php
require_once '../../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$username = trim($_POST['username'] ?? '');
$name = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';

if (!$id || empty($username) || empty($name)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

try {
    // Get current user data
    $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $currentUser = $stmt->fetch();

    if (!$currentUser) {
        echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        exit;
    }

    // Check unique username
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$username, $id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
        exit;
    }

    // Handle Image Upload
    $imagePath = $currentUser['photo'];
    
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['image_file']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            echo json_encode(['success' => false, 'message' => 'Tipe file tidak didukung']);
            exit;
        }

        if ($_FILES['image_file']['size'] > 2 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar (Maks 2MB)']);
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
            // Delete old photo if exists and is local
            if ($currentUser['photo'] && strpos($currentUser['photo'], 'uploads/') === 0) {
                $oldPath = '../../' . $currentUser['photo'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            
            $imagePath = 'uploads/profiles/' . $filename;
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal mengupload gambar']);
            exit;
        }
    }

    // Prepare Update Query
    if (!empty($password)) {
        // Update with password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, photo = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $name, $imagePath, $hashedPassword, $id]);
    } else {
        // Update without password
        $stmt = $pdo->prepare("UPDATE users SET username = ?, name = ?, photo = ? WHERE id = ?");
        $stmt->execute([$username, $name, $imagePath, $id]);
    }

    // Refresh Session if updating self
    if ($id == $_SESSION['user_id']) {
        $_SESSION['user_name'] = $name;
        $_SESSION['user_photo'] = $imagePath;
    }

    echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);

} catch (PDOException $e) {
    if (IS_PRODUCTION) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan sistem']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
?>
