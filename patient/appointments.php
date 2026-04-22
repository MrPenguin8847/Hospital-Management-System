<?php
/**
 * Book appointments + view history
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/departments.php';

require_patient();
$pdo = db();
$pid = patient_id();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book'])) {
    if (!csrf_verify()) {
        $err = 'Invalid security token.';
    } else {
        $doctor_name = clean_string($_POST['doctor_name'] ?? '', 100);
        $department = clean_string($_POST['department'] ?? '', 100);
        $appointment_date = $_POST['appointment_date'] ?? '';
        $appointment_time = $_POST['appointment_time'] ?? '';
        $notes = clean_string($_POST['notes'] ?? '', 2000);

        $depts = departments_list();
        if (!in_array($department, $depts, true)) {
            $err = 'Invalid department.';
        } elseif ($doctor_name === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date)) {
            $err = 'Doctor and valid date required.';
        } elseif (!preg_match('/^\d{2}:\d{2}$/', $appointment_time)) {
            $err = 'Valid time required (HH:MM).';
        } else {
            $timeSql = $appointment_time . ':00';
            $st = $pdo->prepare(
                'INSERT INTO appointments (patient_id, doctor_name, department, appointment_date, appointment_time, status, notes)
                 VALUES (?,?,?,?,?,\'pending\',?)'
            );
            $st->execute([$pid, $doctor_name, $department, $appointment_date, $timeSql, $notes ?: null]);
            $msg = 'Appointment request submitted — pending approval.';
        }
    }
}

$history = $pdo->prepare(
    'SELECT * FROM appointments WHERE patient_id=? ORDER BY appointment_date DESC, appointment_time DESC'
);
$history->execute([$pid]);
$rows = $history->fetchAll();

$depts = departments_list();

$pageTitle = 'Appointments';
$currentPage = 'appointments';
require_once __DIR__ . '/../includes/patient_header.php';
?>

<?php if ($msg): ?><div class="alert alert--success"><?= h($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert--error"><?= h($err) ?></div><?php endif; ?>

<div class="glass" style="padding:24px;margin-bottom:24px;">
    <h2 class="section-title mt-0" style="font-size:1.1rem;">Book appointment</h2>
    <form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;align-items:end;">
        <input type="hidden" name="_csrf" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="book" value="1">
        <div class="form-group" style="margin:0;">
            <label for="department">Department</label>
            <select class="input" name="department" id="department" required>
                <?php foreach ($depts as $d): ?>
                    <option value="<?= h($d) ?>"><?= h($d) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="margin:0;">
            <label for="doctor_name">Doctor name</label>
            <input class="input" name="doctor_name" id="doctor_name" required maxlength="100" placeholder="e.g. Dr. Chen">
        </div>
        <div class="form-group" style="margin:0;">
            <label for="appointment_date">Date</label>
            <input class="input" type="date" name="appointment_date" id="appointment_date" required>
        </div>
        <div class="form-group" style="margin:0;">
            <label for="appointment_time">Time</label>
            <input class="input" type="time" name="appointment_time" id="appointment_time" required>
        </div>
        <div class="form-group" style="margin:0;grid-column:1/-1;">
            <label for="notes">Notes (optional)</label>
            <input class="input" name="notes" id="notes" placeholder="Symptoms or requests">
        </div>
        <button type="submit" class="btn btn--primary">Submit request</button>
    </form>
</div>

<h2 class="section-title" style="font-size:1.1rem;">History</h2>
<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Department</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= h($r['doctor_name']) ?></td>
                    <td><?= h($r['department']) ?></td>
                    <td><?= h($r['appointment_date']) ?></td>
                    <td><?= h(substr($r['appointment_time'], 0, 5)) ?></td>
                    <td>
                        <?php
                        $s = $r['status'];
                        $cls = $s === 'approved' ? 'badge--approved' : ($s === 'rejected' ? 'badge--rejected' : 'badge--pending');
                        ?>
                        <span class="badge <?= $cls ?>"><?= h($s) ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (count($rows) === 0): ?>
    <p class="text-muted">No appointments yet.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/patient_footer.php'; ?>
