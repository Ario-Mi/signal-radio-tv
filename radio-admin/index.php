<?php
// db.php einbinden, damit wir die Datenbank-Verbindung ($pdo) benutzen koennen.
require_once dirname(__DIR__) . '/lib/admin.php';

// SQL-Befehl: Alle Sender holen und den Genre-Namen dazu laden.
$sql = "SELECT stations.*, genres.name AS genre_name
        FROM stations
        LEFT JOIN genres ON stations.genre_id = genres.id";

// SQL-Befehl ausfuehren.
$stmt = $pdo->query($sql);

// Alle gefundenen Sender als Array speichern.
// PDO::FETCH_ASSOC bedeutet: Wir bekommen die Spaltennamen als Array-Schluessel.
// Beispiel: $radio['name'], $radio['stream_url'], $radio['logo']
$radios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php signal_page_start('Radiosender', 'Sender verwalten, Streams probehoeren und Genres zuordnen.', 'radio'); ?>
<div class="page-heading"><span class="badge"><?= count($radios) ?> Sender</span><div class="actions"><a class="button primary" href="create_station.php">Sender hinzuf&uuml;gen</a><a class="button" href="../easy-radio/player.html">Zum Radio-Player</a></div></div>
<?php if (!$radios): ?><div class="empty-state">Noch keine Radiosender vorhanden. Lege deinen ersten Sender an.</div><?php else: ?>
<div class="table-wrap" role="region" aria-label="Radiosender" tabindex="0"><table><thead><tr><th scope="col">Sender</th><th scope="col">Genre</th><th scope="col">Stream-Vorschau</th><th scope="col">Webseite</th><th scope="col">Aktionen</th></tr></thead><tbody>
<?php foreach ($radios as $radio): ?><tr>
<td><div class="station-identity"><img class="station-logo" src="<?= signal_e($radio['logo'] ?: '../easy-radio/assets/images/signal-radio.svg') ?>" data-fallback="../easy-radio/assets/images/signal-radio.svg" alt="<?= signal_e($radio['name']) ?> Logo" width="48" height="48"><div><strong><?= signal_e($radio['name']) ?></strong><small>ID <?= (int) $radio['id'] ?></small></div></div></td>
<td><span class="badge"><?= signal_e($radio['genre_name'] ?? 'Ohne Genre') ?></span></td>
<td><audio class="preview-audio" data-audio-preview controls preload="none" src="<?= signal_e($radio['stream_url']) ?>" aria-label="<?= signal_e($radio['name']) ?> vorh&ouml;ren"></audio><span class="preview-status" role="status"></span></td>
<td><?php if (signal_url((string) $radio['web_url'])): ?><a class="button" href="<?= signal_e($radio['web_url']) ?>" target="_blank" rel="noopener noreferrer">&Ouml;ffnen &nearr;</a><?php else: ?><span class="muted">&mdash;</span><?php endif; ?></td>
<td><div class="row-actions"><a class="button" href="edit_station.php?id=<?= (int) $radio['id'] ?>">Bearbeiten</a><form class="inline" method="post" action="delete_station.php" onsubmit="return confirm('Radiosender wirklich loeschen?')"><?= signal_csrf_field() ?><input type="hidden" name="id" value="<?= (int) $radio['id'] ?>"><button class="danger" type="submit" aria-label="<?= signal_e($radio['name']) ?> l&ouml;schen">L&ouml;schen</button></form></div></td>
</tr><?php endforeach; ?></tbody></table></div><p class="table-note">Auf kleinen Bildschirmen kann die Tabelle seitlich gescrollt werden.</p><?php endif; ?>
<?php signal_page_end(); ?>
