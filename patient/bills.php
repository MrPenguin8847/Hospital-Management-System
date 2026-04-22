<?php
/**
 * Patient bills list + invoice link
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_patient();
$pdo = db();
$pid = patient_id();

$st = $pdo->prepare(
    'SELECT * FROM bills WHERE patient_id=? ORDER BY id DESC'
);
$st->execute([$pid]);
$rows = $st->fetchAll();

$pageTitle = 'Bills';
$currentPage = 'bills';
require_once __DIR__ . '/../includes/patient_header.php';
?>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $b): ?>
                <tr>
                    <td><?= h($b['invoice_number']) ?></td>
                    <td>$<?= h(number_format((float) $b['amount'], 2)) ?></td>
                    <td>
                        <?php if ($b['status'] === 'paid'): ?>
                            <span class="badge badge--paid">paid</span>
                        <?php else: ?>
                            <span class="badge badge--pending">pending</span>
                        <?php endif; ?>
                    </td>
                    <td><?= h($b['created_at']) ?></td>
                    <td>
                        <a class="btn btn--ghost btn--sm" href="invoice.php?id=<?= (int) $b['id'] ?>">View invoice</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (count($rows) === 0): ?>
    <p class="text-muted">No bills yet.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/patient_footer.php'; ?>
