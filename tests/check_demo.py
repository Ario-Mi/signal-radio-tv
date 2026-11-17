"""Validate the Pages artifact and local links without a browser or PHP server."""
from html.parser import HTMLParser
from pathlib import Path
import json
from urllib.parse import urlsplit, unquote

ROOT = Path(__file__).resolve().parents[1] / 'demo'
class References(HTMLParser):
    def __init__(self): super().__init__(); self.links=[]; self.forms=0
    def handle_starttag(self, tag, attrs):
        attrs=dict(attrs)
        if tag=='form': self.forms+=1
        for key in ('src','href'):
            if attrs.get(key): self.links.append(attrs[key])

def main():
    assert (ROOT/'index.html').is_file()
    for path in ROOT.rglob('*'):
        if not path.is_file():continue
        assert path.suffix.lower() not in {'.php','.sql','.env','.zip','.bak'},path
        assert path.name!='config.local.php',path
        if path.suffix=='.html':
            parser=References();parser.feed(path.read_text(encoding='utf-8'))
            assert parser.forms==0, 'Demo must not submit admin forms'
            for reference in parser.links:
                url=urlsplit(reference)
                if url.scheme or url.netloc or not url.path:continue
                target=(path.parent/unquote(url.path)).resolve()
                assert target.is_relative_to(ROOT.resolve()),(path,reference)
                assert target.is_file(),(path,reference)
    radio=(ROOT/'easy-radio/player.html').read_text(encoding='utf-8')
    tv=(ROOT/'tv-player/player.html').read_text(encoding='utf-8')
    assert 'genresUrl:' in radio and 'stationsUrl:' in radio
    assert 'get_stations.php' not in radio+tv
    assert 'preloadFirst: false' in tv
    for file in ['genres.json','stations.json','tv-stations.json']:
        assert isinstance(json.loads((ROOT/'data'/file).read_text(encoding='utf-8')),list)
    print('Pages artifact verified: static files, JSON data, read-only preview and relative links OK.')

if __name__=='__main__':main()
