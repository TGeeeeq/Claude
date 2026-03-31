<?php
/**
 * Create Payment API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/payment.php';

$orderId = $_POST['order_id'] ?? $_GET['order_id'] ?? null;

if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'Missing order ID']);
    exit;
}

try {
    $pdo = getDbConnection();

    // Validate order exists
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([(int)$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }

    // Get order items
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([(int)$orderId]);
    $items = $stmt->fetchAll();

    $orderData = [
        'order_number' => $order['order_number'],
        'customer_name' => $order['customer_name'],
        'customer_email' => $order['customer_email'],
        'customer_phone' => $order['customer_phone'],
        'total_amount' => (float)$order['total_amount'],
        'items' => $items
    ];

    $payment = createPayment($orderData);

    if ($payment['success']) {
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'pending' WHERE id = ?");
        $stmt->execute([(int)$orderId]);
    }

    echo json_encode($payment);

} catch (Exception $e) {
    error_log("Payment creation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Payment creation failed']);
}
?>