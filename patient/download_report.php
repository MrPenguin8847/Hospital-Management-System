<?php
/**
 * Secure PDF download — only owner patient may access
 */
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_patient();
$pdo = db();
$pid = patient_id();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    exit('Bad request');
}

$st = $pdo->prepare('SELECT file_path, original_filename FROM reports WHERE id=? AND patient_id=?');
$st->execute([$id, $pid]);
$row = $st->fetch();
if (!$row) {
    http_response_code(404);
    exit('Not found');
}

$rel = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $row['file_path']);
$full = ROOT_PATH . DIRECTORY_SEPARATOR . $rel;

if (!is_file($full)) {
    http_response_code(404);
    exit('File missing');
}

$filename = $row['original_filename'] ?: 'report.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
header('Content-Length: ' . (string) filesize($full));
header('X-Content-Type-Options: nosniff');
readfile($full);
exit;
