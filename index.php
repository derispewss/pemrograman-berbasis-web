<?php
require_once 'config.php';

// Fetch all articles
$stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $stmt->fetchAll();

// Helper for image path
function getImageSrc($image) {
    if (empty($image)) return '';
    if (strpos($image, 'http') === 0) return $image;
    return $image; // uploads/xxx.jpg - relative from root
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>My Daily Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">My Daily Journal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#article">Article</a></li>
                    <li class="nav-item"><a class="nav-link" href="#gallery">Gallery</a></li>
                    <li class="nav-item"><a class="nav-link" href="#schedule">Schedule</a></li>
                    <li class="nav-item"><a class="nav-link" href="#aboutme">About Me</a></li>
                    <li class="nav-item">
                        <?php if (isLoggedIn()): ?>
                            <a class="nav-link" href="admin/index.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                        <?php else: ?>
                            <a class="nav-link" href="admin/login.php"><i class="bi bi-box-arrow-in-right me-1"></i>Login</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section id="hero" class="text-center text-sm-start">
        <div class="container">
            <div class="d-sm-flex flex-sm-row-reverse align-items-center">
                <img src="https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?w=400" class="hero-image mb-4 mb-sm-0" alt="Journal" />
                <div class="me-sm-5">
                    <h1>Create Memories, Save Memories, Everyday</h1>
                    <p class="lead">Mencatat semua kegiatan sehari-hari yang ada tanpa terkecuali</p>
                    <h6>
                        <span id="tanggal"></span> <span id="jam"></span>
                    </h6>
                </div>
            </div>
        </div>
    </section>

    <!-- ARTICLE -->
    <section id="article" class="text-center">
        <div class="container">
            <h1 class="fw-bold display-5">Article</h1>
            <div class="row row-cols-1 row-cols-md-4 g-4 justify-content-center" id="articleList">
                <?php if (empty($articles)): ?>
                    <div class="col-12">
                        <p class="text-muted">Belum ada artikel.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <?php $imgSrc = getImageSrc($article['image']); ?>
                        <div class="col">
                            <div class="card article-card h-100">
                                <?php if ($imgSrc): ?>
                                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top" alt="<?= htmlspecialchars($article['title']) ?>" onerror="this.src='https://via.placeholder.com/400x180?text=No+Image'" />
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/400x180?text=No+Image" class="card-img-top" alt="No Image" />
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($article['title']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars(truncateText($article['content'], 80)) ?></p>
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center">
                                    <small class="text-muted"><?= formatDate($article['created_at']) ?></small>
                                    <button class="btn btn-read-more btn-sm" data-bs-toggle="modal" data-bs-target="#modal<?= $article['id'] ?>">Read More</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Modal -->
                        <div class="modal fade" id="modal<?= $article['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($article['title']) ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php if ($imgSrc): ?>
                                            <img src="<?= htmlspecialchars($imgSrc) ?>" class="img-fluid rounded mb-3 w-100" style="max-height:300px;object-fit:cover;" alt="" onerror="this.style.display='none'" />
                                        <?php endif; ?>
                                        <p><?= nl2br(htmlspecialchars($article['content'])) ?></p>
                                        <small class="text-muted"><?= formatDate($article['created_at']) ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- GALLERY -->
    <section id="gallery" class="text-center">
        <div class="container">
            <h1 class="fw-bold display-5">Gallery</h1>
            <div id="carouselGallery" class="carousel slide">
                <div class="carousel-inner">
                    <div class="carousel-item active">
                        <img src="https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=1200" class="d-block w-100" alt="Gallery" />
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1200" class="d-block w-100" alt="Gallery" />
                    </div>
                    <div class="carousel-item">
                        <img src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?w=1200" class="d-block w-100" alt="Gallery" />
                    </div>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselGallery" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselGallery" data-bs-slide="next">
                    <span class="carousel-control-next-icon"></span>
                </button>
            </div>
        </div>
    </section>

    <!-- SCHEDULE -->
    <section id="schedule" class="text-center">
        <div class="container">
            <h1 class="fw-bold display-5">Schedule</h1>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-lg-6 g-4">
                <div class="col"><div class="schedule-card"><i class="bi bi-book"></i><h5>Membaca</h5><p>Menambah wawasan setiap pagi.</p></div></div>
                <div class="col"><div class="schedule-card"><i class="bi bi-laptop"></i><h5>Menulis</h5><p>Mencatat pengalaman harian.</p></div></div>
                <div class="col"><div class="schedule-card"><i class="bi bi-people"></i><h5>Diskusi</h5><p>Bertukar ide dengan teman.</p></div></div>
                <div class="col"><div class="schedule-card"><i class="bi bi-bicycle"></i><h5>Olahraga</h5><p>Menjaga kesehatan tubuh.</p></div></div>
                <div class="col"><div class="schedule-card"><i class="bi bi-film"></i><h5>Movie</h5><p>Menonton film favorit.</p></div></div>
                <div class="col"><div class="schedule-card"><i class="bi bi-bag"></i><h5>Belanja</h5><p>Membeli kebutuhan bulanan.</p></div></div>
            </div>
        </div>
    </section>

    <!-- ABOUT ME -->
    <section id="aboutme" class="text-center">
        <div class="container">
            <h1 class="fw-bold display-5">About Me</h1>
            <div class="accordion" id="accordionAbout">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Universitas Dian Nuswantoro Semarang (2024-Now)
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show" data-bs-parent="#accordionAbout">
                        <div class="accordion-body text-start">
                            Saat ini menempuh pendidikan S1 Teknik Informatika. Fokus pada pengembangan web dan aplikasi mobile.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#c2">
                            SMA Negeri 1 Semarang (2021-2024)
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse" data-bs-parent="#accordionAbout">
                        <div class="accordion-body text-start">
                            Menyelesaikan pendidikan menengah atas dengan prestasi yang membanggakan.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#c3">
                            SMP Negeri 2 Semarang (2018-2021)
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse" data-bs-parent="#accordionAbout">
                        <div class="accordion-body text-start">
                            Masa awal menemukan passion dalam bidang teknologi dan programming.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="text-center">
        <div class="container">
            <div class="social-icons">
                <i class="bi bi-instagram"></i>
                <i class="bi bi-twitter"></i>
                <i class="bi bi-whatsapp"></i>
            </div>
            <p>My Daily Journal &copy; <?= date('Y') ?></p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
