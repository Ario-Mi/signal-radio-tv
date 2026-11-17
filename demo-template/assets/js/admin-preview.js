(function () {
    'use strict';
    const content = document.getElementById('preview-content');
    const buttons = document.querySelectorAll('[data-demo-tab]');
    let dataset;
    const text = (tag, value, className) => {
        const element = document.createElement(tag);
        element.textContent = value;
        if (className) element.className = className;
        return element;
    };
    function render(section) {
        if (!dataset) return;
        const genres = new Map(dataset.genres.map(g => [String(g.id), g.name]));
        const rows = section === 'genres' ? dataset.genres : section === 'tv' ? dataset.tv : dataset.stations;
        const headers = section === 'genres' ? ['Genre', 'ID'] : section === 'tv' ? ['Sender', 'Typ', 'Status'] : ['Sender', 'Genre', 'ID'];
        content.replaceChildren();
        const heading = text('div', '', 'preview-heading');
        heading.append(text('h2', section === 'genres' ? 'Genres' : section === 'tv' ? 'TV-Sender' : 'Radiosender'), text('span', rows.length+' Eintraege', 'badge'));
        content.append(heading);
        const wrapper = text('div', '', 'table-wrap');
        wrapper.tabIndex = 0;
        wrapper.setAttribute('role', 'region');
        wrapper.setAttribute('aria-label', heading.textContent);
        const table = document.createElement('table');
        const head = document.createElement('thead');
        const header = document.createElement('tr');
        headers.forEach(label => { const cell = text('th', label); cell.scope='col'; header.append(cell); });
        head.append(header); table.append(head);
        const body = document.createElement('tbody');
        rows.forEach(row => {
            const tr = document.createElement('tr');
            tr.append(text('td', row.name));
            if (section === 'genres') tr.append(text('td', row.id, 'muted'));
            else if (section === 'tv') {
                tr.append(text('td', String(row.stream_type).toUpperCase()));
                const cell = document.createElement('td'); cell.append(text('span', 'Aktiv', 'badge status-on')); tr.append(cell);
            } else tr.append(text('td', genres.get(String(row.genre_id)) || 'Weitere Sender'), text('td', row.id, 'muted'));
            body.append(tr);
        });
        table.append(body); wrapper.append(table); content.append(wrapper);
        content.append(text('p', 'Keine Daten werden gespeichert. Erstellen, Bearbeiten und Loeschen sind nur in der lokal installierten Vollversion verfuegbar.', 'preview-description'));
        buttons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.demoTab === section)));
    }
    buttons.forEach(button => button.addEventListener('click', () => render(button.dataset.demoTab)));
    Promise.all(['genres','stations','tv-stations'].map(async name => {
        const response = await fetch('../data/'+name+'.json');
        if (!response.ok) throw new Error('Beispieldaten nicht erreichbar');
        return response.json();
    })).then(([genres, stations, tv]) => { dataset={genres,stations,tv}; render('stations'); })
      .catch(() => content.replaceChildren(text('div', 'Die Beispieldaten konnten nicht geladen werden. Bitte die Seite erneut laden.', 'errors')));
}());
