<?php
session_start();
require_once "config.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'În Procesare' AND deliverer_id IS NULL";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

echo json_encode(["newOrders" => $row['count']]);
?>
