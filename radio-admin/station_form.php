<?php
require_once dirname(__DIR__) . '/lib/admin.php';
$editing = $signal_editing ?? false;
$id = $editing ? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) : null;
$station = ['name' => '', 'genre_id' => '', 'stream_url' => '', 'web_url' => '', 'logo' => ''];
if ($editing) {
    if (!$id || $id < 1) { signal_error_page(400, 'Ungueltige Sender-ID.', 'radio'); }
    $query = $pdo->prepare('SELECT * FROM stations WHERE id=:id'); $query->execute(['id' => $id]);
    $station = $query->fetch();
    if (!$station) { signal_error_page(404, 'Sender nicht gefunden.', 'radio'); }
}
$genres = $pdo->query('SELECT id, name FROM genres ORDER BY name')->fetchAll();
$errors = [];
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $station = ['name' => signal_text($_POST, 'name'), 'genre_id' => filter_input(INPUT_POST, 'genre_id', FILTER_VALIDATE_INT),
        'stream_url' => signal_text($_POST, 'stream_url'), 'web_url' => signal_text($_POST, 'web_url'), 'logo' => signal_text($_POST, 'logo')];
    if ($station['name'] === '' || mb_strlen($station['name']) > 100) { $errors[] = 'Bitte einen Sendernamen mit maximal 100 Zeichen eingeben.'; }
    if (!in_array($station['genre_id'], array_column($genres, 'id'))) { $errors[] = 'Bitte ein vorhandenes Genre auswaehlen.'; }
    foreach (['stream_url' => 255, 'web_url' => 150, 'logo' => 100] as $field => $limit) {
        $value = $station[$field];
        if (mb_strlen($value) > $limit || (($value !== '' || $field === 'stream_url') && !signal_url($value, $field === 'logo'))) {
            $errors[] = 'Bitte eine gueltige HTTP/HTTPS-Adresse fuer ' . $field . ' eingeben (maximal ' . $limit . ' Zeichen).';
        }
    }
    if (!$errors) {
        if ($editing) {
            $station['id'] = $id;
            $sql = 'UPDATE stations SET name=:name, genre_id=:genre_id, stream_url=:stream_url, web_url=:web_url, logo=:logo WHERE id=:id';
        } else { $sql = 'INSERT INTO stations (name,genre_id,stream_url,web_url,logo) VALUES (:name,:genre_id,:stream_url,:web_url,:logo)'; }
        $pdo->prepare($sql)->execute($station);
        $_SESSION['signal_notice'] = 'Sender gespeichert.';
        header('Location: index.php'); exit;
    }
}
?>
<?php signal_page_start($editing ? 'Radiosender bearbeiten' : 'Radiosender hinzufuegen', 'Senderdaten und Stream-Adresse an einem Ort verwalten.', 'radio'); signal_errors($errors); ?>
<form class="card form-card" method="post"><?= signal_csrf_field() ?><div class="form-grid">
<label>Sendername<input name="name" maxlength="100" value="<?= signal_e($station['name']) ?>" placeholder="Name des Radiosenders" required></label>
<label>Genre<select name="genre_id" required><option value="">Bitte w&auml;hlen</option><?php foreach ($genres as $genre): ?><option value="<?= (int) $genre['id'] ?>" <?= $station['genre_id'] == $genre['id'] ? 'selected' : '' ?>><?= signal_e($genre['name']) ?></option><?php endforeach; ?></select></label>
<label class="full-width">Stream-Adresse<input type="url" name="stream_url" maxlength="255" value="<?= signal_e($station['stream_url']) ?>" placeholder="https://..." required><small>Direkte HTTP- oder HTTPS-Adresse zum Audio-Stream.</small></label>
<label class="full-width">Sender-Webseite <small>Optional</small><input type="url" name="web_url" maxlength="150" value="<?= signal_e($station['web_url']) ?>" placeholder="https://..."></label>
<label class="full-width">Logo-Adresse <small>Optional</small><input name="logo" maxlength="100" value="<?= signal_e($station['logo']) ?>" placeholder="https://... oder /radio_projekt/..."><small>Ohne Logo wird die SIGNAL-Ersatzgrafik verwendet.</small></label></div>
<div class="form-actions"><button class="primary" type="submit">Sender speichern</button><a class="button" href="index.php">Abbrechen</a></div></form>
<?php signal_page_end(); ?>
