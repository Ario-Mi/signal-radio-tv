<?php
require_once 'bootstrap.php';

$stmt = $pdo->query('SELECT * FROM tv_stations ORDER BY sort_order, name');
$stations = $stmt->fetchAll();
?>
<?php signal_page_start('TV-Sender', 'Livestreams verwalten und die Reihenfolge im Player festlegen.', 'tv'); ?>
<div class="page-heading"><span class="badge"><?= count($stations) ?> Sender</span><div class="actions"><a class="button primary" href="create_station.php">Sender hinzuf&uuml;gen</a><form class="inline" action="install_demo_stations.php" method="post" onsubmit="return confirm('Standardsender ergaenzen und vorhandene gleichnamige Sender aktualisieren?')"><?= signal_csrf_field() ?><button type="submit">Standardsender erg&auml;nzen</button></form><a class="button" href="../tv-player/player.html">Zum TV-Player</a></div></div>
<?php if (!$stations): ?><div class="empty-state">Noch keine TV-Sender vorhanden. Lege einen Sender an oder erg&auml;nze die Standardsender.</div><?php else: ?>
<div class="table-wrap" role="region" aria-label="TV-Sender" tabindex="0"><table><thead><tr><th scope="col">Reihenfolge</th><th scope="col">Sender</th><th scope="col">Typ</th><th scope="col">Status</th><th scope="col">Webseite</th><th scope="col">Aktionen</th></tr></thead><tbody>
<?php foreach ($stations as $station): ?><tr><td class="muted"><?= (int) $station['sort_order'] ?></td><td><strong><?= signal_e($station['name']) ?></strong></td><td><span class="badge"><?= signal_e(strtoupper($station['stream_type'])) ?></span></td><td><span class="badge <?= $station['is_active'] ? 'status-on' : 'status-off' ?>"><?= $station['is_active'] ? 'Aktiv' : 'Inaktiv' ?></span></td>
<td><?php if ($station['web_url'] && signal_url($station['web_url'])): ?><a class="button" href="<?= signal_e($station['web_url']) ?>" target="_blank" rel="noopener noreferrer">&Ouml;ffnen &nearr;</a><?php else: ?><span class="muted">&mdash;</span><?php endif; ?></td>
<td><div class="row-actions"><a class="button" href="edit_station.php?id=<?= (int) $station['id'] ?>">Bearbeiten</a><form class="inline" method="post" action="delete_station.php" onsubmit="return confirm('TV-Sender wirklich loeschen?')"><?= signal_csrf_field() ?><input type="hidden" name="id" value="<?= (int) $station['id'] ?>"><button class="danger" type="submit">L&ouml;schen</button></form></div></td></tr><?php endforeach; ?>
</tbody></table></div><p class="table-note">Auf kleinen Bildschirmen kann die Tabelle seitlich gescrollt werden.</p><?php endif; ?>
<?php signal_page_end(); ?>
