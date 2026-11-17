<?php
require_once 'bootstrap.php';

$station = array('name' => '', 'stream_url' => '', 'stream_type' => 'hls', 'web_url' => '', 'logo' => signal_base_path() . '/tv-player/assets/images/signal-tv.svg', 'sort_order' => 0, 'is_active' => 1);
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    tv_verify_csrf();
    $station = tv_station_input();
    $errors = tv_validate_station($station);

    if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO tv_stations (name, stream_url, stream_type, web_url, logo, sort_order, is_active) VALUES (:name, :stream_url, :stream_type, :web_url, :logo, :sort_order, :is_active)');
        $stmt->execute($station);
        $_SESSION['signal_notice'] = 'TV-Sender hinzugefuegt.';
        header('Location: index.php');
        exit;
    }
}
$signal_tv_editing = false;
require __DIR__ . '/station_fields.php';
