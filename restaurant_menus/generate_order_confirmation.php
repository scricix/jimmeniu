<?php

function generateOrderConfirmation($orderId, $orderDetails) {
    $content = "<!DOCTYPE html>\n<html lang=\"ro\">\n<head>\n";
    $content .= "    <meta charset=\"UTF-8\">\n";
    $content .= "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    $content .= "    <title>Confirmare Comandă #" . htmlspecialchars($orderId) . "</title>\n";
    $content .= "    <style>\n";
    $content .= "        :root { --primary: #4CAF50; --secondary: #2196F3; --accent: #FF9800; --background: #F5F5F5; --text: #333; }\n";
    $content .= "        body { font-family: 'Roboto', Arial, sans-serif; line-height: 1.6; color: var(--text); background-color: var(--background); margin: 0; padding: 20px; }\n";
    $content .= "        .container { max-width: 800px; margin: 0 auto; background-color: #fff; border-radius: 15px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); overflow: hidden; }\n";
    $content .= "        h1, h2 { color: var(--primary); margin-top: 0; }\n";
    $content .= "        .order-details, .customer-info { background-color: #fff; border: 1px solid var(--secondary); padding: 20px; margin-top: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }\n";
    $content .= "        .order-item { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid var(--secondary); }\n";
    $content .= "        .order-total { font-weight: bold; margin-top: 20px; padding-top: 10px; border-top: 2px solid var(--primary); }\n";
    $content .= "        .order-status { position: sticky; top: 20px; margin-top: 20px; background-color: var(--accent); color: #fff; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: all 0.3s ease; }\n";
    $content .= "        .order-status:hover { transform: translateY(-5px); box-shadow: 0 6px 12px rgba(0,0,0,0.15); }\n";
    $content .= "        .status-icon { font-size: 24px; margin-right: 10px; }\n";
    $content .= "        #statusText { font-size: 1.2em; font-weight: bold; }\n";
    $content .= "        .highlight { color: var(--secondary); font-weight: bold; }\n";
    $content .= "        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }\n";
    $content .= "        .pulse { animation: pulse 2s infinite; }\n";
    $content .= "        @media (max-width: 600px) { body { padding: 10px; } .container { border-radius: 0; } }\n";
    $content .= "    </style>\n";
    $content .= "</head>\n<body>\n";
    $content .= "<div class=\"container\">\n";
    $content .= "    <h1>Comandă Confirmată</h1>\n";
    $content .= "    <p>Comanda dumneavoastră cu numărul <span class=\"highlight\">#" . htmlspecialchars($orderId) . "</span> a fost plasată cu succes.</p>\n";
    
    $content .= "    <div class=\"order-status\" id=\"orderStatus\">\n";
    $content .= "        <h2><span class=\"status-icon\">📦</span>Status Comandă: <span id=\"statusText\" class=\"pulse\">În Procesare</span></h2>\n";
    $content .= "        <p id=\"statusMessage\">Vă vom informa prin email când comanda va fi expediată.</p>\n";
    $content .= "    </div>\n";

    $content .= "    <div class=\"customer-info\">\n";
    $content .= "        <h2>Informații Client</h2>\n";
    $content .= "        <p><strong>Nume:</strong> " . htmlspecialchars($orderDetails['customerName']) . "</p>\n";
    $content .= "        <p><strong>Email:</strong> " . htmlspecialchars($orderDetails['customerEmail']) . "</p>\n";
    $content .= "        <p><strong>Telefon:</strong> " . htmlspecialchars($orderDetails['customerPhone']) . "</p>\n";
    $content .= "        <p><strong>Adresă livrare:</strong> " . htmlspecialchars($orderDetails['deliveryAddress']) . "</p>\n";
    $content .= "    </div>\n";

    $content .= "    <div class=\"order-details\">\n";
    $content .= "        <h2>Detalii Comandă</h2>\n";

    foreach ($orderDetails['products'] as $product) {
        $content .= "        <div class=\"order-item\">\n";
        $content .= "            <span>" . htmlspecialchars($product['name']) . " (x" . $product['quantity'] . ")</span>\n";
        $content .= "            <span>" . number_format($product['price'] * $product['quantity'], 2) . " RON</span>\n";
        $content .= "        </div>\n";
    }

    $content .= "        <div class=\"order-total\">\n";
    $content .= "            <p>Subtotal: <span class=\"highlight\">" . number_format($orderDetails['subtotal'], 2) . " RON</span></p>\n";
    $content .= "            <p>Cost livrare: <span class=\"highlight\">" . number_format($orderDetails['shippingCost'], 2) . " RON</span></p>\n";
    $content .= "            <p>Total: <span class=\"highlight\">" . number_format($orderDetails['total'], 2) . " RON</span></p>\n";
    $content .= "        </div>\n";
    $content .= "    </div>\n";

    $content .= "</div>\n";
    
    $content .= "<script>\n";
    $content .= "function updateOrderStatus() {\n";
    $content .= "    fetch('get_order_status.php?order_id=" . $orderId . "')\n";
    $content .= "        .then(response => response.json())\n";
    $content .= "        .then(data => {\n";
    $content .= "            if (data.status) {\n";
    $content .= "                document.getElementById('statusText').textContent = data.status;\n";
    $content .= "                document.getElementById('statusText').classList.add('pulse');\n";
    $content .= "                setTimeout(() => document.getElementById('statusText').classList.remove('pulse'), 3000);\n";
    $content .= "                if (data.status === 'Expediată') {\n";
    $content .= "                    document.getElementById('statusMessage').textContent = 'Comanda dumneavoastră a fost expediată.';\n";
    $content .= "                    document.querySelector('.status-icon').textContent = '🚚';\n";
    $content .= "                }\n";
    $content .= "            }\n";
    $content .= "        })\n";
    $content .= "        .catch(error => console.error('Eroare:', error));\n";
    $content .= "}\n";
    $content .= "setInterval(updateOrderStatus, 60000);\n";
    $content .= "updateOrderStatus();\n";
    $content .= "</script>\n";
    
    $content .= "</body>\n</html>";

    return $content;
}
