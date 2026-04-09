<?php
// ============================================================
// Dhoti Mahal - Forgot Password
// ============================================================
$pageTitle = 'Reset Your Password';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../notifications/sendOtpEmail.php';

if (isLoggedIn()) redirect(BASE_URL . 'pages/user-profile.php');

if (isset($_GET['do']) && $_GET['do'] === 'restart') {
    $keys = ['fp_step', 'fp_email', 'fp_otp_hash', 'fp_expires', 'fp_user_id', 'fp_attempts', 'fp_verified', 'fp_dev_otp', 'fp_mail_error'];
    foreach ($keys as $k) unset($_SESSION[$k]);
    redirect(BASE_URL . 'pages/forgot-password.php');
}

$step   = $_SESSION['fp_step'] ?? 'request';
$errors = [];

// STEP 1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            $db   = getDB();
            $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $otp     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                $expires = time() + (15 * 60);
                $_SESSION['fp_step']      = 'verify';
                $_SESSION['fp_email']     = $email;
                $_SESSION['fp_otp_hash']  = password_hash($otp, PASSWORD_BCRYPT);
                $_SESSION['fp_expires']   = $expires;
                $_SESSION['fp_user_id']   = $user['id'];
                $_SESSION['fp_attempts']  = 0;
                $sent = sendOtpEmail($email, $user["name"], $otp);
                if (!$sent) $_SESSION["fp_dev_otp"] = $otp;
            }
            $_SESSION['fp_step'] = 'verify';
            setFlash('info', 'If that email is registered, a 6-digit code has been sent to it.');
            redirect(BASE_URL . 'pages/forgot-password.php');
        }
    }
}

// STEP 2
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify') {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } elseif (empty($_SESSION['fp_otp_hash']) || empty($_SESSION['fp_expires'])) {
        $errors[] = 'Session expired. Please start again.';
        fp_clear_session();
    } elseif (time() > $_SESSION['fp_expires']) {
        $errors[] = 'Your reset code has expired. Please request a new one.';
        fp_clear_session();
    } else {
        $_SESSION['fp_attempts'] = ($_SESSION['fp_attempts'] ?? 0) + 1;
        if ($_SESSION['fp_attempts'] > 5) {
            $errors[] = 'Too many incorrect attempts. Please request a new reset code.';
            fp_clear_session();
        } else {
            $entered = trim($_POST['otp'] ?? '');
            if (!preg_match('/^\d{6}$/', $entered)) {
                $errors[] = 'Please enter the 6-digit code.';
            } elseif (!password_verify($entered, $_SESSION['fp_otp_hash'])) {
                $remaining = 5 - $_SESSION['fp_attempts'];
                $errors[]  = 'Incorrect code. ' . ($remaining > 0 ? $remaining . ' attempt' . ($remaining === 1 ? '' : 's') . ' remaining.' : '');
            } else {
                $_SESSION['fp_step']     = 'reset';
                $_SESSION['fp_verified'] = true;
                unset($_SESSION['fp_otp_hash'], $_SESSION['fp_dev_otp']);
                redirect(BASE_URL . 'pages/forgot-password.php');
            }
        }
    }
}

// STEP 3
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset') {
    if (!validateCSRF($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } elseif (empty($_SESSION['fp_verified']) || empty($_SESSION['fp_user_id'])) {
        $errors[] = 'Session expired. Please start again.';
        fp_clear_session();
    } else {
        $new_pw  = $_POST['password'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        if (strlen($new_pw) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif ($new_pw !== $confirm) {
            $errors[] = 'Passwords do not match.';
        } else {
            $db   = getDB();
            $hash = password_hash($new_pw, PASSWORD_BCRYPT, ['cost' => 12]);
            $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?")->execute([$hash, $_SESSION['fp_user_id']]);
            fp_clear_session();
            setFlash('success', 'Password updated! You can now log in with your new password.');
            redirect(BASE_URL . 'pages/login.php');
        }
    }
}

function fp_clear_session()
{
    $keys = ['fp_step', 'fp_email', 'fp_otp_hash', 'fp_expires', 'fp_user_id', 'fp_attempts', 'fp_verified', 'fp_dev_otp', 'fp_mail_error'];
    foreach ($keys as $k) unset($_SESSION[$k]);
}

$step = $_SESSION['fp_step'] ?? 'request';
if ($step === 'verify' && empty($_SESSION['fp_expires'])) {
    fp_clear_session();
    $step = 'request';
}
if ($step === 'reset'  && empty($_SESSION['fp_verified'])) {
    fp_clear_session();
    $step = 'request';
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Step title -->
        <?php if ($step === 'request'): ?>
            <h2>Forgot Password?</h2>
            <p>Enter your email and we'll send you a reset code</p>
        <?php elseif ($step === 'verify'): ?>
            <h2>Enter Reset Code</h2>
            <p>We sent a 6-digit code to <strong><?= sanitize($_SESSION['fp_email'] ?? '') ?></strong></p>
        <?php elseif ($step === 'reset'): ?>
            <h2>Set New Password</h2>
            <p>Choose a strong password for your account</p>
        <?php endif; ?>

        <!-- Step progress dots -->
        <div style="display:flex;align-items:center;justify-content:center;gap:6px;margin:16px 0 20px;">
            <?php
            $step_order = ['request' => 1, 'verify' => 2, 'reset' => 3];
            $cur_num    = $step_order[$step];
            foreach (['request' => 'Email', 'verify' => 'Code', 'reset' => 'Reset'] as $s => $label):
                $num  = $step_order[$s];
                $done = $num < $cur_num;
                $here = $num === $cur_num;
            ?>
                <?php if ($num > 1): ?>
                    <div
                        style="flex:1;max-width:40px;height:2px;background:<?= $done ? 'var(--gold,#C9943C)' : 'var(--border,#E0CFA8)' ?>;border-radius:1px;">
                    </div>
                <?php endif; ?>
                <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
                    <div
                        style="width:28px;height:28px;border-radius:50%;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;background:<?= $done ? '#27AE60' : ($here ? 'var(--primary,#C9943C)' : 'transparent') ?>;color:<?= ($done || $here) ? '#fff' : 'var(--muted,#8C7B60)' ?>;border:2px solid <?= $done ? '#27AE60' : ($here ? 'var(--primary,#C9943C)' : 'var(--border,#E0CFA8)') ?>;transition:.2s;">
                        <?= $done ? '&#10003;' : $num ?>
                    </div>
                    <span
                        style="font-size:9px;letter-spacing:.5px;text-transform:uppercase;color:<?= $here ? 'var(--primary,#C9943C)' : 'var(--muted,#8C7B60)' ?>;"><?= $label ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Flash messages -->
        <?php $flash = getFlash();
        if ($flash): ?>
            <div class="flash-message flash-<?= $flash['type'] ?>"
                style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                <?= sanitize($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Errors -->
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="border-radius:4px;padding:12px 16px;margin-bottom:16px;">
                <?= sanitize($errors[0]) ?>
            </div>
        <?php endif; ?>

        <!-- Dev OTP banner -->
        <?php if (!empty($_SESSION['fp_dev_otp']) && $step === 'verify'): ?>
            <div style="background:#2C2416;border-radius:6px;padding:14px;text-align:center;margin-bottom:18px;">
                <p
                    style="font-size:10px;color:rgba(255,255,255,.45);letter-spacing:1.5px;text-transform:uppercase;margin-bottom:6px;">
                    Dev mode — email not sent
                </p>



            </div>
        <?php endif; ?>

        <!-- STEP 1 — Email -->
        <?php if ($step === 'request'): ?>
            <form method="POST">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                <input type="hidden" name="action" value="request">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required autofocus
                        placeholder="your@email.com">
                </div>
                <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;font-size:1rem;padding:13px;">
                    <i class="fas fa-paper-plane"></i> Send Reset Code
                </button>
            </form>

            <!-- STEP 2 — OTP -->
        <?php elseif ($step === 'verify'): ?>
            <form method="POST" id="verifyForm">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                <input type="hidden" name="action" value="verify">
                <div class="form-group">
                    <label>6-Digit Code</label>
                    <input type="text" name="otp" id="otpInput" maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                        autocomplete="one-time-code" required autofocus placeholder="000000"
                        style="text-align:center;font-size:1.6rem;font-weight:600;letter-spacing:10px;font-family:monospace;">
                </div>
                <p style="text-align:center;font-size:13px;color:var(--muted);margin:-8px 0 16px;" id="expiryMsg">
                    Code expires in <strong id="countdown">15:00</strong>
                </p>
                <button type="submit" class="btn btn-primary btn-full" style="font-size:1rem;padding:13px;">
                    <i class="fas fa-check-circle"></i> Verify Code
                </button>
            </form>
            <div style="text-align:center;margin-top:14px;">
                <a href="<?= BASE_URL ?>pages/forgot-password.php?do=restart" style="font-size:13px;color:var(--muted);">
                    <i class="fas fa-redo"></i> Use a different email
                </a>
            </div>

            <!-- STEP 3 — New password -->
        <?php elseif ($step === 'reset'): ?>
            <form method="POST" id="resetForm">
                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= generateCSRF() ?>">
                <input type="hidden" name="action" value="reset">
                <div class="form-group">
                    <label>New Password</label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="pwInput" minlength="8" required autofocus
                            placeholder="Min 8 characters" oninput="checkStrength(this.value)">
                        <button type="button" onclick="togglePw('pwInput',this)"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;font-size:14px;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div style="display:flex;gap:3px;margin-top:6px;">
                        <span id="sb1"
                            style="flex:1;height:3px;border-radius:2px;background:var(--border);transition:.2s;"></span>
                        <span id="sb2"
                            style="flex:1;height:3px;border-radius:2px;background:var(--border);transition:.2s;"></span>
                        <span id="sb3"
                            style="flex:1;height:3px;border-radius:2px;background:var(--border);transition:.2s;"></span>
                        <span id="sb4"
                            style="flex:1;height:3px;border-radius:2px;background:var(--border);transition:.2s;"></span>
                    </div>
                    <p id="strengthTxt" style="font-size:12px;color:var(--muted);margin-top:4px;">Enter a password</p>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirm" id="pwConfirm" minlength="8" required
                            placeholder="Repeat password">
                        <button type="button" onclick="togglePw('pwConfirm',this)"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);padding:4px;font-size:14px;">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;font-size:1rem;padding:13px;">
                    <i class="fas fa-save"></i> Save New Password
                </button>
            </form>
        <?php endif; ?>

        <!-- Back to login -->
        <div style="text-align:center;margin-top:16px;">
            <a href="<?= BASE_URL ?>pages/login.php" style="font-size:13px;color:var(--muted);">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>

    </div>
</div>

<script>
    window.__BASE_URL = '<?= BASE_URL ?>';

    // OTP: digits only + auto-submit
    var otpInput = document.getElementById('otpInput');
    if (otpInput) {
        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length === 6) document.getElementById('verifyForm').submit();
        });
    }

    // Countdown timer
    var cdEl = document.getElementById('countdown');
    if (cdEl) {
        var expires = <?= isset($_SESSION['fp_expires']) ? (int)$_SESSION['fp_expires'] : 0 ?>;

        function tick() {
            var left = expires - Math.floor(Date.now() / 1000);
            if (left <= 0) {
                cdEl.textContent = '00:00';
                document.getElementById('expiryMsg').innerHTML =
                    'Code expired. <a href="<?= BASE_URL ?>pages/forgot-password.php?do=restart" style="color:var(--primary,#C9943C);">Request a new one</a>';
                return;
            }
            var m = String(Math.floor(left / 60)).padStart(2, '0');
            var s = String(left % 60).padStart(2, '0');
            cdEl.textContent = m + ':' + s;
            setTimeout(tick, 1000);
        }
        tick();
    }

    // Toggle password visibility
    function togglePw(id, btn) {
        var inp = document.getElementById(id);
        var icon = btn.querySelector('i');
        if (!inp) return;
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            inp.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }

    // Password strength meter
    var strengthColors = {
        weak: '#E74C3C',
        fair: '#F39C12',
        good: '#F1C40F',
        strong: '#27AE60'
    };

    function checkStrength(pw) {
        var score = 0;
        if (pw.length >= 8) score++;
        if (pw.length >= 12) score++;
        if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
        if (/\d/.test(pw)) score++;
        if (/[^a-zA-Z0-9]/.test(pw)) score++;
        var level = score <= 1 ? 'weak' : score <= 2 ? 'fair' : score <= 3 ? 'good' : 'strong';
        var fills = {
            weak: 1,
            fair: 2,
            good: 3,
            strong: 4
        };
        var labels = {
            weak: 'Weak',
            fair: 'Fair',
            good: 'Good',
            strong: 'Strong'
        };
        for (var i = 1; i <= 4; i++) {
            var bar = document.getElementById('sb' + i);
            if (bar) bar.style.background = i <= fills[level] ? strengthColors[level] : 'var(--border,#E0CFA8)';
        }
        var txt = document.getElementById('strengthTxt');
        if (txt) {
            txt.textContent = pw.length ? labels[level] + ' password' : 'Enter a password';
            txt.style.color = pw.length ? strengthColors[level] : 'var(--muted,#8C7B60)';
        }
    }

    // Confirm match check before submit
    var resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            var pw = document.getElementById('pwInput').value;
            var cpw = document.getElementById('pwConfirm').value;
            if (pw !== cpw) {
                e.preventDefault();
                var existing = document.getElementById('matchErr');
                if (!existing) {
                    var div = document.createElement('div');
                    div.id = 'matchErr';
                    div.className = 'flash-message flash-error';
                    div.style.cssText = 'border-radius:4px;padding:12px 16px;margin-bottom:16px;';
                    div.textContent = 'Passwords do not match.';
                    resetForm.parentNode.insertBefore(div, resetForm);
                }
            }
        });
    }

    // Loading state on submit buttons
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            var btn = form.querySelector('[type=submit]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Please wait...';
            }
        });
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>