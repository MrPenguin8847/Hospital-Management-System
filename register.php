<?php
/**
 * Patient registration
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_boot();

if (!empty($_SESSION['patient_id'])) {
    redirect('patient/index.php');
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'Invalid security token.';
    } else {
        $username = clean_string($_POST['username'] ?? '', 50);
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $full_name = clean_string($_POST['full_name'] ?? '', 100);
        $phone = clean_string($_POST['phone'] ?? '', 20);
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirm'] ?? '';
        $dob = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $address = clean_string($_POST['address'] ?? '', 500);

        $allowedG = ['male', 'female', 'other', ''];
        if (!in_array($gender, $allowedG, true)) {
            $gender = '';
        }

        if ($username === '' || !$email || $full_name === '') {
            $error = 'Username, email, and full name are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $password2) {
            $error = 'Passwords do not match.';
        } else {
            $pdo = db();
            $st = $pdo->prepare('SELECT COUNT(*) FROM patients WHERE username = ? OR email = ?');
            $st->execute([$username, $email]);
            if ((int) $st->fetchColumn() > 0) {
                $error = 'Username or email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $dobVal = null;
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dob)) {
                    $dobVal = $dob;
                }
                $ins = $pdo->prepare(
                    'INSERT INTO patients (username, email, password_hash, full_name, phone, date_of_birth, gender, address)
                     VALUES (?,?,?,?,?,?,?,?)'
                );
                $ins->execute([
                    $username,
                    $email,
                    $hash,
                    $full_name,
                    $phone ?: null,
                    $dobVal,
                    $gender,
                    $address ?: null,
                ]);
                $success = true;
            }
        }
    }
}

$pageTitle = 'Register';
$GLOBALS['__asset_prefix'] = '';
require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap container">
    <div class="auth-card glass" style="max-width:520px;width:100%;">
        <h1>Patient Registration</h1>
        <p class="muted">Create your portal account — credentials are hashed at rest.</p>

        <?php if ($success): ?>
            <div class="alert alert--success">
                Account created. <a href="login.php?role=patient">Sign in here</a>.
            </div>
        <?php else: ?>
            <?php if ($error !== ''): ?>
                <div class="alert alert--error"><?= h($error) ?></div>
            <?php endif; ?>

            <form method="post" action="register.php">
                <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input class="input" name="username" id="username" required maxlength="50"
                           value="<?= h($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input class="input" type="email" name="email" id="email" required
                           value="<?= h($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="full_name">Full name</label>
                    <input class="input" name="full_name" id="full_name" required maxlength="100"
                           value="<?= h($_POST['full_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input class="input" name="phone" id="phone" maxlength="20"
                           value="<?= h($_POST['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="date_of_birth">Date of birth</label>
                    <input class="input" type="date" name="date_of_birth" id="date_of_birth"
                           value="<?= h($_POST['date_of_birth'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="gender">Gender</label>
                    <select class="input" name="gender" id="gender">
                        <?php $g = $_POST['gender'] ?? ''; ?>
                        <option value="" <?= $g === '' ? 'selected' : '' ?>>Prefer not to say</option>
                        <option value="male" <?= $g === 'male' ? 'selected' : '' ?>>Male</option>
                        <option value="female" <?= $g === 'female' ? 'selected' : '' ?>>Female</option>
                        <option value="other" <?= $g === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea class="input" name="address" id="address"><?= h($_POST['address'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="input" type="password" name="password" id="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirm password</label>
                    <input class="input" type="password" name="password_confirm" id="password_confirm" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn--primary btn--block">Create account</button>
            </form>
        <?php endif; ?>

        <div class="auth-links">
            <a href="login.php?role=patient">Already have an account?</a> ·
            <a href="index.php">Home</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
