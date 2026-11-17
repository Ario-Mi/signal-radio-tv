<?php
require_once __DIR__ . '/config.php';
function signal_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function signal_text(array $source, string $key, string $default = ''): string
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : $default;
}
function signal_url(string $value, bool $local = false): bool
{
    if ($local && preg_match('~^/(?!/)[^\\\\\x00-\x20]*$~D', $value)) { return true; }
    return filter_var($value, FILTER_VALIDATE_URL) !== false
        && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
}
function signal_base_path(): string
{
    return rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/admin/login.php'))), '/');
}
function signal_start_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        session_name('signal_admin');
        session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'path' => signal_base_path() . '/']);
        session_start();
    }
}
function signal_csrf_token(): string
{
    if (!isset($_SESSION['signal_csrf'])) { $_SESSION['signal_csrf'] = bin2hex(random_bytes(32)); }
    return $_SESSION['signal_csrf'];
}
function signal_csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . signal_e(signal_csrf_token()) . '">';
}
function signal_verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !hash_equals(signal_csrf_token(), $token)) {
        http_response_code(403);
        exit('Ungueltige oder abgelaufene Formularanfrage. Bitte die Seite neu laden.');
    }
}
function signal_is_admin(): bool
{
    $hash = signal_config('ADMIN_PASSWORD_HASH');
    $version = hash('sha256', signal_config('ADMIN_USERNAME', 'admin') . ':' . $hash);
    return $hash !== '' && is_string($_SESSION['signal_admin_version'] ?? null)
        && hash_equals($version, $_SESSION['signal_admin_version']);
}
function signal_admin_navigation(string $section = 'radio'): void
{
    $base = signal_e(signal_base_path());
    $links = ['radio' => ['/radio-admin/index.php', 'Radiosender'], 'genres' => ['/radio-admin/create_genre.php', 'Genres'], 'tv' => ['/tv-admin/index.php', 'TV-Sender']];
    echo '<nav class="admin-nav" aria-label="Verwaltung">';
    foreach ($links as $key => $link) {
        echo '<a href="' . $base . $link[0] . '"' . ($section === $key ? ' aria-current="page"' : '') . '>' . $link[1] . '</a>';
    }
    echo '<form method="post" action="' . $base . '/admin/logout.php">' . signal_csrf_field() . '<button type="submit">Abmelden</button></form></nav>';
}
