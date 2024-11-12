<?php
require_once __DIR__ . '/config.php';

// Șterge sesiunile inactive de mai mult de 5 minute
$stmt = $conn->prepare("DELETE FROM user_sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
$stmt->execute();

$affected_rows = $stmt->affected_rows;
echo "Sesiuni expirate șterse: $affected_rows";

$stmt->close();
$conn->close();
