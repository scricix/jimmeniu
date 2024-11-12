<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $lastName = $data['lastName'] ?? '';
    $firstName = $data['firstName'] ?? '';
    $email = $data['email'] ?? null;
    $phone = $data['phone'] ?? '';
    $address = $data['address'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($lastName) || empty($firstName) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Toate câmpurile obligatorii trebuie completate.']);
        exit;
    }

    // Verifică dacă numărul de telefon există deja
    $stmt = $conn->prepare("SELECT id FROM users WHERE phone = ?");
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Acest număr de telefon este deja înregistrat.']);
        exit;
    }

    // Hash-uiește parola
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Inserează noul utilizator
    $stmt = $conn->prepare("INSERT INTO users (last_name, first_name, email, phone, address, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $lastName, $firstName, $email, $phone, $address, $hashedPassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Înregistrare reușită!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Eroare la înregistrare: ' . $conn->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Metodă de cerere invalidă.']);
}
