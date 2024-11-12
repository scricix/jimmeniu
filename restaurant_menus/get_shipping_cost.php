<?php
// get_shipping_cost.php
header('Content-Type: application/json');

// Aici puteți obține prețul transportului din baza de date sau dintr-un fișier de configurare
$shippingCost = 20; // Exemplu: preț fix

echo json_encode(['shippingCost' => $shippingCost]);
