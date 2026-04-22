<?php
/**
 * Patient profile management
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_patient();
$pdo = db();
$pid = patient_id();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
        $full_name = clean_string($_POST['full_name'] ?? '', 100);
        $phone = clean_string($_POST['phone'] ?? '', 20);
        $dob = $_POST['date_of_birth'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $address = clean_string($_POST['address'] ?? '', 500);
        $newPass = $_POST['new_password'] ?? '';
        $newPass2 = $_POST['new_password_confirm'] ?? '';

        $allowedG = ['male', 'female', 'other', ''];
        if (!in_array($gender, $allowedG, true)) {
            $gender = '';
        }

        if (!$email || $full_name === '') {
            $err = 'Valid email and full name required.';
        } elseif ($newPass !== '' && ($newPass !== $newPass2 || strlen($newPass) < 8)) {
            $err = 'Passwords must match and be at least 8 characters.';
        } else {
            $dobVal = null;
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $dob)) {
                $dobVal = $dob;
            }
            if ($newPass !== '') {
                $hash = password_hash($newPass, PASSWORD_DEFAULT);
                $st = $pdo->prepare(
                    'UPDATE patients SET email=?, full_name=?, phone=?, date_of_birth=?, gender=?, address=?, password_hash=? WHERE id=?'
                );
                $st->execute([$email, $full_name, $phone ?: null, $dobVal, $gender, $address ?: null, $hash, $pid]);
            } else {
                $st = $pdo->prepare(
                    'UPDATE patients SET email=?, full_name=?, phone=?, date_of_birth=?, gender=?, address=? WHERE id=?'
                );
                $st->execute([$email, $full_name, $phone ?: null, $dobVal, $gender, $address ?: null, $pid]);
            }
            $_SESSION['patient_name'] = $full_name;
            $msg = 'Profile updated.';
        }
    }
}

$st = $pdo->prepare('SELECT * FROM patients WHERE id=?');
$st->execute([$pid]);
$p = $st->fetch() ?: [];

$pageTitle = 'My Profile';
$currentPage = 'profile';
require_once __DIR__ . '/../includes/patient_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<form method="post" class="glass" style="max-width:520px;padding:24px;">
    <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
    <div class="form-group">
        <label>Username (read-only)</label>
        <input class="input" type="text" value="<?= h($p['username'] ?? '') ?>" disabled>
    </div>
    <div class="form-group">
        <label for="full_name">Full name</label>
        <input class="input" name="full_name" id="full_name" required value="<?= h($p['full_name'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="email">Email</label>
        <input class="input" type="email" name="email" id="email" required value="<?= h($p['email'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="phone">Phone</label>
        <input class="input" name="phone" id="phone" value="<?= h($p['phone'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="date_of_birth">Date of birth</label>
        <input class="input" type="date" name="date_of_birth" id="date_of_birth" value="<?= h($p['date_of_birth'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="gender">Gender</label>
        <?php $g = $p['gender'] ?? ''; ?>
        <select class="input" name="gender" id="gender">
            <option value="" <?= $g === '' ? 'selected' : '' ?>>Prefer not to say</option>
            <option value="male" <?= $g === 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?= $g === 'female' ? 'selected' : '' ?>>Female</option>
            <option value="other" <?= $g === 'other' ? 'selected' : '' ?>>Other</option>
        </select>
    </div>
    <div class="form-group">
        <label for="address">Address</label>
        <textarea class="input" name="address" id="address"><?= h($p['address'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
        <label for="new_password">New password (optional)</label>
        <input class="input" type="password" name="new_password" id="new_password" minlength="8" autocomplete="new-password">
    </div>
    <div class="form-group">
        <label for="new_password_confirm">Confirm new password</label>
        <input class="input" type="password" name="new_password_confirm" id="new_password_confirm" minlength="8">
    </div>
    <button type="submit" class="btn btn--primary">Save</button>
</form>

<?php require_once __DIR__ . '/../includes/patient_footer.php'; ?>
