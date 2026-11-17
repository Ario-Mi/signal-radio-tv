<?php

require_once dirname(__DIR__) . '/lib/admin.php';

function tv_e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function tv_csrf_token() { return signal_csrf_token(); }
function tv_verify_csrf() { signal_verify_csrf(); }

function tv_station_input()
{
    return array(
        'name' => signal_text($_POST, 'name'),
        'stream_url' => signal_text($_POST, 'stream_url'),
        'stream_type' => in_array($_POST['stream_type'] ?? '', array('hls', 'mp4'), true) ? $_POST['stream_type'] : 'hls',
        'web_url' => signal_text($_POST, 'web_url'),
        'logo' => signal_text($_POST, 'logo'),
        'sort_order' => max(0, (int) ($_POST['sort_order'] ?? 0)),
        'is_active' => isset($_POST['is_active']) ? 1 : 0
    );
}

function tv_validate_station($station)
{
    $errors = array();
    if ($station['name'] === '' || mb_strlen($station['name']) > 100) {
        $errors[] = 'Bitte einen Sendernamen eingeben.';
    }
    if (!signal_url($station['stream_url'])) {
        $errors[] = 'Bitte eine gültige Stream-URL eingeben.';
    }
    if ($station['web_url'] !== '' && !signal_url($station['web_url'])) {
        $errors[] = 'Bitte eine gültige Sender-Webseite eingeben.';
    }
    if ($station['logo'] !== '' && !signal_url($station['logo'], true)) {
        $errors[] = 'Bitte eine gültige Logo-URL oder einen Pfad ab /radio_projekt eingeben.';
    }

    foreach (['stream_url' => 500, 'web_url' => 255, 'logo' => 500] as $field => $limit) {
        if (mb_strlen($station[$field]) > $limit) { $errors[] = 'Eingabe zu lang: ' . $field; }
    }
    return $errors;
}
