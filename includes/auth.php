<?php
/**
 * Session auth guards for admin and patient areas.
 */
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function require_admin(): void
{
    session_boot();
    if (empty($_SESSION['admin_id'])) {
        flash_set('error', 'Please log in as administrator.');
        redirect(login_page_url('admin'));
    }
}

function require_patient(): void
{
    session_boot();
    if (empty($_SESSION['patient_id'])) {
        flash_set('error', 'Please log in as a patient.');
        redirect(login_page_url('patient'));
    }
}

function admin_id(): int
{
    session_boot();
    return (int) ($_SESSION['admin_id'] ?? 0);
}

function patient_id(): int
{
    session_boot();
    return (int) ($_SESSION['patient_id'] ?? 0);
}

function logout_all(): void
{
    session_boot();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
