<?php
// Pas de session_start() ici, car il est appelé dans les fichiers principaux
try {
    $pdo = new PDO("mysql:host=localhost;dbname=projet", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET CHARACTER SET utf8");
} catch (PDOException $e) {
    error_log("Erreur de connexion : " . $e->getMessage());
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>