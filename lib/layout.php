<?php
require_once __DIR__ . '/security.php';
function signal_page_start(string $title, string $description = '', string $section = 'radio', bool $admin = true): void
{
    $base = signal_e(signal_base_path());
    ?><!doctype html>
<html lang="de"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= signal_e($title) ?> | SIGNAL Studio</title>
<link rel="stylesheet" href="<?= $base ?>/assets/css/studio.css?v=3">
<script src="<?= $base ?>/assets/js/studio.js?v=3" defer></script></head>
<body class="signal-site"><a class="skip-link" href="#inhalt">Zum Inhalt</a><div class="studio-shell">
<header class="studio-header"><a class="studio-brand" href="<?= $base ?>/index.html"><img src="<?= $base ?>/easy-radio/assets/images/signal-radio.svg" alt="" width="42" height="42"><span>SIGNAL STUDIO<small>Radio &amp; TV</small></span></a>
<nav class="studio-nav" aria-label="Hauptnavigation"><a href="<?= $base ?>/easy-radio/player.html">Radio</a><a href="<?= $base ?>/tv-player/player.html">Live TV</a><a href="<?= $base ?>/admin/login.php" aria-current="page">Verwaltung</a></nav></header>
<?php if ($admin): signal_admin_navigation($section); endif; ?>
<main id="inhalt" <?= $section === 'login' ? 'class="login-shell"' : '' ?>>
<div class="page-heading"><div><span class="kicker"><?= $admin ? 'Studio-Verwaltung' : 'Willkommen im Studio' ?></span><h1><?= signal_e($title) ?></h1><?php if ($description !== ''): ?><p><?= signal_e($description) ?></p><?php endif; ?></div></div>
<?php
    if ($admin && isset($_SESSION['signal_notice'])) {
        echo '<div class="notice" role="status">' . signal_e($_SESSION['signal_notice']) . '</div>';
        unset($_SESSION['signal_notice']);
    }
}
function signal_page_end(): void
{
    $base = signal_e(signal_base_path());
    ?></main><footer class="studio-footer"><span>SIGNAL Studio &middot; Radio &amp; Live TV</span><nav aria-label="Weitere Links"><a href="<?= $base ?>/easy-radio/player.html">Radio h&ouml;ren</a><a href="<?= $base ?>/tv-player/player.html">TV ansehen</a></nav></footer></div></body></html><?php
}
function signal_errors(array $errors): void
{
    if (!$errors) return;
    echo '<div class="errors" role="alert"><ul>';
    foreach ($errors as $error) echo '<li>' . signal_e($error) . '</li>';
    echo '</ul></div>';
}

function signal_error_page(int $status, string $message, string $section = 'radio'): void
{
    http_response_code($status);
    signal_page_start('Anfrage nicht moeglich', 'Bitte pruefe die Anfrage und versuche es erneut.', $section, signal_is_admin());
    signal_errors([$message]);
    echo '<a class="button" href="' . signal_e(signal_base_path()) . '/radio-admin/index.php">Zur Verwaltung</a>';
    signal_page_end();
    exit;
}
