"""Run: python -B tests/test_app.py. Uses a temporary MariaDB, never the live database."""
import http.client
import json
import os
from pathlib import Path
import re
import secrets
import socket
import subprocess
import tempfile
import time
import unittest
from urllib.parse import urlencode
from mysql_sandbox import MySQLSandbox

ROOT = Path(__file__).resolve().parents[1]
PHP = os.environ.get('PHP_BINARY', r'C:\xampp\php\php.exe')

class AppChecks(unittest.TestCase):
    @classmethod
    def fixture(cls, action):
        r = subprocess.run([PHP, str(ROOT/'tests/db_fixture.php'), action, cls.database],
                           capture_output=True,text=True,timeout=15)
        if r.returncode: raise RuntimeError(r.stderr)
        return r.stdout

    @classmethod
    def setUpClass(cls):
        cls.enterClassContext(MySQLSandbox())
        cls.database = 'signal_test_' + secrets.token_hex(8)
        cls.fixture('create')
        cls.addClassCleanup(cls.fixture,'drop')
        cls.password = secrets.token_urlsafe(24)
        hash_ = subprocess.run([PHP,'-r','echo password_hash($argv[1], PASSWORD_DEFAULT);',cls.password],
                               capture_output=True,text=True,check=True).stdout
        cls.sessions = cls.enterClassContext(tempfile.TemporaryDirectory(prefix='signal-sessions-'))
        with socket.socket() as s:
            s.bind(('127.0.0.1',0));cls.port=s.getsockname()[1]
        env = dict(os.environ,DB_NAME=cls.database,ADMIN_USERNAME='test-admin',ADMIN_PASSWORD_HASH=hash_)
        cls.server = subprocess.Popen([PHP,'-d','session.save_path='+cls.sessions,'-S',f'127.0.0.1:{cls.port}','-t',str(ROOT)],
                                      env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
        cls.addClassCleanup(cls.stop_server)
        for _ in range(50):
            try:
                with socket.create_connection(('127.0.0.1',cls.port),timeout=.2): return
            except OSError:time.sleep(.1)
        raise RuntimeError('PHP server did not start')

    @classmethod
    def stop_server(cls):
        cls.server.terminate();cls.server.wait(timeout=5)

    def setUp(self): self.cookie=''

    def request(self,path,data=None):
        conn=http.client.HTTPConnection('127.0.0.1',self.port,timeout=10)
        headers={'Cookie':self.cookie}
        if data is not None:headers['Content-Type']='application/x-www-form-urlencoded'
        conn.request('POST' if data is not None else 'GET',path,urlencode(data) if data is not None else None,headers)
        r=conn.getresponse();body=r.read().decode('utf-8');cookie=r.getheader('Set-Cookie')
        if cookie:self.cookie=cookie.split(';')[0]
        result=(r.status,body,r.getheader('Location'),cookie)
        conn.close();return result

    def token(self,path='/admin/login.php'):
        r=self.request(path);self.assertEqual(r[0],200)
        match=re.search(r'name="csrf_token" value="([a-f0-9]+)"',r[1])
        self.assertIsNotNone(match,r[1][:200]);return match[1]

    def login(self):
        token=self.token();before=self.cookie
        r=self.request('/admin/login.php',dict(csrf_token=token,username='test-admin',password=self.password))
        self.assertEqual(r[0],302);self.assertNotEqual(before,self.cookie)

    def post(self,path,**data):
        data['csrf_token']=self.token('/radio-admin/index.php')
        return self.request(path,data)

    def snapshot(self):return json.loads(self.fixture('snapshot'))

    def test_shared_design_assets_and_forms(self):
        from urllib.parse import urljoin, urlsplit
        status, login, _, _ = self.request('/admin/login.php')
        self.assertEqual(status, 200)
        self.assertIn('/assets/css/studio.css', login)
        self.login()
        snapshot = self.snapshot()
        genre = snapshot['genres'][0]['id']
        radio = snapshot['stations'][0]['id']
        tv = snapshot['tv_stations'][0]['id']
        paths = ['/', '/easy-radio/player.html', '/tv-player/player.html', '/radio-admin/index.php',
                 '/radio-admin/create_genre.php', '/radio-admin/edit_genre.php?id='+str(genre),
                 '/radio-admin/create_station.php', '/radio-admin/edit_station.php?id='+str(radio),
                 '/tv-admin/index.php', '/tv-admin/create_station.php', '/tv-admin/edit_station.php?id='+str(tv)]
        for path in paths:
            with self.subTest(path=path):
                status, body, _, _ = self.request(path)
                self.assertEqual(status, 200)
                self.assertIn('class="signal-site"', body)
                self.assertIn('studio-header', body)
                self.assertIn('studio-footer', body)
                self.assertNotIn('\u00c3', body)
                for href in re.findall(r'<link[^>]+href="([^"<>]+)"', body):
                    asset = urlsplit(urljoin('http://localhost'+path, href))
                    asset_path = asset.path + ('?'+asset.query if asset.query else '')
                    self.assertEqual(self.request(asset_path)[0], 200, asset_path)

    def test_public_players_and_json(self):
        for path in ['/easy-radio/player.html','/tv-player/player.html']:
            self.assertEqual(self.request(path)[0],200)
        for path in ['/radio-json/get_stations.php','/radio-json/get_genres.php','/tv-json/get_stations.php']:
            r=self.request(path);self.assertEqual(r[0],200);self.assertIsInstance(json.loads(r[1]),list)
            self.assertTrue(json.loads(r[1]))

    def test_admin_requires_login_without_data_changes(self):
        before=self.snapshot()
        paths=['/radio-admin/index.php','/radio-admin/create_station.php','/radio-admin/edit_station.php?id=1',
               '/radio-admin/create_genre.php','/radio-admin/edit_genre.php?id=1',
               '/radio-admin/delete_station.php?id=1','/radio-admin/delete_genre.php?id=1',
               '/tv-admin/index.php','/tv-admin/create_station.php','/tv-admin/edit_station.php?id=1',
               '/tv-admin/delete_station.php','/tv-admin/install_demo_stations.php']
        for path in paths:
            with self.subTest(path=path):
                r=self.request(path);self.assertEqual(r[0],302);self.assertTrue(r[2].endswith('/admin/login.php'))
                self.assertEqual(self.request(path,{'id':'1'})[0],401)
        self.assertEqual(self.snapshot(),before)

    def test_login_csrf_cookie_and_logout(self):
        r=self.request('/admin/login.php');self.assertIn('HttpOnly',r[3]);self.assertIn('SameSite=Lax',r[3])
        self.assertEqual(self.request('/admin/login.php',{'username':'test-admin','password':self.password})[0],403)
        self.login()
        before=self.snapshot()
        for path in ['/radio-admin/create_station.php','/radio-admin/delete_station.php',
                     '/radio-admin/delete_genre.php','/tv-admin/create_station.php',
                     '/tv-admin/delete_station.php','/tv-admin/install_demo_stations.php']:
            for data in ({'id':'1'},{'csrf_token':'bad','id':'1'},{'csrf_token[]':'bad','id':'1'}):
                self.assertEqual(self.request(path,data)[0],403)
        self.assertEqual(self.snapshot(),before)
        self.assertEqual(self.request('/admin/logout.php')[0],405)
        self.assertEqual(self.post('/admin/logout.php')[0],302)
        self.assertEqual(self.request('/radio-admin/index.php')[0],302)

    def test_radio_and_genre_crud_validation(self):
        self.login();before=self.snapshot()
        label='Test Genre '+secrets.token_hex(3)
        self.assertEqual(self.post('/radio-admin/create_genre.php',name=label)[0],302)
        genre=next(g for g in self.snapshot()['genres'] if g['name']==label)
        self.assertEqual(self.post('/radio-admin/edit_genre.php?id='+str(genre['id']),name=label+' edited')[0],302)
        data=dict(name='Test Radio',genre_id=genre['id'],stream_url='javascript:alert(1)',web_url='',logo='')
        r=self.post('/radio-admin/create_station.php',**data);self.assertEqual(r[0],200)
        self.assertEqual(len(self.snapshot()['stations']),len(before['stations']))
        data.update(stream_url='https://example.org/test.mp3')
        self.assertEqual(self.post('/radio-admin/create_station.php',**data)[0],302)
        station=next(s for s in self.snapshot()['stations'] if s['name']=='Test Radio')
        self.assertEqual(self.post('/radio-admin/delete_genre.php',id=genre['id'])[0],409)
        data['name']='Edited Test Radio'
        self.assertEqual(self.post('/radio-admin/edit_station.php?id='+str(station['id']),**data)[0],302)
        self.assertEqual(self.request('/radio-admin/delete_station.php?id='+str(station['id']))[0],405)
        self.assertEqual(self.post('/radio-admin/delete_station.php',id=station['id'])[0],302)
        self.assertEqual(self.post('/radio-admin/delete_genre.php',id=genre['id'])[0],302)
        self.assertEqual(self.snapshot(),before)
        self.assertEqual(self.request('/radio-admin/edit_genre.php?id=999999')[0],404)
        self.assertEqual(self.request('/radio-admin/edit_station.php?id=999999')[0],404)

    def test_tv_crud_and_demo_install(self):
        self.login();before=self.snapshot()
        data=dict(name='Test TV',stream_url='ftp://example.org/test.m3u8',stream_type='hls',
                  web_url='',logo='',sort_order='99',is_active='1')
        self.assertEqual(self.post('/tv-admin/create_station.php',**data)[0],200)
        self.assertEqual(self.snapshot(),before)
        data['stream_url']='https://example.org/test.m3u8'
        self.assertEqual(self.post('/tv-admin/create_station.php',**data)[0],302)
        station=next(s for s in self.snapshot()['tv_stations'] if s['name']=='Test TV')
        self.assertTrue(any(s['id']==station['id'] for s in json.loads(self.request('/tv-json/get_stations.php')[1])))
        data.pop('is_active')
        self.assertEqual(self.post('/tv-admin/edit_station.php?id='+str(station['id']),**data)[0],302)
        self.assertFalse(any(s['id']==station['id'] for s in json.loads(self.request('/tv-json/get_stations.php')[1])))
        self.assertEqual(self.post('/tv-admin/delete_station.php',id=station['id'])[0],302)
        self.assertEqual(self.snapshot(),before)
        self.assertEqual(self.post('/tv-admin/install_demo_stations.php')[0],302)
        channels=json.loads(self.request('/tv-json/get_stations.php')[1])
        self.assertTrue({'Das Erste','ZDF','tagesschau24','ARTE','phoenix','DW Deutsch'}.issubset({s['name'] for s in channels}))
        self.assertEqual(self.post('/tv-admin/install_demo_stations.php')[0],302)
        self.assertEqual(len(json.loads(self.request('/tv-json/get_stations.php')[1])),len(channels))

if __name__=='__main__':unittest.main(verbosity=2)
