<?php
/**
 * Patient dashboard overview
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_patient();
$pdo = db();
$pid = patient_id();

$st = $pdo->prepare('SELECT full_name FROM patients WHERE id=?');
$st->execute([$pid]);
$name = $st->fetchColumn();

$upcoming = $pdo->prepare(
    "SELECT COUNT(*) FROM appointments WHERE patient_id=? AND status='approved' AND appointment_date >= CURDATE()"
);
$upcoming->execute([$pid]);
$uc = (int) $upcoming->fetchColumn();

$pending = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id=? AND status='pending'");
$pending->execute([$pid]);
$pc = (int) $pending->fetchColumn();

$reports = $pdo->prepare('SELECT COUNT(*) FROM reports WHERE patient_id=?');
$reports->execute([$pid]);
$rc = (int) $reports->fetchColumn();

$openC = $pdo->prepare("SELECT COUNT(*) FROM complaints WHERE patient_id=? AND status='open'");
$openC->execute([$pid]);
$cc = (int) $openC->fetchColumn();

$pageTitle = 'Overview';
$currentPage = 'dash';
require_once __DIR__ . '/../includes/patient_header.php';
?>

<p class="text-muted mb-2">Welcome back, <strong><?= h((string) $name) ?></strong>.</p>

<div class="stats-grid">
    <div class="stat-card glass">
        <div class="label">Approved upcoming</div>
        <div class="value"><?= $uc ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Pending requests</div>
        <div class="value"><?= $pc ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Reports</div>
        <div class="value"><?= $rc ?></div>
    </div>
    <div class="stat-card glass">
        <div class="label">Open complaints</div>
        <div class="value"><?= $cc ?></div>
    </div>
</div>

<div class="card-grid" style="margin-top:24px;">
    <a class="feature-card glass" href="appointments.php" style="text-decoration:none;color:inherit;">
        <h3>Book appointment</h3>
        <p class="text-muted">Choose department, doctor, and slot.</p>
    </a>
    <a class="feature-card glass" href="reports.php" style="text-decoration:none;color:inherit;">
        <h3>Medical reports</h3>
        <p class="text-muted">View or download PDFs shared by your care team.</p>
    </a>
    <a class="feature-card glass" href="bills.php" style="text-decoration:none;color:inherit;">
        <h3>Bills &amp; invoices</h3>
        <p class="text-muted">Review charges and payment status.</p>
    </a>
</div>

<?php require_once __DIR__ . '/../includes/patient_footer.php'; ?>
