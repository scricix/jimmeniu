<?php
// Conectare la baza de date
$mysqli = new mysqli("localhost", "root", "", "magazin");

// Verifică conexiunea
if ($mysqli->connect_error) {
    die("Conexiunea a eșuat: " . $mysqli->connect_error);
}

// Verifică dacă `name` și `message` sunt definite
if (!isset($_POST['name']) || !isset($_POST['message'])) {
    die("Numele restaurantului sau mesajul nu sunt specificate.");
}

$restaurant_name = $_POST['name'];
$message = $_POST['message'];

// Adaugă feedback-ul
$stmt = $mysqli->prepare("INSERT INTO feedback (restaurant_name, message) VALUES (?, ?)");
$stmt->bind_param("ss", $restaurant_name, $message);
$stmt->execute();

$stmt->close();
$mysqli->close();
?>
