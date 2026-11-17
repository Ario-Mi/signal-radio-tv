"""Temporary local MariaDB instance using the already installed XAMPP binaries."""
import os
from pathlib import Path
import secrets
import socket
import subprocess
import tempfile
import time


class MySQLSandbox:
    def __enter__(self):
        self.directory = tempfile.TemporaryDirectory(prefix='cinescope-mysql-')
        self.process = None
        self.original = {key: os.environ.get(key) for key in ('DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASSWORD')}
        try:
            with socket.socket() as sock:
                sock.bind(('127.0.0.1', 0))
                port = sock.getsockname()[1]
            binaries = Path(os.environ.get('MYSQL_BIN', r'C:\xampp\mysql\bin'))
            password = secrets.token_urlsafe(24)
            data = Path(self.directory.name) / 'data'
            result = subprocess.run([str(binaries / 'mysql_install_db.exe'), '--datadir=' + str(data),
                                     '--port=' + str(port), '--password=' + password],
                                    capture_output=True, text=True, timeout=60)
            if result.returncode:
                raise RuntimeError('Temporary MariaDB initialization failed: ' + result.stderr)
            self.process = subprocess.Popen([str(binaries / 'mysqld.exe'),
                '--defaults-file=' + str(data / 'my.ini'), '--console', '--bind-address=127.0.0.1',
                '--port=' + str(port)], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
            for _ in range(100):
                if self.process.poll() is not None:
                    raise RuntimeError('Temporary MariaDB stopped during startup')
                try:
                    with socket.create_connection(('127.0.0.1', port), timeout=.2):
                        break
                except OSError:
                    time.sleep(.1)
            else:
                raise RuntimeError('Temporary MariaDB did not start')
            os.environ.update(DB_HOST='127.0.0.1', DB_PORT=str(port), DB_USER='root', DB_PASSWORD=password)
            return self
        except BaseException:
            self.__exit__(None, None, None)
            raise

    def __exit__(self, *args):
        if self.process is not None and self.process.poll() is None:
            self.process.terminate()
            self.process.wait(timeout=15)
        for key, value in self.original.items():
            if value is None:
                os.environ.pop(key, None)
            else:
                os.environ[key] = value
        self.directory.cleanup()
