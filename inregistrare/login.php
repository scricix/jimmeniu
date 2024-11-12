<?php
require_once __DIR__ . '/config.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $name = $data['name'] ?? '';
    $phone = $data['phone'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($name) || empty($phone) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Toate câmpurile sunt obligatorii.']);
        exit;
    }

    // Caută utilizatorul după nume și număr de telefon
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, password FROM users WHERE (last_name = ? OR first_name = ?) AND phone = ?");
    $stmt->bind_param("sss", $name, $name, $phone);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $session_token = bin2hex(random_bytes(32));
            $full_name = $user['first_name'] . ' ' . $user['last_name'];
            
            // Inserează sau actualizează sesiunea utilizatorului
            $stmt = $conn->prepare("INSERT INTO user_sessions (name, email, phone, session_token) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE session_token = VALUES(session_token), is_online = 1, last_activity = CURRENT_TIMESTAMP");
            $stmt->bind_param("ssss", $full_name, $user['email'], $user['phone'], $session_token);
            $stmt->execute();

            echo json_encode([
                'success' => true, 
                'message' => 'Autentificare reușită!', 
                'name' => $full_name,
                'email' => $user['email'],
                'phone' => $user['phone'],
                'sessionToken' => $session_token
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Parolă incorectă.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Utilizator negăsit.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Metodă de cerere invalidă.']);
}
