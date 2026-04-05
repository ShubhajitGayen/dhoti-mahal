<?php
// ============================================================
// Dhoti Mahal - Admin Login
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) { header('Location: ' . BASE_URL . 'admin/dashboard.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $admin    = loginAdminWithCredentials($email, $password);
        if ($admin) {
            loginAdmin($admin);
            header('Location: ' . BASE_URL . 'admin/dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/main.css">
</head>
<body style="background: #1a1a2e; min-height: 100vh; display: flex; align-items: center; justify-content: center;">
<div class="auth-card" style="max-width:400px;">
    <div style="text-align:center;margin-bottom:24px;">
        <div style="font-size:2.5rem;">🪷</div>
        <h2 style="font-family:var(--font-head);color:var(--crimson);">Admin Panel</h2>
        <p style="color:var(--muted);font-size:13px;">Dhoti Mahal Management</p>
    </div>
    <?php if (!empty($errors)): ?>
        <div class="flash-message flash-error" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;"><?= sanitize($errors[0]) ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="email" required autofocus value="<?= sanitize($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-full" style="font-size:1rem;padding:13px;">
            <i class="fas fa-lock"></i> Login to Admin
        </button>
    </form>
    <div style="text-align:center;margin-top:16px;">
        <a href="<?= BASE_URL ?>" style="font-size:13px;color:var(--muted);">← Back to Website</a>
    </div>
</div>
</body>
</html>
