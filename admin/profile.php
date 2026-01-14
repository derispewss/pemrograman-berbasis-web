<?php
require_once '../config.php';
requireLogin();

// Get current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

function getUserPhotoUrl($photo) {
    if (empty($photo)) return '';
    if (strpos($photo, 'http') === 0) return $photo;
    return '../' . $photo;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Profile Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin.css" />
</head>
<body>
    <?php 
    $currentPage = 'profile';
    $baseUrl = '';
    $rootUrl = '../index.php';
    include 'includes/sidebar.php';
    ?>

    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-person-circle me-2"></i>Profile Management</h1>
        </header>

        <div id="alertContainer"></div>

        <div class="row g-4">
            <!-- Update Name & Username -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Ubah Nama & Username</h5></div>
                    <div class="card-body">
                        <!-- Update Name -->
                        <form id="nameForm" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label">Nama Saat Ini</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" disabled />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Baru <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="newName" required />
                                <small class="text-muted">Nama untuk ditampilkan</small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="nameBtn">
                                <i class="bi bi-check-lg me-2"></i>Update Nama
                            </button>
                        </form>
                        
                        <hr>
                        
                        <!-- Update Username -->
                        <form id="usernameForm">
                            <div class="mb-3">
                                <label class="form-label">Username Saat Ini</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Username Baru <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" id="newUsername" required />
                                <small class="text-muted">Username harus unik</small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="usernameBtn">
                                <i class="bi bi-check-lg me-2"></i>Update Username
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Update Photo -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-camera me-2"></i>Ubah Foto Profile</h5></div>
                    <div class="card-body">
                        <form id="photoForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Foto Saat Ini</label>
                                <div class="text-center">
                                    <?php if (!empty($user['photo'])): ?>
                                        <img src="<?= getUserPhotoUrl($user['photo']) ?>" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" id="currentPhoto" />
                                    <?php else: ?>
                                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light" style="width: 120px; height: 120px;">
                                            <i class="bi bi-person-circle" style="font-size: 4rem; color: #ccc;"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload Foto Baru</label>
                                <input type="file" class="form-control" name="photo_file" id="photoFile" accept="image/*" />
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    JPG, PNG, WebP (maks 2MB)
                                </small>
                            </div>
                            <div class="mb-3" id="photoPreviewContainer" style="display: none;">
                                <label class="form-label">Preview</label>
                                <div class="text-center">
                                    <img id="photoPreview" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;" />
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="photoBtn" disabled>
                                <i class="bi bi-check-lg me-2"></i>Update Foto
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Update Password -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header"><h5 class="mb-0"><i class="bi bi-key me-2"></i>Ubah Password</h5></div>
                    <div class="card-body">
                        <form id="passwordForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Password Lama <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="old_password" id="oldPassword" required />
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="new_password" id="newPassword" required />
                                        <small class="text-muted">Minimal 6 karakter</small>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="passwordBtn">
                                <i class="bi bi-check-lg me-2"></i>Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Photo preview
        $('#photoFile').on('change', function(e) {
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#photoPreview').attr('src', event.target.result);
                    $('#photoPreviewContainer').show();
                    $('#photoBtn').prop('disabled', false);
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // Update Name
        $('#nameForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#nameBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
            
            $.ajax({
                url: 'api_profile.php',
                method: 'POST',
                data: {
                    action: 'update_name',
                    name: $('#newName').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        $('#newName').val('');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan saat menyimpan');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Update Nama');
                }
            });
        });
        
        // Update Username
        $('#usernameForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#usernameBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
            
            $.ajax({
                url: 'api_profile.php',
                method: 'POST',
                data: {
                    action: 'update_username',
                    username: $('#newUsername').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        $('#newUsername').val('');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan saat menyimpan');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Update Username');
                }
            });
        });
        
        // Update Photo
        $('#photoForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#photoBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Mengupload...');
            
            const formData = new FormData(this);
            formData.append('action', 'update_photo');
            
            $.ajax({
                url: 'api_profile.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('danger', response.message);
                        btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Update Foto');
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan saat mengupload');
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Update Foto');
                }
            });
        });
        
        // Update Password
        $('#passwordForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#passwordBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
            
            $.ajax({
                url: 'api_profile.php',
                method: 'POST',
                data: {
                    action: 'update_password',
                    old_password: $('#oldPassword').val(),
                    new_password: $('#newPassword').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        $('#passwordForm')[0].reset();
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan saat menyimpan');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Update Password');
                }
            });
        });
        
        function showAlert(type, message) {
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            $('#alertContainer').html('<div class="alert alert-' + type + ' alert-dismissible fade show"><i class="bi bi-' + icon + ' me-2"></i>' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            window.scrollTo(0, 0);
        }
    });
    </script>
</body>
</html>
