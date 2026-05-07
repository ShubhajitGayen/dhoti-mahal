<?php
// ============================================================
// Dhoti Mahal - Authentication & Session Handling
// ============================================================

require_once __DIR__ . '/../config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    $secureCookie = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secureCookie,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
// ---- User Auth ----

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function getLoggedUser(): ?array
{
    if (!isLoggedIn()) return null;
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'phone' => $_SESSION['user_phone'] ?? '',
        'address' => $_SESSION['user_address'] ?? '',
        'city' => $_SESSION['user_city'] ?? '',
        'state' => $_SESSION['user_state'] ?? '',
        'pincode' => $_SESSION['user_pincode'] ?? '',
    ];
}

function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_phone'] = $user['phone'] ?? '';
    $_SESSION['user_address'] = $user['address'] ?? '';
    $_SESSION['user_city'] = $user['city'] ?? '';
    $_SESSION['user_state'] = $user['state'] ?? '';
    $_SESSION['user_pincode'] = $user['pincode'] ?? '';
}

function logoutUser(): void
{
    unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_phone'], $_SESSION['user_address'], $_SESSION['user_city'], $_SESSION['user_state'], $_SESSION['user_pincode']);
    session_destroy();
}

function requireLogin(string $redirectTo = ''): void
{
    if (!isLoggedIn()) {
        $redirect = $redirectTo ?: BASE_URL . 'pages/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']);
        header("Location: $redirect");
        exit;
    }
}

// ---- Admin Auth ----

function isAdminLoggedIn(): bool
{
    return !empty($_SESSION['admin_id']);
}

function loginAdmin(array $admin): void
{
    session_regenerate_id(true);
    $_SESSION['admin_id']    = $admin['id'];
    $_SESSION['admin_name']  = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
}

function logoutAdmin(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
    session_destroy();
}

function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

function isOwnerAuthorized(): bool
{
    $expiresAt = $_SESSION['owner_verified_at'] ?? 0;
    return !empty($_SESSION['owner_verified']) && is_numeric($expiresAt) && (time() - (int)$expiresAt) < OWNER_AUTH_TIMEOUT;
}

function authorizeOwner(string $username, string $password): bool
{
    if (!hash_equals(OWNER_USERNAME, $username)) {
        return false;
    }
    return password_verify($password, OWNER_PASSWORD_HASH);
}

function deauthorizeOwner(): void
{
    unset($_SESSION['owner_verified'], $_SESSION['owner_verified_at']);
}

// ---- Password ----

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

// ---- Register / Login from DB ----

function registerUser(string $name, string $email, string $phone, string $password): int|false
{
    $db = getDB();
    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) return false;

    $hash = hashPassword($password);
    $stmt = $db->prepare("INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)");
    $stmt->execute([$name, $email, $phone, $hash]);
    return (int)$db->lastInsertId();
}

function getUserById(int $id): array|false
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function refreshUserSession(): void
{
    if (isLoggedIn()) {
        $user = getUserById($_SESSION['user_id']);
        if ($user) {
            loginUser($user);
        }
    }
}

function loginWithCredentials(string $email, string $password): array|false
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && verifyPassword($password, $user['password'])) {
        return $user;
    }
    return false;
}

function loginAdminWithCredentials(string $email, string $password): array|false
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admins WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    if ($admin && verifyPassword($password, $admin['password'])) {
        return $admin;
    }
    return false;
}
