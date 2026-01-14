<?php
require_once '../../config.php';
requireLogin();

// API endpoint for AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'list') {
        $stmt = $pdo->query("SELECT a.*, u.name as author_name FROM articles a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }
    
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $pdo->prepare("SELECT image FROM articles WHERE id = ?");
        $stmt->execute([$id]);
        $article = $stmt->fetch();
        
        if ($article) {
            if ($article['image'] && strpos($article['image'], 'uploads/') === 0) {
                $path = '../../' . $article['image'];
                if (file_exists($path)) unlink($path);
            }
            $pdo->prepare("DELETE FROM articles WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Artikel berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Artikel tidak ditemukan']);
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
    <title>Article Management - Admin</title>
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

    <!-- Main Content -->
    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-newspaper me-2"></i>Article Management</h1>
            <a href="create.php" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Tambah Artikel</a>
        </header>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-primary"><i class="bi bi-newspaper"></i></div>
                    <div class="stats-info"><h3 id="totalArticles">-</h3><p>Total Artikel</p></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-success"><i class="bi bi-calendar3"></i></div>
                    <div class="stats-info"><h3><?= date('d M Y') ?></h3><p>Hari Ini</p></div>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar Artikel</h5>
                <button class="btn btn-sm btn-outline-secondary" id="refreshBtn"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
            <div class="card-body">
                <div id="articlesContainer">
                    <div class="loading"><div class="spinner-border text-secondary" role="status"></div><p class="mt-2 text-muted">Memuat data...</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Hapus Artikel</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">Hapus artikel "<strong id="deleteTitle"></strong>"?</div>
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
        
        function loadArticles() {
            $('#articlesContainer').html('<div class="loading"><div class="spinner-border text-secondary"></div><p class="mt-2 text-muted">Memuat data...</p></div>');
            
            $.ajax({
                url: 'index.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderArticles(response.data);
                        $('#totalArticles').text(response.data.length);
                    }
                },
                error: function() {
                    $('#articlesContainer').html('<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-circle"></i> Gagal memuat data</div>');
                }
            });
        }
        
        function renderArticles(articles) {
            if (articles.length === 0) {
                $('#articlesContainer').html('<div class="text-center py-4"><i class="bi bi-inbox display-4 text-muted"></i><p class="mt-2 text-muted">Belum ada artikel.</p><a href="create.php" class="btn btn-primary btn-sm">Tambah Artikel</a></div>');
                return;
            }
            
            let html = '<div class="table-responsive fade-in"><table class="table table-hover align-middle"><thead><tr><th>#</th><th>Gambar</th><th>Judul</th><th>Penulis</th><th>Konten</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>';
            
            articles.forEach(function(a, i) {
                let imgSrc = a.image ? (a.image.indexOf('http') === 0 ? a.image : '../../' + a.image) : '';
                
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td>';
                if (imgSrc) {
                    html += '<img src="' + imgSrc + '" class="table-img" onerror="this.src=\'https://via.placeholder.com/50x35?text=Err\'" />';
                } else {
                    html += '<span class="text-muted">-</span>';
                }
                html += '</td>';
                html += '<td><strong>' + escapeHtml(a.title) + '</strong></td>';
                html += '<td><span class="badge bg-light text-dark border"><i class="bi bi-person me-1"></i>' + escapeHtml(a.author_name || 'Admin') + '</span></td>';
                html += '<td><small class="text-muted">' + escapeHtml(truncate(a.content, 50)) + '</small></td>';
                html += '<td><small>' + formatDate(a.created_at) + '</small></td>';
                html += '<td>';
                html += '<a href="edit.php?id=' + a.id + '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                html += '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + a.id + '" data-title="' + escapeHtml(a.title) + '"><i class="bi bi-trash"></i></button>';
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            $('#articlesContainer').html(html);
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
                        loadArticles();
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Gagal menghapus artikel');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Hapus');
                    deleteId = null;
                }
            });
        });
        
        $('#refreshBtn').click(function() {
            loadArticles();
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
        
        function formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('created')) showAlert('success', 'Artikel berhasil ditambahkan!');
        if (urlParams.has('updated')) showAlert('success', 'Artikel berhasil diperbarui!');
        
        loadArticles();
    });
    </script>
</body>
</html>
