<?php
// ============================================================
// Dhoti Mahal - User Registration
// ============================================================
$pageTitle = 'Create Your Account';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) redirect(BASE_URL . 'pages/user-profile.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (!$name)  $errors[] = 'Full name is required.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (!preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = 'Valid 10-digit mobile number is required.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';

        if (empty($errors)) {
            $userId = registerUser($name, $email, $phone, $password);
            if ($userId === false) {
                $errors[] = 'This email address is already registered. Please login.';
            } else {
                $user = getUserById($userId);
                loginUser($user);
                setFlash('success', 'Account created! Welcome to ' . getSetting('site_name', SITE_NAME) . '!');
                redirect(BASE_URL . 'pages/user-profile.php');
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
        <h2>Create Account</h2>
        <p>Join us and enjoy faster checkout, order tracking & exclusive offers</p>
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                <ul style="margin:0;padding-left:18px;">
                    <?php foreach ($errors as $e): ?><li><?= sanitize($e) ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= sanitize($_POST['name'] ?? '') ?>" required autofocus placeholder="Your full name">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label>Mobile Number</label>
                <input type="tel" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>" required placeholder="10-digit mobile number">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Min. 8 characters">
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" required placeholder="Repeat password">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full" style="font-size:1rem;padding:13px;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        <div class="auth-divider"><span>Already have an account?</span></div>
        <a href="<?= BASE_URL ?>pages/login.php" class="btn btn-outline btn-full">
            <i class="fas fa-sign-in-alt"></i> Login
        </a>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>