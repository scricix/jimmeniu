<?php
// Conectare la baza de date
$mysqli = new mysqli("localhost", "root", "", "magazin");

// Verifică conexiunea
if ($mysqli->connect_error) {
    die("Conexiunea a eșuat: " . $mysqli->connect_error);
}

// Specifică ID-ul restaurantului pentru reclamă
$restaurantID = 42; // Aici setezi ID-ul restaurantului pe care vrei să-l promovezi

// Interogare pentru obținerea restaurantului specific pentru reclamă
$adQuery = "SELECT * FROM restaurants WHERE id = ?";
$stmt = $mysqli->prepare($adQuery);
$stmt->bind_param("i", $restaurantID);
$stmt->execute();
$adResult = $stmt->get_result();
$adRestaurant = $adResult->fetch_assoc();

// Interogare baza de date pentru a obține toate restaurantele
$query = "SELECT * FROM restaurants";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/restaurant1.css">
    <title>Prezentare Restaurante</title>
    <style>
        .menu-container {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1000;
        }
        .menu-icon {
            background-color: #333;
            color: white;
            border: none;
            padding: 10px;
            font-size: 20px;
            cursor: pointer;
        }
        .menu {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100%;
            background-color: #333;
            padding-top: 60px;
        }
        .menu.open {
            display: block;
        }
        .menu a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }
        .menu a:hover {
            background-color: #ddd;
            color: black;
        }
        .menu-close {
            position: absolute;
            top: 0;
            right: 25px;
            font-size: 36px;
            color: white;
            cursor: pointer;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        .view-button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="menu-container">
    <button class="menu-icon" onclick="toggleMenu()">&#9776; Meniu</button>
    <div id="menu" class="menu">
        <span class="menu-close" onclick="toggleMenu()">&times;</span>
        <a href="index.php">Acasă</a>
        <a href="restaurant_details.php?id=23">Restaurantul Recomandat</a>
        <a href="meniu.php">Meniu</a>
        <a href="contact.php">Contact</a>
        <a href="about.php">Despre Noi</a>
    </div>
</div>

<div class="container">
    <h2>Restaurante Disponibile</h2>

    <!-- Secțiunea de reclamă pentru restaurantul specific -->
    <?php if ($adRestaurant): ?>
    <div class="ad-banner">
        <h2>Recomandarea Noastră: <?php echo htmlspecialchars($adRestaurant['name']); ?></h2>
        <p><?php echo htmlspecialchars($adRestaurant['description']); ?></p>
        <img src="../<?php echo htmlspecialchars($adRestaurant['image_path']); ?>" alt="<?php echo htmlspecialchars($adRestaurant['name']); ?>">
        <a href="/restaurant_menus/menu_<?php echo htmlspecialchars($adRestaurant['id']); ?>.html">Vezi Detalii</a>
    </div>
    <?php else: ?>
    <p>Nu s-a găsit restaurantul selectat pentru reclamă.</p>
    <?php endif; ?>

    <!-- Secțiunea existentă de afișare a restaurantelor -->
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $open_time = isset($row['opening_hours_from']) ? $row['opening_hours_from'] : 'N/A';
            $close_time = isset($row['opening_hours_to']) ? $row['opening_hours_to'] : 'N/A';
            $image_path = isset($row['image_path']) ? $row['image_path'] : '';

            $full_image_path = $image_path;

            $current_time = date('H:i');
            $is_open = ($current_time >= $open_time && $current_time <= $close_time);

            echo '<div class="restaurant-card">';
            echo '<div class="restaurant-image"><img src="../' . htmlspecialchars($full_image_path) . '" alt="' . htmlspecialchars($row['name']) . '"></div>';
            echo '<div class="restaurant-details">';
            echo '<h3>' . htmlspecialchars($row['name']) . '</h3>';
            echo '<p>' . htmlspecialchars($row['description']) . '</p>';
            echo '<div class="restaurant-hours">';
            if ($is_open && $open_time !== 'N/A' && $close_time !== 'N/A') {
                echo 'Deschis până la ' . htmlspecialchars($close_time);
            } else {
                echo '<span class="closed">Închis</span>';
            }
            echo '</div>';
            echo '<div class="button-container">';
            echo '<a href="restaurant_details.php?id=' . htmlspecialchars($row['id']) . '" class="view-button">Vezi Detalii</a>';
            echo '<a href="../restaurant_menus/menu_' . htmlspecialchars($row['id']) . '.html" class="view-button">Vezi Meniuri</a>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo '<p>Nu există restaurante disponibile.</p>';
    }

    $mysqli->close();
    ?>
</div>

<script>
    function toggleMenu() {
        var menu = document.getElementById('menu');
        if (menu.classList.contains('open')) {
            menu.classList.remove('open');
        } else {
            menu.classList.add('open');
        }
    }
</script>
</body>
</html>
