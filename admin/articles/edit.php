<?php
require_once '../../config.php';
requireLogin();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) { header('Location: index.php'); exit; }

function getImgSrc($image) {
    if (empty($image)) return '';
    if (strpos($image, 'http') === 0) return $image;
    return '../../' . $image;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Artikel - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body>
    <?php 
    $currentPage = 'articles';
    $baseUrl = '../';
    $rootUrl = '../../index.php';
    include '../includes/sidebar.php';
    ?>

    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-pencil-square me-2"></i>Edit Artikel</h1>
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        </header>

        <div id="alertContainer"></div>

        <div class="card">
            <div class="card-header"><h5 class="mb-0">Edit: <?= htmlspecialchars($article['title']) ?></h5></div>
            <div class="card-body">
                <form id="articleForm" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= $article['id'] ?>" />
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="mb-3">
                                <label class="form-label">Judul <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="title" value="<?= htmlspecialchars($article['title']) ?>" required />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Konten <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="content" id="content" rows="8" required><?= htmlspecialchars($article['content']) ?></textarea>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <?php if ($article['image']): ?>
                            <div class="mb-3">
                                <label class="form-label">Gambar Saat Ini</label>
                                <div><img src="<?= htmlspecialchars(getImgSrc($article['image'])) ?>" class="img-fluid rounded" style="max-height: 150px;" onerror="this.src='https://via.placeholder.com/300x150?text=Error'" /></div>
                            </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label">Upload Gambar Baru (Opsional)</label>
                                <input type="file" class="form-control" name="image_file" id="imageFile" accept="image/*" />
                                <small class="text-muted">Kosongkan jika tidak ingin mengganti</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">atau URL Gambar</label>
                                <input type="url" class="form-control" name="image" id="imageUrl" value="<?= htmlspecialchars($article['image']) ?>" />
                            </div>
                            <div class="mb-3" id="previewContainer" style="display: none;">
                                <label class="form-label">Preview Gambar Baru</label>
                                <div><img id="preview" class="img-fluid rounded" style="max-height: 150px;" /></div>
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
        // Image preview from file
        $('#imageFile').on('change', function(e) {
            if (e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#preview').attr('src', event.target.result);
                    $('#previewContainer').show();
                };
                reader.readAsDataURL(e.target.files[0]);
                $('#imageUrl').val('');
            }
        });
        
        // Image preview from URL
        $('#imageUrl').on('input', function() {
            if (this.value) {
                $('#imageFile').val('');
                $('#previewContainer').hide();
            }
        });
        
        // Form submit
        $('#articleForm').on('submit', function(e) {
            e.preventDefault();
            
            const btn = $('#submitBtn');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...');
            
            const formData = new FormData(this);
            
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
                        btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Simpan Perubahan');
                    }
                },
                error: function() {
                    showAlert('danger', 'Terjadi kesalahan saat menyimpan');
                    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>Simpan Perubahan');
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
