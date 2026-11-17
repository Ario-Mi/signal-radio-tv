<?php
require_once dirname(__DIR__) . '/lib/admin.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); header('Allow: POST'); exit('Methode nicht erlaubt.'); }
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { signal_error_page(400, 'Ungueltige Sender-ID.', 'radio'); }
$stmt = $pdo->prepare('DELETE FROM stations WHERE id = :id');
$stmt->execute(['id' => $id]);
$_SESSION['signal_notice'] = 'Radiosender geloescht.';
        header('Location: index.php'); exit;
