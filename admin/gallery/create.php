<?php
require_once '../../config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Tambah Gallery - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body>
    <?php 
    $currentPage = 'gallery';
    $baseUrl = '../';
    $rootUrl = '../../index.php';
    include '../includes/sidebar.php';
    ?>

    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-plus-circle me-2"></i>Tambah Gallery</h1>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        </header>

        <div id="alertContainer"></div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Form Gallery</h5></div>
            <div class="card-body">
                <form id="galleryForm" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="title" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="description" id="description" rows="4"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="is_active" id="isActive">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Upload Gambar <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="image_file" id="imageFile" accept="image/*" required />
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    JPG, PNG, WebP (maks 5MB)
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preview</label>
                                <div><img id="preview" src="https://placehold.co/400x225?text=Preview" class="img-fluid rounded" /></div>
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
        // Image preview
        $('#imageFile').on('change', function(e) {
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#preview').attr('src', event.target.result);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // Form submit
        $('#galleryForm').on('submit', function(e) {
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
