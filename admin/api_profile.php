<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['user_id'];

// Get current user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
    exit;
}

// ========== UPDATE USERNAME ==========
if ($action === 'update_username') {
    $newUsername = trim($_POST['username'] ?? '');
    
    if (empty($newUsername)) {
        echo json_encode(['success' => false, 'message' => 'Username tidak boleh kosong']);
        exit;
    }
    
    if (strlen($newUsername) < 3) {
        echo json_encode(['success' => false, 'message' => 'Username minimal 3 karakter']);
        exit;
    }
    
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
    $stmt->execute([$newUsername, $userId]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Username sudah digunakan']);
        exit;
    }
    
    // Update username
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$newUsername, $userId]);
        
        $_SESSION['username'] = $newUsername;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Username berhasil diperbarui',
            'name' => $user['name']
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui username']);
    }
    exit;
}

// ========== UPDATE NAME ==========
if ($action === 'update_name') {
    $newName = trim($_POST['name'] ?? '');
    
    if (empty($newName)) {
        echo json_encode(['success' => false, 'message' => 'Nama tidak boleh kosong']);
        exit;
    }
    
    if (strlen($newName) < 2) {
        echo json_encode(['success' => false, 'message' => 'Nama minimal 2 karakter']);
        exit;
    }
    
    // Update name
    try {
        $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$newName, $userId]);
        
        $_SESSION['user_name'] = $newName;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Nama berhasil diperbarui'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui nama']);
    }
    exit;
}

// ========== UPDATE PHOTO ==========
if ($action === 'update_photo') {
    if (!isset($_FILES['photo_file']) || $_FILES['photo_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Foto tidak valid']);
        exit;
    }
    
    $file = $_FILES['photo_file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, atau WebP']);
        exit;
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 2MB']);
        exit;
    }
    
    // Create upload directory
    $uploadDir = '../uploads/profiles/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Delete old photo if exists
        if ($user['photo'] && strpos($user['photo'], 'uploads/') === 0) {
            $oldPath = '../' . $user['photo'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        
        $photoPath = 'uploads/profiles/' . $filename;
        
        // Update database
        try {
            $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ?");
            $stmt->execute([$photoPath, $userId]);
            
            $_SESSION['user_photo'] = $photoPath;
            
            echo json_encode([
                'success' => true, 
                'message' => 'Foto profile berhasil diperbarui',
                'photo_url' => $photoPath
            ]);
        } catch (PDOException $e) {
            // Delete uploaded file if database update fails
            if (file_exists($targetPath)) {
                unlink($targetPath);
            }
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload foto']);
    }
    exit;
}

// ========== UPDATE PASSWORD ==========
if ($action === 'update_password') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    if (empty($oldPassword) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Semua field wajib diisi']);
        exit;
    }
    
    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password baru minimal 6 karakter']);
        exit;
    }
    
    // Verify old password
    if (!password_verify($oldPassword, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Password lama tidak sesuai']);
        exit;
    }
    
    // Update password
    try {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Password berhasil diperbarui'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui password']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
