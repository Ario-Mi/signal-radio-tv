(function () {
    'use strict';
    const previews = document.querySelectorAll('[data-audio-preview]');
    previews.forEach(function (audio) {
        audio.volume = 0.1;
        audio.addEventListener('play', function () {
            previews.forEach(function (other) { if (other !== audio) other.pause(); });
            const status = audio.parentElement.querySelector('.preview-status');
            if (status) status.textContent = '';
        });
        audio.addEventListener('error', function () {
            const status = audio.parentElement.querySelector('.preview-status');
            if (status) status.textContent = 'Stream momentan nicht erreichbar.';
        });
    });
    document.querySelectorAll('img[data-fallback]').forEach(function (image) {
        image.addEventListener('error', function () {
            const fallback = new URL(image.dataset.fallback, document.baseURI).href;
            if (image.src !== fallback) image.src = fallback;
        });
    });
}());
