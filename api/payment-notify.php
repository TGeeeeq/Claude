<?php
/**
 * Payment Gateway Notification Handler
 * This endpoint receives payment status updates from the payment gateway
 */

require_once '../config/database.php';
require_once '../config/payment.php';

// Log all incoming data for debugging
$logFile = __DIR__ . '/../logs/payment-notifications.log';
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'get' => $_GET,
    'post' => $_POST,
    'body' => file_get_contents('php://input')
];
@file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND);

try {
    $pdo = getDbConnection();
    $gateway = initPaymentGateway();
    
    switch ($gateway['type']) {
        case 'gopay':
            handleGoPayNotification($pdo);
            break;
            
        case 'stripe':
            handleStripeWebhook($pdo);
            break;
            
        case 'comgate':
            handleComGateNotification($pdo);
            break;
    }
    
} catch (Exception $e) {
    error_log("Payment notification error: " . $e->getMessage());
    http_response_code(500);
}

function handleGoPayNotification($pdo) {
    // GoPay sends payment ID in POST
    $paymentId = $_POST['id'] ?? null;
    
    if (!$paymentId) {
        http_response_code(400);
        exit;
    }
    
    // Verify payment status with GoPay API
    // Update order status based on payment status
    
    http_response_code(200);
    echo "OK";
}

function handleStripeWebhook($pdo) {
    $payload = file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
    
    // Verify webhook signature and process event
    // Update order status based on payment event
    
    http_response_code(200);
}

function handleComGateNotification($pdo) {
    $transId = $_POST['transId'] ?? $_GET['transId'] ?? null;
    $status = $_POST['status'] ?? $_GET['status'] ?? null;
    
    if (!$transId || !$status) {
        http_response_code(400);
        exit;
    }
    
    // Find order by transaction ID
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
    $stmt->execute([$_POST['refId'] ?? '']);
    $order = $stmt->fetch();
    
    if ($order) {
        // Update payment status
        if ($status === 'PAID') {
            $pdo->prepare("UPDATE orders SET payment_status = 'completed', status = 'paid' WHERE id = ?")
                ->execute([$order['id']]);
        } else if ($status === 'CANCELLED') {
            $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")
                ->execute([$order['id']]);
        }
    }
    
    http_response_code(200);
    echo "OK";
}
?>
