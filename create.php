<?php
require_once 'connection.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student = trim($_POST['student'] ?? '');
    $klas = trim($_POST['klas'] ?? '');
    $minuten = $_POST['minuten'] ?? '';
    $reden = trim($_POST['reden'] ?? '');

    // Back-end validatie (verplicht)
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
            $stmt = $pdo->prepare('INSERT INTO meldingen (student, klas, minuten, reden) VALUES (:student, :klas, :minuten, :reden)');
            $stmt->execute([
                ':student' => $student,
                ':klas' => $klas,
                ':minuten' => $minuten,
                ':reden' => $reden
            ]);
            $success = true;
            // Velden leegmaken na succesvolle opslag
            $student = $klas = $minuten = $reden = '';
        } catch (PDOException $e) {
            $errors[] = 'Opslaan mislukt: ' . $e->getMessage();
        }
    }
}

// Bewaar waarden voor hergebruik bij fouten
$student = $student ?? '';
$klas = $klas ?? '';
$minuten = $minuten ?? '';
$reden = $reden ?? '';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nieuwe melding - Te laat</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-wrapper page-wrapper--narrow">
        <h1>Nieuwe melding toevoegen</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">De melding is succesvol opgeslagen!</div>
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

        <form method="POST" action="create.php" id="meldingForm" novalidate>
            <div class="form-group">
                <label for="student">Student *</label>
                <input type="text" id="student" name="student" value="<?= htmlspecialchars($student) ?>"
                       required minlength="1" placeholder="bijv. Jan Jansen">
            </div>
            <div class="form-group">
                <label for="klas">Klas *</label>
                <input type="text" id="klas" name="klas" value="<?= htmlspecialchars($klas) ?>"
                       required minlength="1" placeholder="bijv. 10A">
            </div>
            <div class="form-group">
                <label for="minuten">Minuten *</label>
                <input type="number" id="minuten" name="minuten" value="<?= htmlspecialchars($minuten) ?>"
                       required min="0" step="1" placeholder="0">
            </div>
            <div class="form-group">
                <label for="reden">Reden *</label>
                <textarea id="reden" name="reden" required placeholder="Waarom was je te laat?"><?= htmlspecialchars($reden) ?></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-submit btn-green">Opslaan</button>
                <a href="read.php" class="btn-back">← Terug naar overzicht</a>
            </div>
        </form>
    </div>

    <script>
        // Front-end validatie (optioneel, voor betere UX)
        document.getElementById('meldingForm').addEventListener('submit', function(e) {
            const student = document.getElementById('student').value.trim();
            const klas = document.getElementById('klas').value.trim();
            const minuten = document.getElementById('minuten').value;
            const reden = document.getElementById('reden').value.trim();

            let valid = true;

            [document.getElementById('student'), document.getElementById('klas'), document.getElementById('minuten'), document.getElementById('reden')]
                .forEach(el => el.classList.remove('invalid'));

            if (!student) {
                document.getElementById('student').classList.add('invalid');
                valid = false;
            }
            if (!klas) {
                document.getElementById('klas').classList.add('invalid');
                valid = false;
            }
            if (minuten === '' || isNaN(minuten) || parseInt(minuten, 10) < 0) {
                document.getElementById('minuten').classList.add('invalid');
                valid = false;
            }
            if (!reden) {
                document.getElementById('reden').classList.add('invalid');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
