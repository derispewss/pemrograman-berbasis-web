<?php
require_once '../../config.php';
requireLogin();

// API endpoint for AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'list') {
        $stmt = $pdo->query("SELECT g.*, u.name as author_name FROM gallery g LEFT JOIN users u ON g.user_id = u.id ORDER BY g.sort_order ASC, g.created_at DESC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }
    
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT image FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        
        if ($item) {
            if ($item['image'] && strpos($item['image'], 'uploads/') === 0) {
                $path = '../../' . $item['image'];
                if (file_exists($path)) unlink($path);
            }
            $pdo->prepare("DELETE FROM gallery WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Gallery berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gallery tidak ditemukan']);
        }
        exit;
    }
}

function getImageUrl($image) {
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
    <title>Gallery Management - Admin</title>
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

    <!-- Main Content -->
    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-images me-2"></i>Gallery Management</h1>
            <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Gallery</a>
        </header>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-primary"><i class="bi bi-images"></i></div>
                    <div class="stats-info"><h3 id="totalGallery">-</h3><p>Total Gallery</p></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-success"><i class="bi bi-check-circle"></i></div>
                    <div class="stats-info"><h3 id="activeGallery">-</h3><p>Active Gallery</p></div>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar Gallery</h5>
                <button class="btn btn-sm btn-outline-secondary" id="refreshBtn"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
            <div class="card-body">
                <div id="galleryContainer">
                    <div class="loading"><div class="spinner-border text-secondary" role="status"></div><p class="mt-2 text-muted">Memuat data...</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Hapus Gallery</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">Hapus gallery "<strong id="deleteTitle"></strong>"?</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-danger" id="confirmDelete"><i class="bi bi-trash me-1"></i>Hapus</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        let deleteId = null;
        const deleteModal = new bootstrap.Modal($('#deleteModal')[0]);
        
        function loadGallery() {
            $('#galleryContainer').html('<div class="loading"><div class="spinner-border text-secondary"></div><p class="mt-2 text-muted">Memuat data...</p></div>');
            
            $.ajax({
                url: 'index.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderGallery(response.data);
                        $('#totalGallery').text(response.data.length);
                        $('#activeGallery').text(response.data.filter(g => g.is_active == 1).length);
                    }
                },
                error: function() {
                    $('#galleryContainer').html('<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-circle"></i> Gagal memuat data</div>');
                }
            });
        }
        
        function renderGallery(items) {
            if (items.length === 0) {
                $('#galleryContainer').html('<div class="text-center py-4"><i class="bi bi-inbox display-4 text-muted"></i><p class="mt-2 text-muted">Belum ada gallery.</p><a href="create.php" class="btn btn-primary btn-sm">Tambah Gallery</a></div>');
                return;
            }
            
            let html = '<div class="table-responsive fade-in"><table class="table table-hover align-middle"><thead><tr><th>#</th><th>Gambar</th><th>Judul</th><th>Penulis</th><th>Deskripsi</th><th>Status</th><th>Aksi</th></tr></thead><tbody>';
            
            items.forEach(function(g, i) {
                let imgSrc = g.image.indexOf('http') === 0 ? g.image : '../../' + g.image;
                let status = g.is_active == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>';
                
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td><img src="' + imgSrc + '" class="table-img" onerror="this.src=\'https://via.placeholder.com/50x35?text=Err\'" /></td>';
                html += '<td><strong>' + escapeHtml(g.title) + '</strong></td>';
                html += '<td><span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>' + escapeHtml(g.author_name || 'Admin') + '</span></td>';
                html += '<td><small class="text-muted">' + escapeHtml(truncate(g.description, 50)) + '</small></td>';
                html += '<td>' + status + '</td>';
                html += '<td>';
                html += '<a href="edit.php?id=' + g.id + '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                html += '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + g.id + '" data-title="' + escapeHtml(g.title) + '"><i class="bi bi-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            $('#galleryContainer').html(html);
        }
        
        $(document).on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            $('#deleteTitle').text($(this).data('title'));
            deleteModal.show();
        });
        
        $('#confirmDelete').click(function() {
            if (!deleteId) return;
            
            const btn = $(this);
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            
            $.ajax({
                url: 'index.php?action=delete&id=' + deleteId,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    deleteModal.hide();
                    if (response.success) {
                        showAlert('success', response.message);
                        loadGallery();
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Gagal menghapus gallery');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Hapus');
                    deleteId = null;
                }
            });
        });
        
        $('#refreshBtn').click(function() {
            loadGallery();
        });
        
        function showAlert(type, message) {
            const alert = $('<div class="alert alert-' + type + ' alert-dismissible fade show"><i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            $('#alertContainer').html(alert);
            setTimeout(() => alert.alert('close'), 3000);
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            return $('<div>').text(text).html();
        }
        
        function truncate(text, len) {
            if (!text) return '';
            return text.length > len ? text.substring(0, len) + '...' : text;
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('created')) showAlert('success', 'Gallery berhasil ditambahkan!');
        if (urlParams.has('updated')) showAlert('success', 'Gallery berhasil diperbarui!');
        
        loadGallery();
    });
    </script>
</body>
</html>
