<?php
$dsn = 'mysql:host=localhost;dbname=school_te_melding;charset=utf8mb4';
$user = 'root';
$pass = '';

try {
  $pdo = new PDO($dsn, $user, $pass);
} catch (PDOException $e) {
  echo "Verbinding mislukt: " . $e->getMessage();
}