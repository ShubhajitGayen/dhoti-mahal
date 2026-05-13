<?php
// ============================================================
// Dhoti Mahal - User Profile / Dashboard
// ============================================================
$pageTitle = 'My Account';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();
$currentUser = getLoggedUser();
$db          = getDB();

// Fetch full user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$currentUser['id']]);
$user = $stmt->fetch();

$tab    = $_GET['tab'] ?? 'orders';
$errors = [];
$saved  = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $state   = trim($_POST['state'] ?? '');
        $pincode = trim($_POST['pincode'] ?? '');

        if (!$name) $errors[] = 'Name is required.';

        if (empty($errors)) {
            $db->prepare("UPDATE users SET name=?,phone=?,address=?,city=?,state=?,pincode=?,updated_at=NOW() WHERE id=?")
                ->execute([$name, $phone, $address, $city, $state, $pincode, $currentUser['id']]);
            $_SESSION['user_name'] = $name;
            $user['name']  = $name;
            $user['phone'] = $phone;
            $saved = true;
        }
    }
}

// Handle password change
$pwdErrors = [];
$pwdSaved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $pwdErrors[] = 'Invalid request.';
    } else {
        $old = $_POST['old_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $cnf = $_POST['confirm_new'] ?? '';
        if (!verifyPassword($old, $user['password'])) $pwdErrors[] = 'Current password is incorrect.';
        if (strlen($new) < 8) $pwdErrors[] = 'New password must be at least 8 characters.';
        if ($new !== $cnf) $pwdErrors[] = 'New passwords do not match.';
        if (empty($pwdErrors)) {
            $db->prepare("UPDATE users SET password=? WHERE id=?")
                ->execute([hashPassword($new), $currentUser['id']]);
            $pwdSaved = true;
        }
    }
}

$orders = getUserOrders((int)$currentUser['id']);
require_once __DIR__ . '/../includes/header.php';
?>
<script>
    window.__BASE_URL = '<?= BASE_URL ?>';
</script>

<div class="page-hero" style="padding:24px 0;">
    <div class="container">
        <h1>My Account</h1>
        <div class="breadcrumb"><a href="<?= BASE_URL ?>">Home</a><span>›</span><span>My Account</span></div>
    </div>
</div>

<div class="container">
    <div class="profile-layout">
        <!-- Sidebar -->
        <aside class="profile-sidebar">
            <div class="profile-avatar">
                <div class="avatar-circle"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
                <strong><?= sanitize($user['name']) ?></strong>
                <div style="font-size:13px;color:var(--muted);"><?= sanitize($user['email']) ?></div>
            </div>
            <nav class="profile-nav">
                <a href="?tab=orders" class="<?= $tab === 'orders' ? 'active' : '' ?>">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <a href="?tab=profile" class="<?= $tab === 'profile' ? 'active' : '' ?>">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
                <a href="?tab=password" class="<?= $tab === 'password' ? 'active' : '' ?>">
                    <i class="fas fa-key"></i> Change Password
                </a>
                <a href="<?= BASE_URL ?>pages/track-order.php"><i class="fas fa-truck"></i> Track Order</a>
                <a href="<?= BASE_URL ?>pages/logout.php" style="color:var(--crimson);">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Content -->
        <div class="profile-content">

            <?php if ($tab === 'orders'): ?>
                <h2>My Orders</h2>
                <?php if (empty($orders)): ?>
                    <div class="empty-state" style="padding:40px 0;">
                        <i class="fas fa-box-open"></i>
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders. Start shopping!</p>
                        <a href="<?= BASE_URL ?>pages/category.php" class="btn btn-primary">Browse Products</a>
                    </div>
                <?php else: ?>
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th colspan="2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><strong><?= sanitize($order['order_number']) ?></strong></td>
                                    <td><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                    <td><?= formatPrice((float)$order['total']) ?></td>
                                    <td><span
                                            class="status-badge status-<?= $order['payment_status'] ?>"><?= ucfirst($order['payment_status']) ?></span>
                                    </td>
                                    <td><span
                                            class="status-badge status-<?= $order['order_status'] ?>"><?= ucfirst($order['order_status']) ?></span>
                                    </td>
                                    <td>
                                        <a href="<?= BASE_URL ?>pages/track-order.php?order=<?= urlencode($order['order_number']) ?>"
                                            style="color:var(--crimson);font-size:13px;font-weight:600;">
                                            <i class="fas fa-truck"></i> Track
                                        </a>
                                        <?php if ($order['payment_status'] === 'pending'): ?>
                                            <a href="<?= BASE_URL ?>pages/payment.php?order=<?= urlencode($order['order_number']) ?>"
                                                style="color:var(--crimson);font-size:13px;font-weight:600;margin-left:12px;">
                                                <i class="fas fa-credit-card"></i> Pay Now
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

            <?php elseif ($tab === 'profile'): ?>
                <h2>Edit Profile</h2>
                <?php if ($saved): ?>
                    <div class="flash-message flash-success" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                        Profile updated successfully!</div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="flash-message flash-error" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                        <?= sanitize($errors[0]) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= sanitize($user['name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Mobile Number</label>
                            <input type="tel" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Email Address (cannot change)</label>
                        <input type="email" value="<?= sanitize($user['email']) ?>" disabled
                            style="background:var(--cream);">
                    </div>
                    <div class="form-group">
                        <label>Default Delivery Address</label>
                        <textarea name="address" rows="2"
                            placeholder="House/Flat, Street, Area"><?= sanitize($user['address'] ?? '') ?></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" value="<?= sanitize($user['city'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" value="<?= sanitize($user['state'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group" style="max-width:200px;">
                        <label>Pincode</label>
                        <input type="text" name="pincode" value="<?= sanitize($user['pincode'] ?? '') ?>" maxlength="6">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </form>

            <?php elseif ($tab === 'password'): ?>
                <h2>Change Password</h2>
                <?php if ($pwdSaved): ?>
                    <div class="flash-message flash-success" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                        Password changed successfully!</div>
                <?php endif; ?>
                <?php if (!empty($pwdErrors)): ?>
                    <div class="flash-message flash-error" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                        <?php foreach ($pwdErrors as $e): ?><div><?= sanitize($e) ?></div><?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" style="max-width:400px;">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required placeholder="Min. 8 characters">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_new" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Update Password</button>
                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>