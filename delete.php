<?php
require_once 'connection.php';

$error = null;
$record = null;
$deleted = false;

$id = isset($_GET['id']) ? trim($_GET['id']) : (isset($_POST['id']) ? trim($_POST['id']) : '');

// Controleer of id is meeggegeven
if (empty($id)) {
    $error = 'Geen id meeggegeven. Gebruik delete.php?id=...';
} elseif (!ctype_digit($id) && !is_numeric($id)) {
    $error = 'Ongeldig id.';
} elseif (isset($pdo)) {
    $id = (int)$id;

    // Haal melding op voor weergave of verwijdering
    try {
        $stmt = $pdo->prepare('SELECT * FROM meldingen WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            $error = 'Deze melding bestaat niet of is al verwijderd.';
            $record = null;
        }
    } catch (PDOException $e) {
        $error = 'Kon gegevens niet laden: ' . $e->getMessage();
    }
}

// Na bevestiging: voer DELETE uit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $record && isset($_POST['bevestig']) && isset($pdo)) {
    try {
        $stmt = $pdo->prepare('DELETE FROM meldingen WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $deleted = true;
    } catch (PDOException $e) {
        $error = 'Verwijderen mislukt: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melding verwijderen - Te laat</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-wrapper page-wrapper--narrow">
        <h1>Melding verwijderen</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <a href="read.php" class="btn-back">← Terug naar overzicht</a>

        <?php elseif ($deleted): ?>
            <div class="alert alert-success">De melding is succesvol verwijderd.</div>
            <a href="read.php" class="btn-back">← Terug naar overzicht</a>

        <?php elseif ($record): ?>
            <p>Weet je zeker dat je deze melding wilt verwijderen?</p>
            <div class="bevestiging">
                <p><strong>Student:</strong> <?= htmlspecialchars($record['student']) ?></p>
                <p><strong>Klas:</strong> <?= htmlspecialchars($record['klas']) ?></p>
                <p><strong>Minuten:</strong> <?= (int)$record['minuten'] ?></p>
                <p><strong>Reden:</strong> <?= htmlspecialchars($record['reden']) ?></p>
            </div>
            <div class="form-actions">
                <form method="POST" action="delete.php?id=<?= (int)$id ?>" style="display: inline;">
                    <input type="hidden" name="id" value="<?= (int)$id ?>">
                    <input type="hidden" name="bevestig" value="1">
                    <button type="submit" class="btn btn-verwijder">Ja, verwijderen</button>
                </form>
                <a href="read.php" class="btn btn-annuleer">Annuleren</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
