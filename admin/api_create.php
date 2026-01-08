<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$image = trim($_POST['image'] ?? '');

// Validation
if (empty($title)) {
    $response['message'] = 'Judul wajib diisi';
    echo json_encode($response);
    exit;
}

if (empty($content)) {
    $response['message'] = 'Konten wajib diisi';
    echo json_encode($response);
    exit;
}

// Handle file upload
if (!empty($_FILES['image_file']['name'])) {
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($_FILES['image_file']['type'], $allowed)) {
        $response['message'] = 'Tipe file tidak valid. Gunakan JPG, PNG, GIF, atau WebP.';
        echo json_encode($response);
        exit;
    }
    
    if ($_FILES['image_file']['size'] > 5 * 1024 * 1024) {
        $response['message'] = 'File terlalu besar (maksimal 5MB)';
        echo json_encode($response);
        exit;
    }
    
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['image_file']['name']);
    if (move_uploaded_file($_FILES['image_file']['tmp_name'], $uploadDir . $fileName)) {
        $image = 'uploads/' . $fileName;
    } else {
        $response['message'] = 'Gagal mengupload file';
        echo json_encode($response);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, image) VALUES (?, ?, ?)");
    $stmt->execute([$title, $content, $image]);
    
    $response['success'] = true;
    $response['message'] = 'Artikel berhasil ditambahkan';
    $response['id'] = $pdo->lastInsertId();
} catch (PDOException $e) {
    $response['message'] = 'Gagal menyimpan artikel';
}

echo json_encode($response);
?>
