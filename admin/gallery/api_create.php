<?php
require_once '../../config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

// Validation
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Judul wajib diisi']);
    exit;
}

$imagePath = '';

// Handle image upload
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
        $imagePath = 'uploads/gallery/' . $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal mengupload gambar']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Gambar wajib diupload']);
    exit;
}

// Get next sort_order
$stmt = $pdo->query("SELECT MAX(sort_order) as max_order FROM gallery");
$maxOrder = $stmt->fetch()['max_order'] ?? 0;
$sortOrder = $maxOrder + 1;

// Insert to database
try {
    $userId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO gallery (user_id, title, description, image, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $description, $imagePath, $sortOrder, $is_active]);
    
    echo json_encode(['success' => true, 'message' => 'Gallery berhasil ditambahkan']);
} catch (PDOException $e) {
    // Delete uploaded file if database insert fails
    if ($imagePath && file_exists('../../' . $imagePath)) {
        unlink('../../' . $imagePath);
    }
    
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()]);
}
?>
