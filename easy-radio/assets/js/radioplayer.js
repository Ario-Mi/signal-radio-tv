(function () {
    'use strict';

    const settings = window.signalRadioSettings || {};
    let radioDataPromise = null;

    function joinUrl(base, path) {
        return new URL(path, base).href;
    }

    async function loadRadioData() {
        if (!radioDataPromise) {
            radioDataPromise = Promise.all([
                fetch(settings.genresUrl || joinUrl(settings.apiBaseUrl, 'radio-json/get_genres.php'), { credentials: 'same-origin' }),
                fetch(settings.stationsUrl || joinUrl(settings.apiBaseUrl, 'radio-json/get_stations.php'), { credentials: 'same-origin' })
            ]).then(async function (responses) {
                if (!responses[0].ok || !responses[1].ok) {
                    throw new Error('Radio-API nicht erreichbar');
                }

                const genres = await responses[0].json();
                const stations = await responses[1].json();

                if (!Array.isArray(genres) || !Array.isArray(stations)) {
                    throw new Error('Ungültige Radio-Daten');
                }

                return { genres: genres, stations: stations };
            });
        }

        return radioDataPromise;
    }

    function makeButton(label, className, onClick) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = label;
        button.setAttribute('aria-pressed', 'false');
        button.addEventListener('click', onClick);
        return button;
    }

    function initPlayer(root) {
        if (root.dataset.radioReady === 'true') {
            return;
        }
        root.dataset.radioReady = 'true';

        const brandLogo = root.querySelector('.rp-logo');
        const genreArea = root.querySelector('.rp-genre');
        const stationArea = root.querySelector('.rp-station');
        const currentStation = root.querySelector('.rp-current-station');
        const currentLogo = root.querySelector('.rp-current-logo');
        const audio = root.querySelector('.rp-audio-player');
        const status = root.querySelector('.rp-status');

        const fallbackLogo = settings.defaultLogo || settings.brandLogo || '';
        brandLogo.src = settings.brandLogo || fallbackLogo;
        currentLogo.src = fallbackLogo;

        // Prüfungsanforderung: Jeder Player startet bei jedem Laden mit exakt 10 %.
        audio.volume = 0.1;

        currentLogo.addEventListener('error', function () {
            if (fallbackLogo && currentLogo.src !== fallbackLogo) {
                currentLogo.src = fallbackLogo;
            }
        });

        function setPressed(container, activeButton) {
            container.querySelectorAll('button').forEach(function (button) {
                button.setAttribute('aria-pressed', button === activeButton ? 'true' : 'false');
            });
        }

        let playVersion = 0;
        let selectedStationId = null;

        audio.addEventListener('playing', function () {
            currentLogo.classList.add('is-playing');
            status.classList.remove('is-error');
            status.textContent = 'Wiedergabe laeuft.';
        });
        audio.addEventListener('pause', function () { currentLogo.classList.remove('is-playing'); });
        audio.addEventListener('error', function () {
            currentLogo.classList.remove('is-playing');
            status.classList.add('is-error');
            status.textContent = 'Dieser Stream ist momentan nicht erreichbar. Bitte einen anderen Sender waehlen.';
        });

        function renderStations(stations) {
            stationArea.replaceChildren();

            if (stations.length === 0) {
                status.textContent = 'Für dieses Genre sind keine Sender vorhanden.';
                return;
            }

            status.textContent = stations.length + ' Sender verfügbar';
            stations.forEach(function (station) {
                const stationButton = makeButton(station.name, 'rp-station-button', function () {
                    selectedStationId = String(station.id);
                    setPressed(stationArea, stationButton);
                    currentStation.textContent = 'Jetzt läuft: ' + station.name;
                    currentLogo.src = station.logo || fallbackLogo;
                    currentLogo.alt = station.name + ' Senderlogo';
                    const version = ++playVersion;
                    currentLogo.classList.remove('is-playing');
                    status.classList.remove('is-error');
                    status.textContent = 'Stream wird verbunden ...';
                    audio.src = station.stream_url;
                    audio.play().catch(function (error) {
                        if (version !== playVersion || error.name === 'AbortError') return;
                        if (error.name === 'NotAllowedError') {
                            status.textContent = 'Bitte Play druecken, um den Sender zu starten.';
                        } else {
                            status.classList.add('is-error');
                            status.textContent = 'Dieser Stream kann momentan nicht abgespielt werden.';
                        }
                    });
                });
                if (String(station.id) === selectedStationId) stationButton.setAttribute('aria-pressed', 'true');
                stationArea.appendChild(stationButton);
            });
        }

        loadRadioData().then(function (data) {
            const allButton = makeButton('Alle Sender', 'rp-genre-button', function () {
                setPressed(genreArea, allButton);
                renderStations(data.stations);
            });
            allButton.setAttribute('aria-pressed', 'true');
            genreArea.appendChild(allButton);

            data.genres.forEach(function (genre) {
                const genreButton = makeButton(genre.name, 'rp-genre-button', function () {
                    setPressed(genreArea, genreButton);
                    const filtered = data.stations.filter(function (station) {
                        return String(station.genre_id) === String(genre.id);
                    });
                    renderStations(filtered);
                });
                genreArea.appendChild(genreButton);
            });

            renderStations(data.stations);
        }).catch(function () {
            status.textContent = settings.errorMessage || 'Die Sender konnten nicht geladen werden.';
            status.classList.add('is-error');
        });
    }

    function initAllPlayers() {
        document.querySelectorAll('[data-radio-player]').forEach(initPlayer);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllPlayers);
    } else {
        initAllPlayers();
    }
}());
