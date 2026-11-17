<?php

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once dirname(__DIR__) . '/lib/database.php';

try {
    $pdo = signal_pdo();
    $stmt = $pdo->query('SELECT id, name FROM genres ORDER BY name');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(array('error' => 'Genres konnten nicht geladen werden.'), JSON_UNESCAPED_UNICODE);
}
