<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    $stmt = $pdo->prepare("SELECT status FROM orders WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        echo json_encode(['order_status' => $result['order_status']]);
    } else {
        echo json_encode(['error' => 'Comanda nu a fost găsită']);
    }
} else {
    echo json_encode(['error' => 'ID-ul comenzii lipsește']);
}
