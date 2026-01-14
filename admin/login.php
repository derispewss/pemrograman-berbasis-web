<?php
require_once '../config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_photo'] = $user['photo'];
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 10px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); padding: 2.5rem; width: 100%; max-width: 400px; }
        .login-card h2 { font-weight: 600; color: #2c3e50; margin-bottom: 0.5rem; }
        .login-card p { color: #6c757d; margin-bottom: 1.5rem; }
        .form-control { border-radius: 8px; padding: 0.75rem 1rem; }
        .form-control:focus { border-color: #e74c3c; box-shadow: 0 0 0 3px rgba(231,76,60,0.1); }
        .btn-login { background: #e74c3c; border: none; padding: 0.75rem; border-radius: 8px; font-weight: 500; }
        .btn-login:hover { background: #c0392b; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center">
            <i class="bi bi-journal-richtext text-danger" style="font-size: 3rem;"></i>
            <h2>Login Admin</h2>
            <p>Masuk untuk mengelola artikel</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger py-2">
                <i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" name="username" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required />
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" placeholder="Masukkan password" required />
            </div>
            <button type="submit" class="btn btn-login btn-danger w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
        </form>
        
        <div class="text-center mt-4">
            <a href="../index.php" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Kembali ke Homepage
            </a>
        </div>
    </div>
</body>
</html>
