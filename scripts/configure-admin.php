<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$file = dirname(__DIR__) . '/lib/config.local.php';
$config = is_file($file) ? require $file : [];
echo "Admin-Benutzername: ";
$username = trim((string) fgets(STDIN));
echo "Neues Passwort (12 bis 72 Zeichen, Eingabe sichtbar): ";
$password = rtrim((string) fgets(STDIN), "\r\n");
if ($username === '' || strlen($password) < 12 || strlen($password) > 72) {
    fwrite(STDERR, "Ungueltiger Benutzername oder Passwortlaenge. Nichts gespeichert.\n"); exit(1);
}
$config['ADMIN_USERNAME'] = $username;
$config['ADMIN_PASSWORD_HASH'] = password_hash($password, PASSWORD_DEFAULT);
if (file_put_contents($file, "<?php\nreturn " . var_export($config, true) . ";\n", LOCK_EX) === false) { exit(1); }
echo "Admin-Zugang lokal eingerichtet; nur der Passwort-Hash wurde gespeichert.\n";
