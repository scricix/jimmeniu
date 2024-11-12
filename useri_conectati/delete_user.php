<?php
// Conectare la baza de date
$servername = "localhost";
$username = "root"; // Schimbați cu utilizatorul vostru
$password = ""; // Schimbați cu parola voastră
$dbname = "magazin"; // Numele bazei de date

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificăm conexiunea
if ($conn->connect_error) {
    die("Conexiunea a eșuat: " . $conn->connect_error);
}

// Calea către fișierul JSON pentru utilizatorii șterși
$jsonFile = 'deleted_users.json';

// Verificăm dacă datele au fost trimise corect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['user_source'])) {
    $userId = $_POST['user_id'];
    $userSource = $_POST['user_source'];

    if ($userSource === 'database') {
        // Ștergem utilizatorul din baza de date
        $sql = "DELETE FROM user_sessions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            echo "Utilizatorul a fost șters din baza de date.";
        } else {
            echo "A apărut o eroare la ștergerea utilizatorului.";
        }
    } elseif ($userSource === 'json') {
        // Ștergem utilizatorul din fișierul JSON
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $deletedUsers = json_decode($jsonContent, true);

            if (isset($deletedUsers[$userId])) {
                unset($deletedUsers[$userId]);
                file_put_contents($jsonFile, json_encode($deletedUsers, JSON_PRETTY_PRINT));
                echo "Utilizatorul a fost șters din fișierul JSON.";
            } else {
                echo "Utilizatorul nu a fost găsit în fișierul JSON.";
            }
        }
    }
} else {
    echo "Datele trimise sunt incomplete.";
}

$conn->close();
?>
