<?php
require_once dirname(__DIR__) . '/lib/security.php';
signal_start_session();
header('Cache-Control: no-store');
if (signal_is_admin()) { header('Location: ../radio-admin/index.php'); exit; }
$hash = signal_config('ADMIN_PASSWORD_HASH');
$error = $hash === '' ? 'Der Admin-Zugang ist noch nicht eingerichtet. Bitte scripts/configure-admin.php lokal ausfuehren.' : '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    signal_verify_csrf();
    $username = signal_text($_POST, 'username');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    if ($hash !== '' && $username === signal_config('ADMIN_USERNAME', 'admin') && password_verify($password, $hash)) {
        session_regenerate_id(true);
        $_SESSION['signal_admin_version'] = hash('sha256', $username . ':' . $hash);
        header('Location: ../radio-admin/index.php');
        exit;
    }
    $error = 'Anmeldung fehlgeschlagen. Bitte Zugangsdaten pruefen.';
}
?>
<?php require_once dirname(__DIR__) . '/lib/layout.php'; signal_page_start('Admin-Anmeldung', 'Melde dich an, um Radio- und TV-Sender zu verwalten.', 'login', false); ?>
<?php if ($error !== ''): ?><div class="<?= $hash === '' ? 'notice' : 'errors' ?>" role="<?= $hash === '' ? 'status' : 'alert' ?>"><?= signal_e($error) ?></div><?php endif; ?>
<form class="card" method="post"><?= signal_csrf_field() ?>
<label for="admin-username">Benutzername<input id="admin-username" name="username" autocomplete="username" required></label>
<label for="admin-password">Passwort<input id="admin-password" type="password" name="password" autocomplete="current-password" required></label>
<div class="form-actions"><button class="primary" type="submit" <?= $hash === '' ? 'disabled' : '' ?>>Sicher anmelden</button></div></form>
<?php signal_page_end(); ?>
