<?php
/**
 * Submit and view complaints
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
        $subject = clean_string($_POST['subject'] ?? '', 200);
        $message = clean_string($_POST['message'] ?? '', 5000);
        if ($subject === '' || $message === '') {
            $err = 'Subject and message required.';
        } else {
            $st = $pdo->prepare(
                'INSERT INTO complaints (patient_id, subject, message, status) VALUES (?,?,?,\'open\')'
            );
            $st->execute([$pid, $subject, $message]);
            $msg = 'Complaint submitted.';
        }
    }
}

$list = $pdo->prepare('SELECT * FROM complaints WHERE patient_id=? ORDER BY id DESC');
$list->execute([$pid]);
$rows = $list->fetchAll();

$pageTitle = 'Complaints';
$currentPage = 'complaints';
require_once __DIR__ . '/../includes/patient_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<div class="glass" style="padding:24px;margin-bottom:24px;">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">New complaint</h2>
    <form method="post" style="max-width:560px;">
        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
        <div class="form-group">
            <label for="subject">Subject</label>
            <input class="input" name="subject" id="subject" required maxlength="200">
        </div>
        <div class="form-group">
            <label for="message">Message</label>
            <textarea class="input" name="message" id="message" required></textarea>
        </div>
        <button type="submit" class="btn btn--primary">Submit</button>
    </form>
</div>

<h2 class="section-title" style="font-size:1.1rem;">Your requests</h2>
<?php foreach ($rows as $c): ?>
    <div class="glass" style="padding:16px;margin-bottom:12px;">
        <div class="flex-between">
            <strong><?= h($c['subject']) ?></strong>
            <?php if ($c['status'] === 'open'): ?>
                <span class="badge badge--open">open</span>
            <?php else: ?>
                <span class="badge badge--resolved">resolved</span>
            <?php endif; ?>
        </div>
        <p class="text-muted" style="font-size:0.85rem;"><?= h($c['created_at']) ?></p>
        <p><?= nl2br(h($c['message'])) ?></p>
        <?php if ($c['status'] === 'resolved' && $c['admin_response']): ?>
            <div style="border-left:3px solid var(--accent-2);padding-left:12px;margin-top:12px;">
                <strong>Hospital response:</strong>
                <p style="margin:8px 0 0;"><?= nl2br(h((string) $c['admin_response'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (count($rows) === 0): ?>
    <p class="text-muted">No complaints submitted yet.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/patient_footer.php'; ?>
