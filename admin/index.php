<?php
require_once '../config.php';
requireLogin();

// API endpoint for AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'list') {
        $stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
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
                $path = '../' . $article['image'];
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

// Helper function for image path
function getImageUrl($image) {
    if (empty($image)) return '';
    if (strpos($image, 'http') === 0) return $image;
    return '../' . $image;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard - Admin</title>
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
        .stats-card { background: #fff; border-radius: 10px; padding: 1.5rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem; }
        .stats-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; color: #fff; }
        .stats-icon.bg-primary { background: #e74c3c; }
        .stats-icon.bg-success { background: #27ae60; }
        .stats-info h3 { font-size: 1.5rem; font-weight: 600; margin: 0; }
        .stats-info p { color: #6c757d; margin: 0; font-size: 0.85rem; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .card-header { background: #fff; border-bottom: 1px solid #eee; padding: 1rem 1.25rem; }
        .table-img { width: 50px; height: 35px; object-fit: cover; border-radius: 5px; background: #eee; }
        .btn-primary { background: #e74c3c; border: none; }
        .btn-primary:hover { background: #c0392b; }
        .user-info { color: rgba(255,255,255,0.7); font-size: 0.85rem; padding: 1rem; background: rgba(0,0,0,0.1); border-radius: 8px; margin-bottom: 1rem; }
        .loading { text-align: center; padding: 2rem; }
        .fade-in { animation: fadeIn 0.3s ease; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand"><i class="bi bi-journal-richtext me-2"></i>Admin Panel</div>
        <div class="user-info"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name']) ?></div>
        <nav class="sidebar-nav">
            <a href="index.php" class="active"><i class="bi bi-grid"></i>Dashboard</a>
            <a href="create.php"><i class="bi bi-plus-circle"></i>Tambah Artikel</a>
            <a href="../index.php"><i class="bi bi-house"></i>Lihat Website</a>
            <a href="logout.php" class="logout"><i class="bi bi-box-arrow-left"></i>Logout</a>
        </nav>
    </div>

    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-grid me-2"></i>Dashboard</h1>
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
        
        // Load articles
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
        
        // Render articles table
        function renderArticles(articles) {
            if (articles.length === 0) {
                $('#articlesContainer').html('<div class="text-center py-4"><i class="bi bi-inbox display-4 text-muted"></i><p class="mt-2 text-muted">Belum ada artikel.</p><a href="create.php" class="btn btn-primary btn-sm">Tambah Artikel</a></div>');
                return;
            }
            
            let html = '<div class="table-responsive fade-in"><table class="table table-hover align-middle"><thead><tr><th>#</th><th>Gambar</th><th>Judul</th><th>Konten</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>';
            
            articles.forEach(function(a, i) {
                let imgSrc = '';
                if (a.image) {
                    if (a.image.indexOf('http') === 0) {
                        imgSrc = a.image;
                    } else {
                        imgSrc = '../' + a.image;
                    }
                }
                
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
        
        // Delete button click
        $(document).on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            $('#deleteTitle').text($(this).data('title'));
            deleteModal.show();
        });
        
        // Confirm delete
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
        
        // Refresh button
        $('#refreshBtn').click(function() {
            loadArticles();
        });
        
        // Show alert
        function showAlert(type, message) {
            const alert = $('<div class="alert alert-' + type + ' alert-dismissible fade show"><i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-2"></i>' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>');
            $('#alertContainer').html(alert);
            setTimeout(() => alert.alert('close'), 3000);
        }
        
        // Helper functions
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
        
        // Check URL params for messages
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('created')) showAlert('success', 'Artikel berhasil ditambahkan!');
        if (urlParams.has('updated')) showAlert('success', 'Artikel berhasil diperbarui!');
        
        // Initial load
        loadArticles();
    });
    </script>
</body>
</html>
