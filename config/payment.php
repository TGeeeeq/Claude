<?php
/**
 * Payment Gateway Configuration
 * Loaded from environment variables for security
 */

require_once __DIR__ . '/env.php';

// Payment gateway selection
define('PAYMENT_GATEWAY', env('PAYMENT_GATEWAY', 'gopay'));

// GoPay Configuration
define('GOPAY_GOID', env('GOPAY_GOID', ''));
define('GOPAY_CLIENT_ID', env('GOPAY_CLIENT_ID', ''));
define('GOPAY_CLIENT_SECRET', env('GOPAY_CLIENT_SECRET', ''));
define('GOPAY_TEST_MODE', env('GOPAY_TEST_MODE', 'true') === 'true');

// Stripe Configuration
define('STRIPE_PUBLIC_KEY', env('STRIPE_PUBLIC_KEY', ''));
define('STRIPE_SECRET_KEY', env('STRIPE_SECRET_KEY', ''));

// ComGate Configuration
define('COMGATE_MERCHANT_ID', env('COMGATE_MERCHANT_ID', ''));
define('COMGATE_SECRET', env('COMGATE_SECRET', ''));
define('COMGATE_TEST_MODE', env('COMGATE_TEST_MODE', 'true') === 'true');

// PayPal Configuration
define('PAYPAL_CLIENT_ID', env('PAYPAL_CLIENT_ID', ''));
define('PAYPAL_SECRET', env('PAYPAL_SECRET', ''));
define('PAYPAL_MODE', env('PAYPAL_MODE', 'sandbox'));

// Payment return URLs
define('PAYMENT_RETURN_URL', 'https://nechmerust.org/obchod/payment-return.php');
define('PAYMENT_NOTIFY_URL', 'https://nechmerust.org/api/payment-notify.php');

/**
 * Initialize payment gateway
 */
function initPaymentGateway() {
    switch (PAYMENT_GATEWAY) {
        case 'gopay':
            return [
                'type' => 'gopay',
                'config' => [
                    'goid' => GOPAY_GOID,
                    'clientId' => GOPAY_CLIENT_ID,
                    'clientSecret' => GOPAY_CLIENT_SECRET,
                    'isProductionMode' => !GOPAY_TEST_MODE
                ]
            ];

        case 'stripe':
            return [
                'type' => 'stripe',
                'config' => [
                    'public_key' => STRIPE_PUBLIC_KEY,
                    'secret_key' => STRIPE_SECRET_KEY
                ]
            ];

        case 'comgate':
            return [
                'type' => 'comgate',
                'config' => [
                    'merchant_id' => COMGATE_MERCHANT_ID,
                    'secret' => COMGATE_SECRET,
                    'test' => COMGATE_TEST_MODE
                ]
            ];

        default:
            return null;
    }
}

/**
 * Create payment
 */
function createPayment($orderData) {
    $gateway = initPaymentGateway();

    if (!$gateway) {
        return ['success' => false, 'error' => 'Payment gateway not configured'];
    }

    switch ($gateway['type']) {
        case 'gopay':
            return createGoPayPayment($orderData, $gateway['config']);

        case 'stripe':
            return createStripePayment($orderData, $gateway['config']);

        case 'comgate':
            return createComGatePayment($orderData, $gateway['config']);

        default:
            return ['success' => false, 'error' => 'Unsupported payment gateway'];
    }
}

/**
 * GoPay payment creation
 */
function createGoPayPayment($orderData, $config) {
    if (empty($config['goid']) || empty($config['clientSecret'])) {
        return ['success' => false, 'error' => 'GoPay not configured'];
    }

    // Implementation would use GoPay SDK
    return [
        'success' => false,
        'error' => 'GoPay SDK integration pending'
    ];
}

/**
 * Stripe payment creation
 */
function createStripePayment($orderData, $config) {
    if (empty($config['secret_key'])) {
        return ['success' => false, 'error' => 'Stripe not configured'];
    }

    // Implementation would use Stripe SDK
    return [
        'success' => false,
        'error' => 'Stripe SDK integration pending'
    ];
}

/**
 * ComGate payment creation
 */
function createComGatePayment($orderData, $config) {
    if (empty($config['merchant_id']) || empty($config['secret'])) {
        return ['success' => false, 'error' => 'ComGate not configured'];
    }

    $data = [
        'merchant' => $config['merchant_id'],
        'price' => (int)($orderData['total_amount'] * 100),
        'curr' => 'CZK',
        'label' => $orderData['order_number'],
        'refId' => $orderData['order_number'],
        'email' => $orderData['customer_email'],
        'method' => 'ALL',
        'prepareOnly' => 'true',
        'test' => $config['test'] ? 'true' : 'false',
        'country' => 'CZ',
        'phone' => $orderData['customer_phone'] ?? '',
        'name' => $orderData['customer_name']
    ];

    // Create signature
    $data['secret'] = $config['secret'];
    ksort($data);
    $sign = hash('sha256', implode('', $data));
    unset($data['secret']);
    $data['sign'] = $sign;

    $url = 'https://payments.comgate.cz/v1.0/create';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $response = curl_exec($ch);
    curl_close($ch);

    parse_str($response, $result);

    if ($result['code'] === '0') {
        return [
            'success' => true,
            'payment_url' => $result['redirect'],
            'transaction_id' => $result['transId']
        ];
    }

    return ['success' => false, 'error' => $result['message'] ?? 'Payment creation failed'];
}
?>