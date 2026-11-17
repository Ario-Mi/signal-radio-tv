<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/lib/database.php';

try {
    $pdo = signal_pdo();
    $stmt = $pdo->query(
        'SELECT id, name, stream_url, stream_type, web_url, logo
         FROM tv_stations
         WHERE is_active = 1
         ORDER BY sort_order, name'
    );
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(array('error' => 'TV-Sender konnten nicht geladen werden.'), JSON_UNESCAPED_UNICODE);
}
