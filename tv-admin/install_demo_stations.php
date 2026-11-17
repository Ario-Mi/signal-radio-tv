<?php
require_once 'bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Methode nicht erlaubt.');
}

tv_verify_csrf();

$stations = array(
    array('Das Erste', 'https://mcdn.daserste.de/daserste/de/master.m3u8', 'https://www.daserste.de/live/index.html', 1),
    array('ZDF', 'https://zdf-hls-15.akamaized.net/hls/live/2016498/de/high/master.m3u8', 'https://www.zdf.de/live-tv', 2),
    array('tagesschau24', 'https://tagesschau.akamaized.net/hls/live/2020117/tagesschau/tagesschau_1/master.m3u8', 'https://www.tagesschau.de/multimedia/livestreams/', 3),
    array('ARTE', 'https://artesimulcast.akamaized.net/hls/live/2030993/artelive_de/master_v720.m3u8', 'https://www.arte.tv/de/live/', 4),
    array('phoenix', 'https://zdf-hls-19.akamaized.net/hls/live/2016502/de/high/master.m3u8', 'https://www.phoenix.de/livestream.html', 5),
    array('DW Deutsch', 'https://dwamdstream104.akamaized.net/hls/live/2015531/dwstream104/index.m3u8', 'https://www.dw.com/de/media-center/live-tv/s-100817', 6)
);

$stmt = $pdo->prepare(
    "INSERT INTO tv_stations
        (name, stream_url, stream_type, web_url, logo, sort_order, is_active)
     VALUES
        (:name, :stream_url, 'hls', :web_url, '/radio_projekt/tv-player/assets/images/signal-tv.svg', :sort_order, 1)
     ON DUPLICATE KEY UPDATE
        stream_url = VALUES(stream_url),
        stream_type = VALUES(stream_type),
        web_url = VALUES(web_url),
        logo = VALUES(logo),
        sort_order = VALUES(sort_order),
        is_active = 1"
);

foreach ($stations as $station) {
    $stmt->execute(array(
        'name' => $station[0],
        'stream_url' => $station[1],
        'web_url' => $station[2],
        'sort_order' => $station[3]
    ));
}

$_SESSION['signal_notice'] = 'Standardsender ergaenzt und aktualisiert.';
        header('Location: index.php');
exit;
