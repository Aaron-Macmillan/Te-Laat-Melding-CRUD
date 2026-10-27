<?php
require_once 'connection.php';

$error = null;
$record = null;
$success = false;
$errors = [];

$id = isset($_GET['id']) ? trim($_GET['id']) : '';

// Controleer of id is meeggegeven
if (empty($id)) {
    $error = 'Geen id meeggegeven. Gebruik update.php?id=...';
} elseif (!ctype_digit($id) && !is_numeric($id)) {
    $error = 'Ongeldig id.';
} elseif (isset($pdo)) {
    $id = (int)$id;

    // Haal bestaande gegevens op met SELECT
    try {
        $stmt = $pdo->prepare('SELECT * FROM meldingen WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$record) {
            $error = 'Deze melding bestaat niet of is verwijderd.';
            $record = null;
        }
    } catch (PDOException $e) {
        $error = 'Kon gegevens niet laden: ' . $e->getMessage();
    }
}

// Verwerk formulier bij POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $record) {
    $student = trim($_POST['student'] ?? '');
    $klas = trim($_POST['klas'] ?? '');
    $minuten = $_POST['minuten'] ?? '';
    $reden = trim($_POST['reden'] ?? '');

    // Back-end validatie
    if (empty($student)) {
        $errors[] = 'Naam student is verplicht.';
    }
    if (empty($klas)) {
        $errors[] = 'Klas is verplicht.';
    }
    if ($minuten === '' || $minuten === null) {
        $errors[] = 'Minuten te laat is verplicht.';
    } elseif (!is_numeric($minuten) || (int)$minuten < 0) {
        $errors[] = 'Minuten moet een positief getal zijn (0 of hoger).';
    } else {
        $minuten = (int)$minuten;
    }
    if (empty($reden)) {
        $errors[] = 'Reden te laat is verplicht.';
    }

    if (empty($errors) && isset($pdo)) {
        try {
            $stmt = $pdo->prepare('UPDATE meldingen SET student = :student, klas = :klas, minuten = :minuten, reden = :reden WHERE id = :id');
            $stmt->execute([
                ':student' => $student,
                ':klas' => $klas,
                ':minuten' => $minuten,
                ':reden' => $reden,
                ':id' => $id
            ]);
            $success = true;
            // Vernieuw record voor weergave
            $record = [
                'student' => $student,
                'klas' => $klas,
                'minuten' => $minuten,
                'reden' => $reden,
                'id' => $id
            ];
        } catch (PDOException $e) {
            $errors[] = 'Bijwerken mislukt: ' . $e->getMessage();
        }
    } else {
        // Bewaar ingevoerde waarden bij validatiefouten
        $record = [
            'student' => $student,
            'klas' => $klas,
            'minuten' => $minuten,
            'reden' => $reden,
            'id' => $id
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melding wijzigen - Te laat</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-wrapper page-wrapper--narrow">
        <h1>Melding wijzigen</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <a href="read.php" class="btn-back">← Terug naar overzicht</a>
        <?php elseif ($record): ?>
            <?php if ($success): ?>
                <div class="alert alert-success">De melding is succesvol bijgewerkt!</div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="update.php?id=<?= (int)$record['id'] ?>" id="updateForm" novalidate>
                <div class="form-group">
                    <label for="student">Student *</label>
                    <input type="text" id="student" name="student" value="<?= htmlspecialchars($record['student']) ?>"
                           required minlength="1" placeholder="bijv. Jan Jansen">
                </div>
                <div class="form-group">
                    <label for="klas">Klas *</label>
                    <input type="text" id="klas" name="klas" value="<?= htmlspecialchars($record['klas']) ?>"
                           required minlength="1" placeholder="bijv. 10A">
                </div>
                <div class="form-group">
                    <label for="minuten">Minuten *</label>
                    <input type="number" id="minuten" name="minuten" value="<?= htmlspecialchars($record['minuten']) ?>"
                           required min="0" step="1" placeholder="0">
                </div>
                <div class="form-group">
                    <label for="reden">Reden *</label>
                    <textarea id="reden" name="reden" required placeholder="Waarom was je te laat?"><?= htmlspecialchars($record['reden']) ?></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-submit">Bijwerken</button>
                    <a href="read.php" class="btn-back">← Terug naar overzicht</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php if ($record): ?>
    <script>
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            const student = document.getElementById('student').value.trim();
            const klas = document.getElementById('klas').value.trim();
            const minuten = document.getElementById('minuten').value;
            const reden = document.getElementById('reden').value.trim();
            let valid = true;

            [document.getElementById('student'), document.getElementById('klas'), document.getElementById('minuten'), document.getElementById('reden')]
                .forEach(el => el.classList.remove('invalid'));

            if (!student) { document.getElementById('student').classList.add('invalid'); valid = false; }
            if (!klas) { document.getElementById('klas').classList.add('invalid'); valid = false; }
            if (minuten === '' || isNaN(minuten) || parseInt(minuten, 10) < 0) {
                document.getElementById('minuten').classList.add('invalid'); valid = false;
            }
            if (!reden) { document.getElementById('reden').classList.add('invalid'); valid = false; }

            if (!valid) e.preventDefault();
        });
    </script>
    <?php endif; ?>
</body>
</html>
