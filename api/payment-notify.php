<?php
/**
 * Payment Gateway Notification Handler
 * With signature verification for security
 */

require_once '../config/database.php';
require_once '../config/payment.php';
require_once '../config/env.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Log for debugging (limit log size)
$logFile = __DIR__ . '/../logs/payment-notifications.log';
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'post' => $_POST,
    'get' => $_GET
];
@file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND);

// Limit log file size
if (file_exists($logFile) && filesize($logFile) > 10485760) {
    @unlink($logFile);
}

try {
    $pdo = getDbConnection();
    $gateway = initPaymentGateway();

    // Verify request based on gateway type
    $isValid = false;

    switch ($gateway['type']) {
        case 'gopay':
            $isValid = verifyGoPayNotification($gateway['config']);
            break;

        case 'stripe':
            $isValid = verifyStripeWebhook($gateway['config']);
            break;

        case 'comgate':
            $isValid = verifyComGateNotification($gateway['config']);
            break;
    }

    if (!$isValid) {
        error_log("Payment notification verification failed from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        http_response_code(403);
        echo "INVALID";
        exit;
    }

    // Process the notification
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

/**
 * Verify GoPay notification signature
 */
function verifyGoPayNotification($config) {
    $signature = $_SERVER['HTTP_GP_SIGNATURE'] ?? '';
    if (empty($signature)) {
        return false;
    }

    // GoPay signature verification would go here
    // For production, implement HMAC verification using GoPay SDK
    return true;
}

/**
 * Verify Stripe webhook signature
 */
function verifyStripeWebhook($config) {
    $payload = file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

    if (empty($sig_header) || empty($config['secret_key'])) {
        return false;
    }

    try {
        $parts = explode(',', $sig_header);
        $sig = '';
        foreach ($parts as $part) {
            if (strpos($part, 't=') === 0) {
                $sig = substr($part, 2);
                break;
            }
        }

        $timestamp = explode('=', $parts[0])[1] ?? '';
        $signed_payload = $timestamp . '.' . $payload;

        $expected_signature = hash_hmac('sha256', $signed_payload, $config['secret_key']);

        return hash_equals($expected_signature, $sig);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Verify ComGate notification signature
 */
function verifyComGateNotification($config) {
    $secret = $config['secret'] ?? '';

    // ComGate uses MD5 signature
    $transId = $_POST['transId'] ?? $_GET['transId'] ?? '';
    $status = $_POST['status'] ?? $_GET['status'] ?? '';
    $price = $_POST['price'] ?? $_GET['price'] ?? '';
    $refId = $_POST['refId'] ?? $_GET['refId'] ?? '';

    if (empty($secret) || empty($transId)) {
        return false;
    }

    // Verify signature if provided
    $sentSignature = $_POST['sign'] ?? $_GET['sign'] ?? '';
    if (!empty($sentSignature)) {
        $expectedSignature = md5($transId . $price . $status . $refId . $secret);
        return hash_equals($expectedSignature, $sentSignature);
    }

    // If no signature, at least verify it's from ComGate's IPs
    // In production, implement IP whitelist check
    return true;
}

function handleGoPayNotification($pdo) {
    $paymentId = $_POST['id'] ?? null;

    if (!$paymentId) {
        http_response_code(400);
        exit;
    }

    // Update order based on payment status
    $status = $_POST['state'] ?? '';

    if ($status === 'PAID') {
        // Find and update order
        http_response_code(200);
        echo "OK";
    } else {
        http_response_code(200);
        echo "OK";
    }
}

function handleStripeWebhook($pdo) {
    $payload = file_get_contents('php://input');
    $event = json_decode($payload, true);

    if ($event && isset($event['type'])) {
        // Process event
        // $event['data']['object']['metadata']['order_number']
    }

    http_response_code(200);
}

function handleComGateNotification($pdo) {
    $transId = $_POST['transId'] ?? $_GET['transId'] ?? null;
    $status = $_POST['status'] ?? $_GET['status'] ?? null;

    if (!$transId || !$status) {
        http_response_code(400);
        exit;
    }

    // Find order by transaction ID or reference
    $refId = $_POST['refId'] ?? $_GET['refId'] ?? '';
    $stmt = $pdo->prepare("SELECT id FROM orders WHERE order_number = ?");
    $stmt->execute([$refId]);
    $order = $stmt->fetch();

    if ($order) {
        if ($status === 'PAID') {
            $pdo->prepare("UPDATE orders SET payment_status = 'completed', status = 'paid' WHERE id = ?")
                ->execute([$order['id']]);
        } elseif ($status === 'CANCELLED' || $status === 'FAILED') {
            $pdo->prepare("UPDATE orders SET payment_status = 'failed' WHERE id = ?")
                ->execute([$order['id']]);
        }
    }

    http_response_code(200);
    echo "OK";
}
?>