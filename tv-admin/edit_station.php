<?php
require_once 'bootstrap.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    signal_error_page(400, 'Ungültige Sender-ID.', 'tv');
}

$stmt = $pdo->prepare('SELECT * FROM tv_stations WHERE id = :id');
$stmt->execute(array('id' => $id));
$station = $stmt->fetch();
if (!$station) {
    signal_error_page(404, 'TV-Sender nicht gefunden.', 'tv');
}

$errors = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    tv_verify_csrf();
    $station = tv_station_input();
    $errors = tv_validate_station($station);

    if (!$errors) {
        $station['id'] = $id;
        $stmt = $pdo->prepare('UPDATE tv_stations SET name=:name, stream_url=:stream_url, stream_type=:stream_type, web_url=:web_url, logo=:logo, sort_order=:sort_order, is_active=:is_active WHERE id=:id');
        $stmt->execute($station);
        $_SESSION['signal_notice'] = 'TV-Sender gespeichert.';
        header('Location: index.php');
        exit;
    }
}
$signal_tv_editing = true;
require __DIR__ . '/station_fields.php';
