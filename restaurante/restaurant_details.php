<?php
// Conectare la baza de date
$mysqli = new mysqli("localhost", "root", "", "magazin");

// Verifică conexiunea
if ($mysqli->connect_error) {
    die("Conexiunea a eșuat: " . $mysqli->connect_error);
}

// Obținerea detaliilor restaurantului
$restaurant_id = $_GET['id'];
$query = "SELECT * FROM restaurants WHERE id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$restaurant = $stmt->get_result()->fetch_assoc();

// Adăugarea recenzilor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $comment = $_POST['comment'];
    $rating = $_POST['rating'];

    $stmt = $mysqli->prepare("INSERT INTO reviews (restaurant_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $restaurant_id, $rating, $comment);
    $stmt->execute();
}

// Obținerea recenziilor
$query = "SELECT * FROM reviews WHERE restaurant_id = ? ORDER BY id DESC";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("i", $restaurant_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Calea imaginii fixe
$fixed_image_path = 'galeriefotorestaurant1/caine.png'; // Calea completă către imaginea specifică
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalii Restaurant</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .container {
            margin-top: 20px;
            padding: 20px;
        }
        .restaurant-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
            background-color: #fff;
            transition: background-color 0.3s ease;
        }
        .restaurant-card:hover {
            background-color: #f9f9f9;
        }
        .restaurant-image img {
            width: 10%;
            height: auto;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .restaurant-details h2 {
            margin-top: 0;
        }
        .restaurant-info p {
            margin: 5px 0;
        }
        .restaurant-description {
            margin-top: 15px;
        }
        .review-form, .feedback-form {
            margin-top: 20px;
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            border: 1px solid #ddd;
        }
        .review-form textarea, .review-form select, .review-form button,
        .feedback-form textarea, .feedback-form button {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .review-form button, .feedback-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .review-form button:hover, .feedback-form button:hover {
            background-color: #45a049;
        }
        .feedback-form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .feedback-form textarea {
            margin-bottom: 10px;
        }
        .reviews-section {
            margin-top: 20px;
        }
        .review {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #fff;
        }
        .rating {
            color: #ff9800;
        }
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .gallery img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
        }
        #feedbackMessages {
            margin-top: 20px;
        }
        .feedback {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
            margin-bottom: 10px;
        }
        .feedback-message {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            background-color: #fff;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container">
    <?php if ($restaurant): ?>
        <div class="restaurant-card">
            <div class="restaurant-image">
                <img src="../<?php echo '' . htmlspecialchars($restaurant['image_path']); ?>" alt="<?php echo htmlspecialchars($restaurant['name']); ?>">
            </div>
            <div class="restaurant-details">
                <h2><?php echo htmlspecialchars($restaurant['name']); ?></h2>

                <!-- Afișează informațiile doar dacă există -->
                <div class="restaurant-description">
                    <h3>Descriere</h3>
                    <p>
                        Acesta este un restaurant de top, cunoscut pentru bucătăria sa rafinată și serviciile excelente. Cu un meniu variat, ce include preparate tradiționale și internaționale, restaurantul oferă o experiență culinară de neuitat. Ambianța elegantă și atenția la detalii fac din fiecare vizită o ocazie specială.
                    </p>
                </div>
            </div>
        </div>

        <!-- Galerie foto -->
        <div class="gallery">
            <!-- Afișare imagine fixă -->
            <img src="<?php echo htmlspecialchars($fixed_image_path); ?>" alt="Restaurant Image">
        </div>

        <!-- Formular pentru recenzie și feedback -->
        <div style="display: flex; flex-direction: column;">
            <div class="review-form">
                <h3>Adaugă o Recenzie</h3>
                <form method="POST" action="">
                    <textarea name="comment" placeholder="Scrie o recenzie..." required></textarea>
                    <select name="rating" required>
                        <option value="1">1 Stea</option>
                        <option value="2">2 Stele</option>
                        <option value="3">3 Stele</option>
                        <option value="4">4 Stele</option>
                        <option value="5">5 Stele</option>
                    </select>
                    <button type="submit">Trimite Recenzia</button>
                </form>
            </div>

            <div class="feedback-form">
                <h3>Adaugă un Feedback</h3>
                <form id="feedbackForm">
                    <textarea id="feedbackMessage" placeholder="Scrie un mesaj..." required></textarea>
                    <button type="button" id="sendFeedback">Trimite Feedback</button>
                </form>
            </div>
        </div>

        <!-- Afișare recenzii -->
        <div class="reviews-section">
            <h3>Recenzii</h3>
            <?php while ($row = $reviews->fetch_assoc()): ?>
                <div class="review">
                    <p><?php echo htmlspecialchars($row['comment']); ?></p>
                    <p>Rating: <span class="rating"><?php echo str_repeat('⭐', $row['rating']); ?></span> (<?php echo htmlspecialchars($row['rating']); ?> Stele)</p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>Restaurantul nu a fost găsit.</p>
    <?php endif; ?>

    <!-- Afișare feedback -->
    <div id="feedbackMessages">
        <!-- Feedback-ul va fi încărcat aici -->
    </div>
</div>

<script>
$(document).ready(function() {
    var restaurantName = <?php echo json_encode(htmlspecialchars($restaurant['name'])); ?>; // Obține numele restaurantului din PHP

    // Funcție pentru a obține feedback-ul
    function loadFeedback() {
        $.ajax({
            url: 'fetch_feedback.php',
            method: 'GET',
            data: { name: restaurantName }, // Trimit numele restaurantului
            success: function(data) {
                $('#feedbackMessages').html(data);
            }
        });
    }

    // Încarcă feedback-ul la încărcarea paginii
    loadFeedback();

    // Trimite feedback-ul
    $('#sendFeedback').click(function() {
        var message = $('#feedbackMessage').val();
        if (message) {
            $.ajax({
                url: 'submit_feedback.php',
                method: 'POST',
                data: { name: restaurantName, message: message }, // Trimit numele restaurantului
                success: function() {
                    $('#feedbackMessage').val(''); // Resetează câmpul text
                    loadFeedback(); // Reîncarcă feedback-ul
                }
            });
        } else {
            alert('Te rog să scrii un mesaj.');
        }
    });
});
</script>

</body>
</html>

<?php
$stmt->close();
$mysqli->close();
?>
