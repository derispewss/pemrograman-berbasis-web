<?php
require_once '../config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Artikel - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; }
        .sidebar { position: fixed; left: 0; top: 0; bottom: 0; width: 240px; background: #2c3e50; padding: 1.5rem; }
        .sidebar-brand { color: #fff; font-size: 1.2rem; font-weight: 600; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 1.5rem; }
        .sidebar-nav a { color: rgba(255,255,255,0.7); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.75rem; text-decoration: none; border-radius: 8px; margin-bottom: 0.25rem; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.1); color: #fff; }
        .sidebar-nav a.logout { color: #e74c3c; }
        .main-content { margin-left: 240px; padding: 2rem; }
        .content-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .content-header h1 { font-size: 1.5rem; font-weight: 600; color: #2c3e50; margin: 0; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-header { background: #fff; border-bottom: 1px solid #eee; padding: 1rem 1.25rem; }
        .form-control { border-radius: 8px; }
        .form-control:focus { border-color: #e74c3c; box-shadow: 0 0 0 3px rgba(231,76,60,0.1); }
        .btn-primary { background: #e74c3c; border: none; }
        .btn-primary:hover { background: #c0392b; }
        .img-preview { max-height: 150px; border-radius: 8px; margin-top: 0.5rem; }
        .user-info { color: rgba(255,255,255,0.7); font-size: 0.85rem; padding: 1rem; background: rgba(0,0,0,0.1); border-radius: 8px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand"><i class="bi bi-journal-richtext me-2"></i>Admin Panel</div>
        <div class="user-info"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <nav class="sidebar-nav">
            <a href="index.php"><i class="bi bi-grid"></i>Dashboard</a>
            <a href="create.php" class="active"><i class="bi bi-plus-circle"></i>Tambah Artikel</a>
            <a href="../index.php"><i class="bi bi-house"></i>Lihat Website</a>
            <a href="logout.php" class="logout"><i class="bi bi-box-arrow-left"></i>Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-plus-circle me-2"></i>Tambah Artikel</h1>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        </header>

        <div id="alertContainer"></div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Form Artikel</h5></div>
            <div class="card-body">
                <form id="articleForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="title" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konten <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="content" id="content" rows="8" required></textarea>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Upload Gambar</label>
                                <input type="file" class="form-control" name="image_file" id="imageFile" accept="image/*" />
                                <small class="text-muted">JPG, PNG, GIF, WebP (maks 5MB)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">atau URL Gambar</label>
                                <input type="url" class="form-control" name="image" id="imageUrl" placeholder="https://..." />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preview</label>
                                <div><img id="preview" src="https://via.placeholder.com/300x150?text=Preview" class="img-preview img-fluid" /></div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-lg me-2"></i>Simpan
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Image preview from URL
        $('#imageUrl').on('input', function() {
            if (this.value) {
                $('#preview').attr('src', this.value);
                $('#imageFile').val('');
            }
        });
        
        // Image preview from file
        $('#imageFile').on('change', function(e) {
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#preview').attr('src', e.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
                $('#imageUrl').val('');
            }
        });
        
        // Form submit with AJAX
        $('#articleForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#submitBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
            
            const formData = new FormData(this);
            
            $.ajax({
                url: 'api_create.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'index.php?created=1';
                    } else {
                        showAlert('danger', response.message);
                        btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Simpan');
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan saat menyimpan');
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Simpan');
                }
            });
        });
        
        function showAlert(type, message) {
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            $('#alertContainer').html('<div class="alert alert-' + type + ' alert-dismissible fade show"><i class="bi bi-' + icon + ' me-2"></i>' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
        }
    });
    </script>
</body>
</html>
