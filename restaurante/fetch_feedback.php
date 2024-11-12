<?php
// Conectare la baza de date
$mysqli = new mysqli("localhost", "root", "", "magazin");

// Verifică conexiunea
if ($mysqli->connect_error) {
    die("Conexiunea a eșuat: " . $mysqli->connect_error);
}

// Verifică dacă `name` este definit
if (!isset($_GET['name'])) {
    die("Numele restaurantului nu este specificat.");
}

$restaurant_name = $_GET['name'];

// Obține feedback-ul pentru restaurantul curent
$query = "SELECT * FROM feedback WHERE restaurant_name = ? ORDER BY created_at DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("s", $restaurant_name);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo '<div class="feedback-message">';
    echo '<strong>' . htmlspecialchars($row['restaurant_name']) . ':</strong> ';
    echo htmlspecialchars($row['message']);
    echo '</div>';
}

$stmt->close();
$mysqli->close();
?>
