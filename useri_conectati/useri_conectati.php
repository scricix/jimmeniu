
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

// Setăm ora curentă
$currentTime = time();

// Calea către fișierul JSON pentru utilizatorii șterși
$jsonFile = 'deleted_users.json';

// Verificăm dacă fișierul JSON există și îl încărcăm
$deletedUsers = [];
if (file_exists($jsonFile)) {
    $jsonContent = file_get_contents($jsonFile);
    $deletedUsers = json_decode($jsonContent, true);
}

// Selectăm utilizatorii activi din baza de date
$sql = "SELECT id, name, email, phone, last_activity FROM user_sessions";
$result = $conn->query($sql);

$activeUsers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $activeUsers[] = $row;
    }
}

// Procesăm cererea de ștergere
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $userId = $_POST['delete_user'];
    
    // Verificăm și îndepărtăm utilizatorul din lista activă
    foreach ($activeUsers as $key => $user) {
        if ($user['id'] == $userId) {
            // Adăugăm utilizatorul în fișierul JSON
            $deletedUsers[$userId] = $user;
            // Îndepărtăm utilizatorul din lista activă
            unset($activeUsers[$key]);
            break; // Ieșim din buclă după ce am găsit utilizatorul
        }
    }
    
    // Salvăm utilizatorii șterși în fișierul JSON
    file_put_contents($jsonFile, json_encode($deletedUsers, JSON_PRETTY_PRINT));
}

// Re-îmbinăm utilizatorii după ștergere
$allUsers = array_merge($activeUsers, $deletedUsers);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stare Utilizatori</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Starea Utilizatorilor</h1>
        <?php
        if (!empty($allUsers)) {
            foreach ($allUsers as $user) {
                // Verificăm dacă utilizatorul are date valide
                if (isset($user['name'], $user['email'], $user['phone'], $user['last_activity'])) {
                    // Calculăm diferența de timp pentru a stabili starea utilizatorului
                    $timeDifference = $currentTime - strtotime($user['last_activity']);
                    if ($timeDifference <= 300) {
                        $status = "Online";
                        $statusClass = "online";
                    } else {
                        $minutesOffline = floor($timeDifference / 60);
                        $hoursOffline = floor($minutesOffline / 60);
                        if ($hoursOffline > 0) {
                            $status = "Offline de " . $hoursOffline . " ore";
                        } else {
                            $status = "Offline de " . $minutesOffline . " minute";
                        }
                        $statusClass = "offline";
                    }
                    ?>
                    <div class="card">
                        <div class="user-info">
                            <div><strong>Nume:</strong> <?php echo htmlspecialchars($user['name']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></div>
                            <div><strong>Telefon:</strong> <?php echo htmlspecialchars($user['phone']); ?></div>
                            <div><strong>Ultima activitate:</strong> <?php echo htmlspecialchars($user['last_activity']); ?></div>
                            <div class="status <?php echo $statusClass; ?>"><strong>Stare:</strong> <?php echo $status; ?></div>
                        </div>
                        <form method="POST" action="">
                            <button type="submit" name="delete_user" value="<?php echo $user['id']; ?>">Șterge</button>
                        </form>
                    </div>
                    <?php
                }
            }
        } else {
            echo "<p>Nu există utilizatori în baza de date sau fișierul JSON</p>";
        }
        ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
