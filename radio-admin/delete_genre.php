<?php
require_once dirname(__DIR__) . '/lib/admin.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { http_response_code(405); header('Allow: POST'); exit('Methode nicht erlaubt.'); }
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) { signal_error_page(400, 'Ungueltige Genre-ID.', 'genres'); }
// Existing stations keep their genre: do not create orphaned references.
$check = $pdo->prepare('SELECT COUNT(*) FROM stations WHERE genre_id = :id');
$check->execute(['id' => $id]);
if ($check->fetchColumn() > 0) { signal_error_page(409, 'Dieses Genre wird noch von Sendern verwendet. Bitte zuerst die Sender einem anderen Genre zuordnen.', 'genres'); }
$stmt = $pdo->prepare('DELETE FROM genres WHERE id = :id');
$stmt->execute(['id' => $id]);
$_SESSION['signal_notice'] = 'Genre geloescht.';
        header('Location: create_genre.php'); exit;
