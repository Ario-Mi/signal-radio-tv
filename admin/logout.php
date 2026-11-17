<?php
require_once dirname(__DIR__) . '/lib/security.php';
signal_start_session();
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405); header('Allow: POST'); exit('Methode nicht erlaubt.');
}
signal_verify_csrf();
$_SESSION = [];
session_regenerate_id(true);
header('Location: login.php');
exit;
