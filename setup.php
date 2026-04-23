<?php
/**
 * One-time setup: creates default admin if none exist.
 * Default: username `admin`, password `Admin@123`
 * DELETE or protect this file in production after first run.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

session_boot();

header('Content-Type: text/html; charset=UTF-8');
$pdo = db();

$count = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($count > 0) {
    echo '<p>Setup already completed — at least one admin exists.</p>';
    echo '<p><a href="login.php?role=admin">Go to admin login</a></p>';
    exit;
}

$hash = password_hash('Admin@123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO admins (username, email, password_hash, full_name) VALUES (?,?,?,?)');
$stmt->execute(['admin', 'admin@hospital.local', $hash, 'System Administrator']);

echo '<p><strong>Setup complete.</strong> Default admin created.</p>';
echo '<ul><li>Username: <code>admin</code></li><li>Password: <code>Admin@123</code></li></ul>';
echo '<p><a href="login.php?role=admin">Admin login</a> — remove or restrict <code>setup.php</code> after use.</p>';