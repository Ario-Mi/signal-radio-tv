<?php if (!isset($signal_tv_editing, $station, $errors)) { http_response_code(404); exit; } ?>

<?php signal_page_start($signal_tv_editing ? 'TV-Sender bearbeiten' : 'TV-Sender hinzufuegen', 'Stream, Senderdaten und Sichtbarkeit im TV-Player festlegen.', 'tv'); signal_errors($errors); ?>
<form class="card form-card" method="post"><?= signal_csrf_field() ?><div class="form-grid">
<label class="full-width">Sendername<input name="name" maxlength="100" value="<?= signal_e($station['name']) ?>" required></label>
<label class="full-width">Stream-Adresse<input type="url" name="stream_url" maxlength="500" value="<?= signal_e($station['stream_url']) ?>" placeholder="https://.../master.m3u8" required></label>
<label>Stream-Typ<select name="stream_type"><option value="hls" <?= $station['stream_type'] === 'hls' ? 'selected' : '' ?>>HLS (.m3u8)</option><option value="mp4" <?= $station['stream_type'] === 'mp4' ? 'selected' : '' ?>>MP4</option></select></label>
<label>Reihenfolge<input type="number" name="sort_order" min="0" value="<?= (int) $station['sort_order'] ?>"></label>
<label class="full-width">Sender-Webseite <small>Optional</small><input type="url" name="web_url" maxlength="255" value="<?= signal_e($station['web_url']) ?>"></label>
<label class="full-width">Logo-Adresse <small>Optional</small><input name="logo" maxlength="500" value="<?= signal_e($station['logo']) ?>"><small>HTTP/HTTPS-Adresse oder lokaler Pfad. Ohne Logo wird die Ersatzgrafik verwendet.</small></label>
<label class="check full-width"><input type="checkbox" name="is_active" value="1" <?= $station['is_active'] ? 'checked' : '' ?>> Im TV-Player anzeigen</label></div>
<div class="form-actions"><button class="primary" type="submit">Sender speichern</button><a class="button" href="index.php">Abbrechen</a></div></form>
<?php signal_page_end(); ?>
