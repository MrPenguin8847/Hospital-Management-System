<?php
/**
 * Patient medical reports — download via secure script
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_patient();
$pdo = db();
$pid = patient_id();

$st = $pdo->prepare(
    'SELECT id, title, original_filename, created_at FROM reports WHERE patient_id=? ORDER BY id DESC'
);
$st->execute([$pid]);
$rows = $st->fetchAll();

$pageTitle = 'Medical Reports';
$currentPage = 'reports';
require_once __DIR__ . '/../includes/patient_header.php';
?>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>File</th>
                <th>Uploaded</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= h($r['title']) ?></td>
                    <td><?= h($r['original_filename']) ?></td>
                    <td><?= h($r['created_at']) ?></td>
                    <td>
                        <a class="btn btn--primary btn--sm" href="download_report.php?id=<?= (int) $r['id'] ?>">Download</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (count($rows) === 0): ?>
    <p class="text-muted">No reports uploaded yet.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/patient_footer.php'; ?>
