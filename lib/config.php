<?php
// Environment variables take priority over the untracked local configuration.
function signal_config(string $key, string $default = ''): string
{
    static $local = null;
    if ($local === null) {
        $file = __DIR__ . '/config.local.php';
        $local = is_file($file) ? require $file : [];
        if (!is_array($local)) { throw new RuntimeException('Invalid local configuration'); }
    }
    $value = getenv($key);
    return $value !== false ? $value : (string) ($local[$key] ?? $default);
}
ini_set('display_errors', '0');
ini_set('log_errors', '1');
