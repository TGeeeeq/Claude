<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';
require_once '../config/payment.php';

$orderId = $_POST['order_id'] ?? $_GET['order_id'] ?? null;

if (!$orderId) {
    echo json_encode(['success' => false, 'error' => 'Missing order ID']);
    exit;
}

try {
    $pdo = getDbConnection();
    
    // Get order details
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'error' => 'Order not found']);
        exit;
    }
    
    // Get order items
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $items = $stmt->fetchAll();
    
    $orderData = [
        'order_number' => $order['order_number'],
        'customer_name' => $order['customer_name'],
        'customer_email' => $order['customer_email'],
        'customer_phone' => $order['customer_phone'],
        'total_amount' => $order['total_amount'],
        'items' => $items
    ];
    
    // Create payment
    $payment = createPayment($orderData);
    
    if ($payment['success']) {
        // Update order with payment info
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET payment_status = 'pending' 
            WHERE id = ?
        ");
        $stmt->execute([$orderId]);
        
        echo json_encode($payment);
    } else {
        echo json_encode($payment);
    }
    
} catch (Exception $e) {
    error_log("Payment creation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Payment creation failed']);
}
?>
