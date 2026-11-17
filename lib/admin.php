<?php
require_once __DIR__ . '/security.php';
signal_start_session();
header('Cache-Control: no-store');
if (!signal_is_admin()) {
    // Authentication is checked before any database access or write operation.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(401);
        exit('Bitte zuerst als Administrator anmelden.');
    }
    header('Location: ' . signal_base_path() . '/admin/login.php');
    exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') { signal_verify_csrf(); }
set_exception_handler(function (Throwable $error): void {
    error_log('[SIGNAL admin] ' . $error->getMessage());
    http_response_code(500);
    require_once __DIR__ . '/layout.php';
    signal_error_page(500, 'Die Anfrage konnte nicht verarbeitet werden. Bitte versuche es spaeter erneut.');
});
require_once __DIR__ . '/database.php';
$pdo = signal_pdo();

require_once __DIR__ . '/layout.php';
