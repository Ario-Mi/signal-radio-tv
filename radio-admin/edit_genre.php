<?php

// db.php einbinden, damit wir die Datenbank-Verbindung ($pdo) benutzen koennen.
require_once dirname(__DIR__) . '/lib/admin.php';

// ID aus der URL holen.
// Beispiel: edit_genre.php?id=3
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { signal_error_page(400, 'Ungueltige Genre-ID.', 'genres'); }

// Das Genre aus der Datenbank holen, das zu dieser ID gehoert.
$stmt = $pdo->prepare("SELECT * FROM genres WHERE id = :id");

// Die ID in den Platzhalter :id einsetzen und SQL ausfuehren.
$stmt->execute([':id' => $id]);

// Das gefundene Genre speichern.
$genre = $stmt->fetch();
if (!$genre) { signal_error_page(404, 'Genre nicht gefunden.', 'genres'); }

// Dieser Code laeuft nur, wenn das Formular abgeschickt wurde.
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Neuen Genre-Namen aus dem Formular holen.
    // trim() entfernt Leerzeichen am Anfang und Ende.
    $genre_name = signal_text($_POST, 'name');

    // Pruefen, ob der Name nicht leer ist.
    if ($genre_name !== '' && mb_strlen($genre_name) <= 50) {

        // Genre in der Datenbank aktualisieren.
        $stmt = $pdo->prepare("UPDATE genres SET name = :name WHERE id = :id");

        // Neue Werte in die Platzhalter einsetzen und SQL ausfuehren.
        $stmt->execute([
            ':name' => $genre_name,
            ':id' => $id
        ]);

        // Nach dem Speichern wieder auf diese Bearbeiten-Seite weiterleiten.
        $_SESSION['signal_notice'] = 'Genre gespeichert.';
        header('Location: edit_genre.php?id=' . $id);
        exit;
    } else {
        $errors[] = 'Bitte einen Genre-Namen mit maximal 50 Zeichen eingeben.';
        $genre['name'] = $genre_name;
    }
}

?>
<?php signal_page_start('Genre bearbeiten', 'Passe den Namen an. Die zugeordneten Sender bleiben erhalten.', 'genres'); signal_errors($errors); ?>
<form class="card form-card" method="post"><?= signal_csrf_field() ?><label for="genre-name">Genre-Name<input id="genre-name" name="name" maxlength="50" value="<?= signal_e($genre['name']) ?>" required></label><div class="form-actions"><button class="primary" type="submit">&Auml;nderungen speichern</button><a class="button" href="create_genre.php">Abbrechen</a></div></form>
<?php signal_page_end(); ?>
