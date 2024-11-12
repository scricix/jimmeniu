<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "magazin";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    /**
     * Handles the case where the database connection fails by printing an error message.
     */
    die("Conexiunea la baza de date a eșuat: " . $e->getMessage());
}


?>