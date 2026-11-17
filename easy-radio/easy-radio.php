<?php
/*
Plugin Name: EasyRadio – SIGNAL Studio
Description: Responsiver Radio-Player mit Sender- und Genre-Auswahl.
Version: 2.0.0
Author: Ario
*/

if (!defined('ABSPATH')) {
    exit;
}

function signal_radio_origin_url()
{
    $parts = wp_parse_url(home_url('/'));
    $scheme = $parts['scheme'] ?? (is_ssl() ? 'https' : 'http');
    $host = $parts['host'] ?? ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $port = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

    return $scheme . '://' . $host . $port;
}

function signal_radio_enqueue_assets()
{
    global $post;

    if (!($post instanceof WP_Post) || !has_shortcode($post->post_content, 'easy_radio')) {
        return;
    }

    $css_file = __DIR__ . '/assets/css/radioplayer.css';
    $js_file = __DIR__ . '/assets/js/radioplayer.js';

    wp_enqueue_style(
        'signal-radio-player',
        plugin_dir_url(__FILE__) . 'assets/css/radioplayer.css',
        array(),
        file_exists($css_file) ? (string) filemtime($css_file) : '2.0.0'
    );

    wp_enqueue_script(
        'signal-radio-player',
        plugin_dir_url(__FILE__) . 'assets/js/radioplayer.js',
        array(),
        file_exists($js_file) ? (string) filemtime($js_file) : '2.0.0',
        true
    );

    wp_localize_script('signal-radio-player', 'signalRadioSettings', array(
        'apiBaseUrl' => apply_filters('signal_radio_api_base_url', signal_radio_origin_url() . '/radio_projekt/'),
        'brandLogo' => plugin_dir_url(__FILE__) . 'assets/images/signal-radio.svg',
        'defaultLogo' => plugin_dir_url(__FILE__) . 'assets/images/signal-radio.svg',
        'errorMessage' => 'Die Sender konnten nicht geladen werden. Bitte versuche es erneut.'
    ));
}
add_action('wp_enqueue_scripts', 'signal_radio_enqueue_assets');

function signal_radio_shortcode()
{
    static $instance = 0;
    $instance++;
    $title_id = 'signal-radio-title-' . $instance;

    ob_start();
    ?>
    <section class="signal-radio-player" data-radio-player aria-labelledby="<?php echo esc_attr($title_id); ?>">
        <div class="rp-brand">
            <img class="rp-logo" src="" width="96" height="96" alt="SIGNAL Studio Radio-Logo">
            <div>
                <span class="rp-kicker">SIGNAL Studio</span>
                <h2 id="<?php echo esc_attr($title_id); ?>">Live Radio</h2>
            </div>
        </div>

        <p class="rp-intro">Wähle ein Genre und starte deinen Lieblingssender.</p>

        <div class="rp-section">
            <p class="rp-label">Genre auswählen</p>
            <div class="rp-genre" role="group" aria-label="Radio-Genres"></div>
        </div>

        <div class="rp-now-playing" aria-live="polite">
            <img class="rp-current-logo" src="" width="132" height="132" alt="SIGNAL Radio">
            <strong class="rp-current-station">Kein Sender ausgewählt</strong>
        </div>

        <audio class="rp-audio-player" controls preload="none" aria-label="Radio-Player"></audio>

        <div class="rp-section">
            <p class="rp-label">Sender auswählen</p>
            <div class="rp-station" role="group" aria-label="Radiosender"></div>
        </div>

        <p class="rp-status" role="status" aria-live="polite"></p>
    </section>
    <?php

    return ob_get_clean();
}
add_shortcode('easy_radio', 'signal_radio_shortcode');
