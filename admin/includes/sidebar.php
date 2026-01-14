<!-- Mobile Toggle -->
<button class="mobile-toggle" id="mobileToggle">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-journal-richtext me-2"></i>
        <span>Admin Panel</span>
    </div>
    
    <div class="user-info">
        <?php if (!empty($_SESSION['user_photo'])): 
            $photoPath = $_SESSION['user_photo'];
            // Check if it's already a full URL
            if (strpos($photoPath, 'http') !== 0) {
                // Determine absolute URL relative to site
                $userPhotoUrl = SITE_URL . '/' . ltrim($photoPath, '/');
            } else {
                $userPhotoUrl = $photoPath;
            }
        ?>
            <img src="<?= htmlspecialchars($userPhotoUrl) ?>" class="user-avatar" alt="Profile">
        <?php else: ?>
            <div class="user-avatar-placeholder"><i class="bi bi-person-circle"></i></div>
        <?php endif; ?>
        <span><?= htmlspecialchars($_SESSION['user_name']) ?></span>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?= $baseUrl ?>index.php" class="<?= $currentPage == 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-grid"></i>
            <span>Dashboard</span>
        </a>
        
        <div class="sidebar-section"><span>Content</span></div>
        <a href="<?= $baseUrl ?>articles/index.php" class="<?= $currentPage == 'articles' ? 'active' : '' ?>">
            <i class="bi bi-newspaper"></i>
            <span>Articles</span>
        </a>
        <a href="<?= $baseUrl ?>gallery/index.php" class="<?= $currentPage == 'gallery' ? 'active' : '' ?>">
            <i class="bi bi-images"></i>
            <span>Gallery</span>
        </a>
        <a href="<?= $baseUrl ?>users/index.php" class="<?= $currentPage == 'users' ? 'active' : '' ?>">
            <i class="bi bi-people"></i>
            <span>Users</span>
        </a>
        
        <div class="sidebar-section"><span>Settings</span></div>
        <a href="<?= $baseUrl ?>profile.php" class="<?= $currentPage == 'profile' ? 'active' : '' ?>">
            <i class="bi bi-person"></i>
            <span>Profile</span>
        </a>
        
        <div class="sidebar-section"><span>Other</span></div>
        <a href="<?= $rootUrl ?>">
            <i class="bi bi-house"></i>
            <span>Lihat Website</span>
        </a>
        <a href="<?= $baseUrl ?>logout.php" class="logout">
            <i class="bi bi-box-arrow-left"></i>
            <span>Logout</span>
        </a>
    </nav>
</div>

<script>
// Sidebar mobile toggle only (vanilla JS - no jQuery dependency)
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebar = document.getElementById('sidebar');
    
    if (mobileToggle && sidebarOverlay && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
    }
});
</script>
