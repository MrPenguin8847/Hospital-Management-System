<?php
/**
 * Helpers: sanitization, redirects, CSRF, invoice numbers.
 */
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * HTML escape for output
 */
function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Trim and strip tags for general text input
 */
function clean_string(?string $s, int $maxLen = 5000): string
{
    $s = trim((string) $s);
    $s = strip_tags($s);
    if (mb_strlen($s) > $maxLen) {
        $s = mb_substr($s, 0, $maxLen);
    }
    return $s;
}

/**
 * Redirect and exit
 */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Login page URL relative to current script (admin/patient subfolders use ../)
 */
function login_page_url(string $role): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (preg_match('#/(admin|patient)/#', (string) $script)) {
        return '../login.php?role=' . urlencode($role);
    }
    return 'login.php?role=' . urlencode($role);
}

/**
 * Session bootstrap (call once per request before output)
 */
function session_boot(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite'   => 'Lax',
            'use_strict_mode'   => true,
        ]);
    }
}

/**
 * CSRF token in session
 */
function csrf_token(): string
{
    session_boot();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/**
 * Validate CSRF from POST
 */
function csrf_verify(): bool
{
    session_boot();
    $t = $_POST['_csrf'] ?? '';
    return is_string($t) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $t);
}

/**
 * Flash message (one-time)
 */
function flash_set(string $key, string $message): void
{
    session_boot();
    $_SESSION['_flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    session_boot();
    if (!isset($_SESSION['_flash'][$key])) {
        return null;
    }
    $m = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $m;
}

/**
 * Generate unique invoice number
 */
function generate_invoice_number(PDO $pdo): string
{
    $prefix = 'INV-' . date('Ymd') . '-';
    for ($i = 0; $i < 50; $i++) {
        $suffix = str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $num = $prefix . $suffix;
        $st = $pdo->prepare('SELECT COUNT(*) FROM bills WHERE invoice_number = ?');
        $st->execute([$num]);
        if ((int) $st->fetchColumn() === 0) {
            return $num;
        }
    }
    return $prefix . bin2hex(random_bytes(2));
}

/**
 * Ensure upload directory exists
 */
function ensure_upload_dir(): void
{
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
}

/**
 * Asset URL prefix: '' from root pages, '../' from admin/patient
 */
function asset_prefix(): string
{
    return $GLOBALS['__asset_prefix'] ?? '';
}
