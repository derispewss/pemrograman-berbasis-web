<?php
require_once '../../config.php';
requireLogin();

// API endpoint for AJAX
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'list') {
        $stmt = $pdo->query("SELECT id, username, name, photo, created_at FROM users ORDER BY created_at DESC");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll()]);
        exit;
    }
    
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        
        // Prevent self delete
        if ($id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Anda tidak dapat menghapus akun sendiri.']);
            exit;
        }
        
        $stmt = $pdo->prepare("SELECT photo FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user) {
            if ($user['photo'] && strpos($user['photo'], 'uploads/') === 0) {
                $path = '../../' . $user['photo'];
                if (file_exists($path)) unlink($path);
            }
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>User Management - Admin</title>
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

    <!-- Main Content -->
    <div class="main-content">
        <header class="content-header">
            <h1><i class="bi bi-people me-2"></i>User Management</h1>
            <a href="create.php" class="btn btn-primary"><i class="bi bi-person-plus-fill me-2"></i>Tambah User</a>
        </header>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="stats-card">
                    <div class="stats-icon bg-info"><i class="bi bi-people"></i></div>
                    <div class="stats-info"><h3 id="totalUsers">-</h3><p>Total User</p></div>
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
                <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Daftar User</h5>
                <button class="btn btn-sm btn-outline-secondary" id="refreshBtn"><i class="bi bi-arrow-clockwise"></i></button>
            </div>
            <div class="card-body">
                <div id="usersContainer">
                    <div class="loading"><div class="spinner-border text-secondary" role="status"></div><p class="mt-2 text-muted">Memuat data...</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Hapus User</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">Hapus user "<strong id="deleteName"></strong>"?</div>
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
        const currentUserId = <?= $_SESSION['user_id'] ?>;
        
        function loadUsers() {
            $('#usersContainer').html('<div class="loading"><div class="spinner-border text-secondary"></div><p class="mt-2 text-muted">Memuat data...</p></div>');
            
            $.ajax({
                url: 'index.php?action=list',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        renderUsers(response.data);
                        $('#totalUsers').text(response.data.length);
                    }
                },
                error: function() {
                    $('#usersContainer').html('<div class="text-center py-4 text-danger"><i class="bi bi-exclamation-circle"></i> Gagal memuat data</div>');
                }
            });
        }
        
        function renderUsers(users) {
            if (users.length === 0) {
                $('#usersContainer').html('<div class="text-center py-4"><i class="bi bi-people display-4 text-muted"></i><p class="mt-2 text-muted">Belum ada user.</p><a href="create.php" class="btn btn-primary btn-sm">Tambah User</a></div>');
                return;
            }
            
            let html = '<div class="table-responsive fade-in"><table class="table table-hover align-middle"><thead><tr><th>#</th><th>Avatar</th><th>Name</th><th>Username</th><th>Joined</th><th>Aksi</th></tr></thead><tbody>';
            
            users.forEach(function(u, i) {
                let imgSrc = u.photo ? (u.photo.indexOf('http') === 0 ? u.photo : '../../' + u.photo) : '';
                let isMe = u.id == currentUserId;
                
                html += '<tr>';
                html += '<td>' + (i + 1) + '</td>';
                html += '<td>';
                if (imgSrc) {
                    html += '<img src="' + imgSrc + '" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src=\'https://via.placeholder.com/40?text=U\'" />';
                } else {
                    html += '<div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="bi bi-person"></i></div>';
                }
                html += '</td>';
                html += '<td><strong>' + escapeHtml(u.name) + '</strong>' + (isMe ? ' <span class="badge bg-info text-dark">You</span>' : '') + '</td>';
                html += '<td>' + escapeHtml(u.username) + '</td>';
                html += '<td><small>' + formatDate(u.created_at) + '</small></td>';
                html += '<td>';
                html += '<a href="edit.php?id=' + u.id + '" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-pencil"></i></a>';
                if (!isMe) {
                    html += '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + u.id + '" data-name="' + escapeHtml(u.name) + '"><i class="bi bi-trash"></i></button>';
                }
                html += '</td>';
                html += '</tr>';
            });
            
            html += '</tbody></table></div>';
            $('#usersContainer').html(html);
        }
        
        $(document).on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            $('#deleteName').text($(this).data('name'));
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
                        loadUsers();
                    } else {
                        showAlert('danger', response.message);
                    }
                },
                error: function() {
                    showAlert('danger', 'Gagal menghapus user');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="bi bi-trash me-1"></i>Hapus');
                    deleteId = null;
                }
            });
        });
        
        $('#refreshBtn').click(function() {
            loadUsers();
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
        
        function formatDate(date) {
            if (!date) return '';
            const d = new Date(date);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
            return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        }
        
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('created')) showAlert('success', 'User berhasil ditambahkan!');
        if (urlParams.has('updated')) showAlert('success', 'User berhasil diperbarui!');
        
        loadUsers();
    });
    </script>
</body>
</html>
