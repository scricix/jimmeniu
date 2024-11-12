<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $session_token = $data['sessionToken'] ?? '';
    $phone = $data['phone'] ?? '';

    if (empty($session_token) || empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Token de sesiune sau număr de telefon lipsă.']);
        exit;
    }

    // Marchează sesiunea ca offline
    $stmt = $conn->prepare("UPDATE user_sessions SET is_online = 0 WHERE session_token = ? AND phone = ?");
    $stmt->bind_param("ss", $session_token, $phone);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Deconectare reușită.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sesiune invalidă sau deja deconectată.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Metodă de cerere invalidă.']);
}
