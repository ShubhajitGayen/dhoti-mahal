<?php
// ============================================================
// Dhoti Mahal - User Login
// ============================================================
$pageTitle = 'Login to Your Account';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) redirect(BASE_URL . 'pages/user-profile.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$email || !$password) {
            $errors[] = 'Email and password are required.';
        } else {
            $user = loginWithCredentials($email, $password);
            if ($user) {
                loginUser($user);
                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                $redirect = $_GET['redirect'] ?? BASE_URL . 'pages/user-profile.php';
                if (!str_starts_with($redirect, BASE_URL)) {
                    if (str_starts_with($redirect, '/')) {
                        $redirect = BASE_URL . ltrim($redirect, '/');
                    } else {
                        $redirect = BASE_URL . 'pages/user-profile.php';
                    }
                }
                redirect($redirect);
            } else {
                $errors[] = 'Invalid email or password.';
            }
        }
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<script>
    window.__BASE_URL = '<?= BASE_URL ?>';
</script>
<div class="auth-wrapper">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p>Login to view your orders, track deliveries and more</p>
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                <?= sanitize($errors[0]) ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus
                    placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Your password">
                <a href="<?= BASE_URL ?>pages/forgot-password.php" style="font-size:13px;color:var(--muted);">
                    <i class="fas fa-question-circle"></i> Forgot Password?
                </a>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;font-size:1rem;padding:13px;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        <div class="auth-divider"><span>New here?</span></div>
        <a href="<?= BASE_URL ?>pages/register.php" class="btn btn-outline btn-full">
            <i class="fas fa-user-plus"></i> Create an Account
        </a>
        <div style="text-align:center;margin-top:16px;">
            <a href="<?= BASE_URL ?>pages/track-order.php" style="font-size:13px;color:var(--muted);">
                <i class="fas fa-truck"></i> Track order without account
            </a>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>