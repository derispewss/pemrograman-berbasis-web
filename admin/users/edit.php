<?php
require_once '../../config.php';
requireLogin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit User - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body>
    <?php 
    $currentPage = 'users';
    $baseUrl = '../';
    $rootUrl = '../../index.php';
    include '../includes/sidebar.php';
    ?>

    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-pencil-square me-2"></i>Edit User</h1>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        </header>

        <div id="alertContainer"></div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Edit Data User</h5></div>
            <div class="card-body">
                <form id="userForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $user['id'] ?>">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required autocomplete="username" />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required />
                            </div>
                            <div class="alert alert-info py-2">
                                <small><i class="bi bi-info-circle me-1"></i>Kosongkan password jika tidak ingin mengubahnya.</small>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" class="form-control" name="password" id="password" autocomplete="new-password" />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" autocomplete="new-password" />
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="mb-3">
                                <label class="form-label">Foto Profil</label>
                                <input type="file" class="form-control" name="image_file" id="imageFile" accept="image/*" />
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Format: JPG, PNG, GIF, WebP
                                </small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Preview</label>
                                <div class="text-center p-3 border rounded bg-light">
                                    <?php 
                                        $photoSrc = !empty($user['photo']) ? (strpos($user['photo'], 'http') === 0 ? $user['photo'] : '../../' . $user['photo']) : 'https://via.placeholder.com/150?text=Avatar';
                                    ?>
                                    <img id="preview" src="<?= htmlspecialchars($photoSrc) ?>" class="rounded-circle" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/150?text=Avatar'" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-lg me-2"></i>Simpan Perubahan
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
        $('#userForm').on('submit', function(e) {
            e.preventDefault();
            
            // Password match check if filled
            if ($('#password').val() && $('#password').val() !== $('#confirm_password').val()) {
                showAlert('danger', 'Password baru tidak cocok!');
                return;
            }

            const btn = $('#submitBtn');
            const originalText = btn.html();
            const formData = new FormData(this);

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');

            $.ajax({
                url: 'api_update.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'index.php?updated=1';
                    } else {
                        showAlert('danger', response.message);
                        btn.prop('disabled', false).html(originalText);
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan sistem');
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });

        function showAlert(type, message) {
            const alert = $('<div class="alert alert-' + type + ' alert-dismissible fade show"><i class="bi bi-exclamation-circle me-2"></i>' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            $('#alertContainer').html(alert);
            $('html, body').animate({ scrollTop: 0 }, 'fast');
        }
    });
    </script>
</body>
</html>
