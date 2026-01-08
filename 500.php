<?php
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 - Server Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: sans-serif; }
        .error-page { text-align: center; padding: 2rem; }
        .error-code { font-size: 8rem; font-weight: 700; color: #e74c3c; line-height: 1; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-code">500</div>
        <p style="font-size: 1.5rem; color: #2c3e50;">Terjadi Kesalahan Server</p>
        <p class="text-muted">Mohon coba beberapa saat lagi.</p>
        <a href="/" class="btn btn-danger mt-3">Kembali ke Beranda</a>
    </div>
</body>
</html>
