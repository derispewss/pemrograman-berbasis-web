<?php
require_once '../../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// Validation
if ($id === 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Judul wajib diisi']);
    exit;
}

// Get existing gallery
$stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
$stmt->execute([$id]);
$gallery = $stmt->fetch();

if (!$gallery) {
    echo json_encode(['success' => false, 'message' => 'Gallery tidak ditemukan']);
    exit;
}

$imagePath = $gallery['image'];

// Handle new image upload
if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image_file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP']);
        exit;
    }
    
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file terlalu besar. Maksimal 5MB']);
        exit;
    }
    
    // Create upload directory if not exists
    $uploadDir = '../../uploads/gallery/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'gallery_' . time() . '_' . uniqid() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Delete old image if it's a local file
        if ($gallery['image'] && strpos($gallery['image'], 'uploads/') === 0) {
            $oldPath = '../../' . $gallery['image'];
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }
        
        $imagePath = 'uploads/gallery/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload gambar']);
        exit;
    }
}

// Update database
try {
    $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, image = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$title, $description, $imagePath, $is_active, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Gallery berhasil diperbarui']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()]);
}
?>
