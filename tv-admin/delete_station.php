<?php
require_once 'bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Methode nicht erlaubt.');
}

tv_verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id) {
    $stmt = $pdo->prepare('DELETE FROM tv_stations WHERE id = :id');
    $stmt->execute(array('id' => $id));
}

$_SESSION['signal_notice'] = 'TV-Sender geloescht.';
        header('Location: index.php');
exit;
