<?php
require_once 'connection.php';

$records = [];
$error = null;

if (isset($pdo)) {
    try {
        $stmt = $pdo->query('SELECT * FROM meldingen ORDER BY minuten DESC');
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = 'Kon gegevens niet laden: ' . $e->getMessage();
    }
}

$minuten = array_column($records, 'minuten');
$hoogste = count($minuten) > 0 ? max($minuten) : 0;
$totaal = count($minuten) > 0 ? array_sum($minuten) : 0;
$gemiddeld = count($minuten) > 0 ? $totaal / count($minuten) : 0;

function getMinutenClass($minuten) {
    if ($minuten == 0) return 'min-normaal';
    if ($minuten <= 30) return 'min-rood-tekst';
    return 'min-rood-achtergrond';
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overzicht studenten die te laat waren</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <h1>Overzicht studenten die te laat waren</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (empty($records)): ?>
            <div class="geen-meldingen">Geen meldingen gevonden.</div>
        <?php else: ?>
            <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Klas</th>
                        <th>Minuten</th>
                        <th>Reden</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student']) ?></td>
                            <td><?= htmlspecialchars($row['klas']) ?></td>
                            <td class="<?= getMinutenClass((int)$row['minuten']) ?>">
                                <?= (int)$row['minuten'] ?>
                            </td>
                            <td><?= htmlspecialchars($row['reden']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="update.php?id=<?= (int)$row['id'] ?>" class="btn btn-wijzig btn-sm">wijzig</a>
                                    <a href="delete.php?id=<?= (int)$row['id'] ?>" class="btn btn-verwijder btn-sm">verwijder</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>

        <a href="create.php" class="btn btn-add"> Student Toevoegen</a>

        <div class="statistieken">
            <h2>Statistieken</h2>
            <p><strong>Hoogste aantal minuten te laat:</strong> <?= $hoogste ?></p>
            <p><strong>Gemiddeld aantal minuten:</strong> <?= number_format($gemiddeld, 0) ?></p>
            <p><strong>Totaal aantal minuten:</strong> <?= $totaal ?></p>
        </div>
    </div>
</body>
</html>
