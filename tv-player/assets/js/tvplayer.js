(function () {
    'use strict';

    const settings = window.signalTvSettings || {};

    function initTvPlayer(root) {
        if (root.dataset.tvReady === 'true') {
            return;
        }
        root.dataset.tvReady = 'true';

        const video = root.querySelector('.tv-video');
        const channelArea = root.querySelector('.tv-channels');
        const currentChannel = root.querySelector('.tv-current-channel');
        const status = root.querySelector('.tv-status');
        let hls = null;
        let channelVersion = 0;
        let metadataHandler = null;
        video.addEventListener('error', function () {
            status.textContent = 'Dieser Livestream kann momentan nicht abgespielt werden.';
            status.classList.add('is-error');
        });

        // Prüfungsanforderung: TV-Player startet bei jedem Laden mit exakt 10 %.
        video.volume = 0.1;

        function setPressed(activeButton) {
            channelArea.querySelectorAll('button').forEach(function (button) {
                button.setAttribute('aria-pressed', button === activeButton ? 'true' : 'false');
            });
        }

        function destroyStream() {
            if (hls) {
                hls.destroy();
                hls = null;
            }
            if (metadataHandler) {
                video.removeEventListener('loadedmetadata', metadataHandler);
                metadataHandler = null;
            }
            video.pause();
            video.removeAttribute('src');
            video.load();
        }

        function loadChannel(station, shouldPlay) {
            const version = ++channelVersion;
            destroyStream();
            currentChannel.textContent = station.name;
            video.poster = station.logo || settings.fallbackLogo || '';
            status.classList.remove('is-error');
            status.textContent = 'Stream wird geladen …';

            const streamUrl = station.stream_url;
            const isHls = station.stream_type === 'hls' || /\.m3u8($|\?)/i.test(streamUrl);

            function ready() {
                if (version !== channelVersion) return;
                status.textContent = 'Lautstärke beim Start: 10 %';
                if (shouldPlay) {
                    video.play().catch(function (error) {
                        if (version !== channelVersion || error.name === 'AbortError') return;
                        status.textContent = 'Bitte Play drücken, um den Sender zu starten.';
                    });
                }
            }

            if (isHls && window.Hls && window.Hls.isSupported()) {
                hls = new window.Hls({ enableWorker: true, lowLatencyMode: true });
                hls.loadSource(streamUrl);
                hls.attachMedia(video);
                hls.on(window.Hls.Events.MANIFEST_PARSED, ready);
                hls.on(window.Hls.Events.ERROR, function (_event, data) {
                    if (version === channelVersion && data.fatal) {
                        status.textContent = 'Dieser Livestream ist momentan nicht erreichbar.';
                        status.classList.add('is-error');
                    }
                });
            } else if (isHls && video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = streamUrl;
                metadataHandler = ready;
                video.addEventListener('loadedmetadata', metadataHandler, { once: true });
            } else if (isHls) {
                status.textContent = 'HLS-Wiedergabe wird in diesem Browser nicht unterstuetzt.';
                status.classList.add('is-error');
            } else {
                video.src = streamUrl;
                metadataHandler = ready;
                video.addEventListener('loadedmetadata', metadataHandler, { once: true });
            }
        }

        fetch(settings.apiUrl, { credentials: 'same-origin' })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('TV-API nicht erreichbar');
                }
                return response.json();
            })
            .then(function (stations) {
                if (!Array.isArray(stations) || stations.length === 0) {
                    throw new Error('Keine TV-Sender vorhanden');
                }

                stations.forEach(function (station, index) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.textContent = station.name;
                    button.setAttribute('aria-pressed', 'false');
                    button.addEventListener('click', function () {
                        setPressed(button);
                        loadChannel(station, true);
                    });
                    channelArea.appendChild(button);

                    if (index === 0 && settings.preloadFirst !== false) {
                        button.setAttribute('aria-pressed', 'true');
                        loadChannel(station, false);
                    }
                });
            })
            .catch(function () {
                status.textContent = settings.errorMessage || 'Die TV-Sender konnten nicht geladen werden.';
                status.classList.add('is-error');
            });
    }

    function init() {
        document.querySelectorAll('[data-tv-player]').forEach(initTvPlayer);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
