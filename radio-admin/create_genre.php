<?php

// db.php einbinden, damit wir die Datenbank-Verbindung ($pdo) benutzen koennen.
require_once dirname(__DIR__) . '/lib/admin.php';

$stmt_genres = $pdo->query("SELECT * FROM genres");
$genres = $stmt_genres->fetchAll(PDO::FETCH_ASSOC);

// Array fuer Fehlermeldungen.
$errors = [];

// Merkt sich, ob das Speichern erfolgreich war.
$success = false;

// Dieser Code laeuft nur, wenn das Formular abgeschickt wurde.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Genre-Name aus dem Formular holen.
    // trim() entfernt Leerzeichen am Anfang und Ende.
    $name = signal_text($_POST, 'name');

    // Pruefen, ob der Genre-Name ausgefuellt ist.
    if ($name !== '' && mb_strlen($name) <= 50) {

        // SQL vorbereiten: Neues Genre wird in die Tabelle "genres" eingefuegt.
        $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (:name)");

        // SQL ausfuehren und den Formularwert einsetzen.
        $stmt->execute([
            ':name' => $name
        ]);

        // Speichern war erfolgreich.
        $success = true;

        // Nach dem Speichern zur Startseite weiterleiten.
        $_SESSION['signal_notice'] = 'Genre hinzugefuegt.';
        header('Location: index.php');
        exit;
    } else {
        // Fehlermeldung, wenn das Feld leer ist.
        $errors[] = 'Bitte einen Genre-Namen mit maximal 50 Zeichen eingeben.';
    }
}

?>
<?php signal_page_start('Genres', 'Genres anlegen und deine Radiosender uebersichtlich ordnen.', 'genres'); signal_errors($errors); ?>
<form class="card form-card" method="post"><?= signal_csrf_field() ?><h2>Neues Genre</h2><label for="genre-name">Genre-Name<input id="genre-name" name="name" maxlength="50" value="<?= signal_e($name ?? '') ?>" placeholder="Zum Beispiel Jazz" required></label><div class="form-actions"><button class="primary" type="submit">Genre hinzuf&uuml;gen</button></div></form>
<h2>Vorhandene Genres <span class="badge"><?= count($genres) ?></span></h2>
<?php if (!$genres): ?><div class="empty-state">Noch keine Genres vorhanden.</div><?php else: ?>
<div class="table-wrap" role="region" aria-label="Vorhandene Genres" tabindex="0"><table><thead><tr><th scope="col">Genre</th><th scope="col">ID</th><th scope="col">Aktionen</th></tr></thead><tbody>
<?php foreach ($genres as $genre): ?><tr><td><strong><?= signal_e($genre['name']) ?></strong></td><td class="muted"><?= (int) $genre['id'] ?></td><td><div class="row-actions"><a class="button" href="edit_genre.php?id=<?= (int) $genre['id'] ?>">Bearbeiten</a><form class="inline" method="post" action="delete_genre.php" onsubmit="return confirm('Genre wirklich loeschen?')"><?= signal_csrf_field() ?><input type="hidden" name="id" value="<?= (int) $genre['id'] ?>"><button class="danger" type="submit">L&ouml;schen</button></form></div></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
<?php signal_page_end(); ?>
