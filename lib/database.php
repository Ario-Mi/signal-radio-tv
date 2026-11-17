<?php
require_once __DIR__ . '/config.php';
function signal_pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        ini_set('mysqlnd.net_read_timeout', '5');
        $dsn = 'mysql:host=' . signal_config('DB_HOST', 'localhost')
            . ';port=' . signal_config('DB_PORT', '3306')
            . ';dbname=' . signal_config('DB_NAME', 'myradio') . ';charset=utf8mb4';
        $pdo = new PDO($dsn, signal_config('DB_USER', 'root'), signal_config('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    }
    return $pdo;
}
