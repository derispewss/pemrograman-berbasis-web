<?php
require_once 'config.php';
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Halaman Tidak Ditemukan | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .error-page { text-align: center; padding: 2rem; }
        .error-code { font-size: 8rem; font-weight: 700; color: #e74c3c; line-height: 1; }
        .error-message { font-size: 1.5rem; color: #2c3e50; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">404</div>
        <p class="error-message">Halaman Tidak Ditemukan</p>
        <p class="text-muted">Halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <a href="<?= SITE_URL ?>" class="btn btn-danger mt-3">
            <i class="bi bi-house me-2"></i>Kembali ke Beranda
        </a>
    </div>
</body>
</html>
