<?php
/**
 * Destroy session and return home
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

session_boot();
logout_all();

header('Location: index.php');
exit;
