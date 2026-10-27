<?php include 'connection.php'; ?>
<link rel="stylesheet" href="style.css">

<nav>
    <a href="read.php">Home</a>
</nav>

<div class="container">
<h1>Zoek student</h1>

<form method="GET">
    <input type="text" name="zoek" placeholder="Zoek op voornaam">
    <button type="submit">Zoek</button>
</form>

<?php
if (isset($_GET['zoek'])) {
    $zoek = "%" . $_GET['zoek'] . "%";

    $stmt = $pdo->prepare("SELECT * FROM studenten WHERE voornaam LIKE ?");
    $stmt->execute([$zoek]);

    foreach ($stmt as $row) {
        echo $row['voornaam'] . " " . $row['achternaam'] . "<br>";
    }
}
?>
</div>