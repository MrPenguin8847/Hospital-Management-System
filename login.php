<?php
/**
 * Login — Admin or Patient (role query param)
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

session_boot();

$role = $_GET['role'] ?? 'patient';
if (!in_array($role, ['admin', 'patient'], true)) {
    $role = 'patient';
}

// Already logged in
if ($role === 'admin' && !empty($_SESSION['admin_id'])) {
    redirect('admin/index.php');
}
if ($role === 'patient' && !empty($_SESSION['patient_id'])) {
    redirect('patient/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid security token.';
    } else {
        $role = $_POST['role'] ?? 'patient';
        if (!in_array($role, ['admin', 'patient'], true)) {
            $role = 'patient';
        }
        $username = clean_string($_POST['username'] ?? '', 50);
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Enter username and password.';
        } else {
            $pdo = db();
            if ($role === 'admin') {
                $st = $pdo->prepare('SELECT id, username, full_name, password_hash FROM admins WHERE username = ? LIMIT 1');
                $st->execute([$username]);
                $row = $st->fetch();
                if ($row && password_verify($password, $row['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = (int) $row['id'];
                    $_SESSION['admin_name'] = $row['full_name'];
                    $_SESSION['admin_user'] = $row['username'];
                    redirect('admin/index.php');
                }
                $error = 'Invalid admin credentials.';
            } else {
                $st = $pdo->prepare('SELECT id, username, full_name, password_hash FROM patients WHERE username = ? LIMIT 1');
                $st->execute([$username]);
                $row = $st->fetch();
                if ($row && password_verify($password, $row['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['patient_id'] = (int) $row['id'];
                    $_SESSION['patient_name'] = $row['full_name'];
                    $_SESSION['patient_user'] = $row['username'];
                    redirect('patient/index.php');
                }
                $error = 'Invalid patient credentials.';
            }
        }
    }
}

$flash = flash_get('error');
if ($flash) {
    $error = $flash;
}

$pageTitle = 'Login';
$GLOBALS['__asset_prefix'] = '';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap container">
    <div class="auth-card glass">
        <h1><?= $role === 'admin' ? 'Admin Access' : 'Patient Login' ?></h1>
        <p class="muted">Session-secured gateway to your dashboard.</p>

        <?php if ($error !== ''): ?>
            <div class="alert alert--error"><?= h($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php?role=<?= h(urlencode($role)) ?>">
            <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="role" value="<?= h($role) ?>">

            <div class="form-group">
                <label for="username">Username</label>
                <input class="input" type="text" id="username" name="username" required autocomplete="username"
                       value="<?= h($_POST['username'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input class="input" type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn--primary btn--block">Sign In</button>
        </form>

        <div class="auth-links">
            <?php if ($role === 'admin'): ?>
                <a href="login.php?role=patient">Patient login instead</a>
            <?php else: ?>
                <a href="register.php">Create patient account</a> ·
                <a href="login.php?role=admin">Admin login</a>
            <?php endif; ?>
            <br><a href="index.php">← Back to home</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
