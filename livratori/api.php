<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require_once "config.php";

function response($status, $message, $data = null) {
    header("Content-Type: application/json");
    echo json_encode([
        "status" => $status,
        "message" => $message,
        "data" => $data,
        "debug" => error_get_last()
    ]);
    exit;
}

set_exception_handler(function($e) {
    response(false, "Eroare internă: " . $e->getMessage(), null);
});

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
   
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'login':
                $name = $data['name'];
                $password = $data['password'];
               
                $sql = "SELECT id, name, password FROM deliverers WHERE name = ? AND status = 'active'";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $name);
                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_store_result($stmt);
                        if (mysqli_stmt_num_rows($stmt) == 1) {
                            mysqli_stmt_bind_result($stmt, $id, $name, $hashed_password);
                            if (mysqli_stmt_fetch($stmt)) {
                                if (password_verify($password, $hashed_password)) {
                                    $_SESSION["loggedin"] = true;
                                    $_SESSION["deliverer_id"] = $id;
                                    $_SESSION["deliverer_name"] = $name;
                                    response(true, "Autentificare reușită", ['delivererId' => $id, 'delivererName' => $name]);
                                } else {
                                    response(false, "Parolă incorectă");
                                }
                            }
                        } else {
                            response(false, "Livrator inexistent sau inactiv");
                        }
                    } else {
                        response(false, "Oops! Ceva nu a mers bine. Încercați din nou mai târziu.");
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'logout':
                session_destroy();
                response(true, "Deconectare reușită");
                break;

            case 'getOrders':
                if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                    response(false, "Acces neautorizat");
                }
                $sortBy = isset($data['sortBy']) ? $data['sortBy'] : 'created_at';
                $allowedSortFields = ['created_at', 'customer_name', 'status'];
                if (!in_array($sortBy, $allowedSortFields)) {
                    $sortBy = 'created_at';
                }
                $sql = "SELECT * FROM orders ORDER BY $sortBy DESC";
                $result = mysqli_query($conn, $sql);
                $orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
                
                foreach ($orders as &$order) {
                    $order['clicked_statuses'] = $order['clicked_statuses'] ? explode(',', $order['clicked_statuses']) : [];
                }
                
                response(true, "Comenzi preluate cu succes", $orders);
                break;

            case 'acceptaComanda':
                if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                    response(false, "Acces neautorizat");
                }
                $orderId = $data['id'];
                $delivererId = $_SESSION['deliverer_id'];
               
                $checkSql = "SELECT deliverer_id FROM orders WHERE id = ? AND deliverer_id IS NULL";
                if ($stmt = mysqli_prepare($conn, $checkSql)) {
                    mysqli_stmt_bind_param($stmt, "i", $orderId);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_store_result($stmt);
                    if (mysqli_stmt_num_rows($stmt) == 0) {
                        response(false, "Comanda a fost deja acceptată de alt livrator");
                    }
                    mysqli_stmt_close($stmt);
                }

                $sql = "UPDATE orders SET deliverer_id = ?, status = 'Acceptată', clicked_statuses = 'Acceptată' WHERE id = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ii", $delivererId, $orderId);
                    if (mysqli_stmt_execute($stmt)) {
                        response(true, "Comanda a fost acceptată cu succes");
                    } else {
                        response(false, "Eroare la acceptarea comenzii");
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'updateOrderStatus':
                if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                    response(false, "Acces neautorizat");
                }
                $orderId = $data['id'];
                $status = $data['status'];
                $delivererId = $_SESSION['deliverer_id'];

                $checkSql = "SELECT deliverer_id, clicked_statuses FROM orders WHERE id = ? AND deliverer_id = ?";
                if ($stmt = mysqli_prepare($conn, $checkSql)) {
                    mysqli_stmt_bind_param($stmt, "ii", $orderId, $delivererId);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $order = mysqli_fetch_assoc($result);
                    if (!$order) {
                        response(false, "Nu aveți permisiunea de a actualiza această comandă");
                    }
                    $clickedStatuses = $order['clicked_statuses'] ? explode(',', $order['clicked_statuses']) : [];
                    if (!in_array($status, $clickedStatuses)) {
                        $clickedStatuses[] = $status;
                    }
                    $newClickedStatuses = implode(',', $clickedStatuses);
                    mysqli_stmt_close($stmt);
                }

                $sql = "UPDATE orders SET status = ?, clicked_statuses = ? WHERE id = ? AND deliverer_id = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssii", $status, $newClickedStatuses, $orderId, $delivererId);
                    if (mysqli_stmt_execute($stmt)) {
                        response(true, "Status actualizat cu succes");
                    } else {
                        response(false, "Eroare la actualizarea statusului");
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'trimitebon':
                if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
                    response(false, "Acces neautorizat");
                }
                $orderId = $data['id'];
                $delivererId = $_SESSION['deliverer_id'];

                $checkSql = "SELECT o.*, d.name as deliverer_name FROM orders o JOIN deliverers d ON o.deliverer_id = d.id WHERE o.id = ? AND o.deliverer_id = ?";
                if ($stmt = mysqli_prepare($conn, $checkSql)) {
                    mysqli_stmt_bind_param($stmt, "ii", $orderId, $delivererId);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($order = mysqli_fetch_assoc($result)) {
                        $to = $order['customer_email'];
                        $subject = "Bon fiscal pentru comanda #" . $order['order_id'];
                        $message = "Vă mulțumim pentru comanda dumneavoastră!\n\n";
                        $message .= "Detalii comandă:\n";
                        $message .= "Produse: " . $order['products'] . "\n";
                        $message .= "Total: " . $order['total'] . " RON\n";
                        $message .= "Livrator: " . $order['deliverer_name'] . "\n";
                        $headers = "From: noreply@yourcompany.com";

                        if (mail($to, $subject, $message, $headers)) {
                            response(true, "Bonul a fost trimis cu succes");
                        } else {
                            response(false, "Eroare la trimiterea bonului");
                        }
                    } else {
                        response(false, "Nu aveți permisiunea de a trimite bonul pentru această comandă");
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            default:
                response(false, "Acțiune nerecunoscută");
        }
    } else {
        response(false, "Acțiune nespecificată");
    }
} else {
    response(false, "Metodă de cerere invalidă");
}

mysqli_close($conn);
?>
