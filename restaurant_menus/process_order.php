<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'generate_order_confirmation.php';
require_once 'config.php'; // Asigurați-vă că aveți acest fișier cu conexiunea la baza de date

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = uniqid();
    $orderData = json_decode(file_get_contents('php://input'), true);
   
    $orderDetails = [
        'customerName' => $orderData['name'] ?? 'N/A',
        'customerEmail' => $orderData['email'] ?? 'N/A',
        'customerPhone' => $orderData['phone'] ?? 'N/A',
        'deliveryAddress' => $orderData['address'] ?? 'N/A',
        'products' => $orderData['products'] ?? [],
        'subtotal' => floatval($orderData['subtotal'] ?? 0),
        'shippingCost' => floatval($orderData['shippingCost'] ?? 0),
        'total' => floatval($orderData['total'] ?? 0)
    ];

    // Funcție pentru a formata produsele într-un string
    function formatProductsForDatabase($products) {
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = "{$product['name']} - {$product['price']} lei x {$product['quantity']} ({$product['restaurantName']})";
        }
        return implode('; ', $formattedProducts);
    }

    // Salvarea comenzii în baza de date
    $stmt = $pdo->prepare("INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, delivery_address, products, subtotal, shipping_cost, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $orderId,
        $orderDetails['customerName'],
        $orderDetails['customerEmail'],
        $orderDetails['customerPhone'],
        $orderDetails['deliveryAddress'],
        formatProductsForDatabase($orderDetails['products']),
        $orderDetails['subtotal'],
        $orderDetails['shippingCost'],
        $orderDetails['total']
    ]);

    $confirmationContent = generateOrderConfirmation($orderId, $orderDetails);
    
    $confirmationFileName = "confirmation_" . $orderId . ".html";
    if (file_put_contents($confirmationFileName, $confirmationContent) === false) {
        echo json_encode(['error' => 'Eroare la salvarea confirmării']);
        exit;
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'confirmationUrl' => $confirmationFileName]);
} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Metoda nu este permisă']);
}
?>
