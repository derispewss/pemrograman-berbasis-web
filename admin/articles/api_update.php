<?php
require_once '../../config.php';
requireLogin();

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Method not allowed';
    echo json_encode($response);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$image = trim($_POST['image'] ?? '');

if ($id <= 0) {
    $response['message'] = 'ID tidak valid';
    echo json_encode($response);
    exit;
}

// Get existing article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) {
    $response['message'] = 'Artikel tidak ditemukan';
    echo json_encode($response);
    exit;
}

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

// Keep existing image if not changed
if (empty($image)) {
    $image = $article['image'];
}

// Handle file upload
if (!empty($_FILES['image_file']['name'])) {
    $uploadDir = '../../uploads/articles/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!in_array($_FILES['image_file']['type'], $allowed)) {
        $response['message'] = 'Tipe file tidak valid';
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
        // Delete old uploaded image
        if ($article['image'] && strpos($article['image'], 'uploads/') === 0) {
            $oldPath = '../../' . $article['image'];
            if (file_exists($oldPath)) unlink($oldPath);
        }
        $image = 'uploads/articles/' . $fileName;
    } else {
        $response['message'] = 'Gagal mengupload file';
        echo json_encode($response);
        exit;
    }
}

try {
    $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, image = ? WHERE id = ?");
    $stmt->execute([$title, $content, $image, $id]);
    
    $response['success'] = true;
    $response['message'] = 'Artikel berhasil diperbarui';
} catch (PDOException $e) {
    $response['message'] = 'Gagal memperbarui artikel';
}

echo json_encode($response);
?>
