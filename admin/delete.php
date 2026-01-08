<?php
require_once '../config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if (!$article) { header('Location: index.php'); exit; }

// Delete uploaded image
if ($article['image'] && strpos($article['image'], 'uploads/') === 0) {
    $path = '../' . $article['image'];
    if (file_exists($path)) unlink($path);
}

// Delete from database
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$stmt->execute([$id]);

header('Location: index.php?deleted=1');
exit;
?>
