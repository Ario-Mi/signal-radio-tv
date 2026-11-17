"""Build a self-contained, read-only GitHub Pages demo. No PHP or local config is copied."""
from pathlib import Path
import json
import shutil

ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / 'demo'
FILES = [
    'index.html', 'assets/css/studio.css', 'assets/js/studio.js',
    'easy-radio/player.html', 'easy-radio/assets/css/radioplayer.css',
    'easy-radio/assets/js/radioplayer.js', 'easy-radio/assets/images/signal-radio.svg',
    'tv-player/player.html', 'tv-player/assets/css/tvplayer.css',
    'tv-player/assets/js/tvplayer.js', 'tv-player/assets/js/hls.min.js',
    'tv-player/assets/js/hls.LICENSE.txt', 'tv-player/assets/js/APACHE-2.0.txt',
    'tv-player/assets/images/signal-tv.svg',
]

def build():
    for relative in FILES:
        source = ROOT / relative
        destination = OUTPUT / relative
        destination.parent.mkdir(parents=True, exist_ok=True)
        if source.suffix == '.html':
            text = source.read_text(encoding='utf-8')
            text = text.replace('admin/login.php', 'admin/index.html').replace('>Verwaltung</a>', '>Admin-Vorschau</a>')
            notice = '<aside class="notice demo-notice" role="note"><strong>Interaktive Demo</strong> &middot; Beispieldaten. Keine Anmeldung oder Speicherung. Streams werden erst beim Ausw&auml;hlen eines Senders gestartet.</aside>'
            text = text.replace('</header>', '</header>' + notice, 1)
            if relative == 'easy-radio/player.html':
                text = text.replace("apiBaseUrl: new URL('../', window.location.href).href,", "apiBaseUrl: new URL('../', window.location.href).href,\n            genresUrl: new URL('../data/genres.json', window.location.href).href,\n            stationsUrl: new URL('../data/stations.json', window.location.href).href,")
            elif relative == 'tv-player/player.html':
                text = text.replace("../tv-json/get_stations.php", "../data/tv-stations.json")
                text = text.replace("window.signalTvSettings = {", "window.signalTvSettings = { preloadFirst: false,")
            destination.write_text(text, encoding='utf-8')
        else:
            shutil.copyfile(source, destination)
    data_dir = OUTPUT / 'data'
    data_dir.mkdir(exist_ok=True)
    for name in ['genres.json', 'stations.json', 'tv-stations.json']:
        data = json.loads((ROOT / 'demo-data' / name).read_text(encoding='utf-8'))
        (data_dir / name).write_text(json.dumps(data, ensure_ascii=False, indent=2)+'\n', encoding='utf-8')
    for name in ['admin/index.html', 'assets/js/admin-preview.js', 'assets/css/demo.css']:
        destination = OUTPUT / name
        destination.parent.mkdir(parents=True, exist_ok=True)
        shutil.copyfile(ROOT / 'demo-template' / name, destination)
    for page in OUTPUT.rglob('*.html'):
        text = page.read_text(encoding='utf-8')
        prefix = '' if page.parent == OUTPUT else '../'
        text = text.replace('</head>', '<link rel="stylesheet" href="'+prefix+'assets/css/demo.css"></head>')
        page.write_text(text, encoding='utf-8')
    (OUTPUT / '.nojekyll').write_text('', encoding='utf-8')
    print('GitHub Pages demo built:', OUTPUT)

if __name__ == '__main__':
    build()
