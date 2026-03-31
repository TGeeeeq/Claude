<?php
/**
 * Payment Gateway Configuration
 * 
 * This file contains configuration for payment gateway integration.
 * Currently supports:
 * - GoPay (recommended for Czech market)
 * - Stripe
 * - PayPal
 * - ComGate
 */

// Payment gateway selection
define('PAYMENT_GATEWAY', 'gopay'); // Options: 'gopay', 'stripe', 'comgate', 'paypal'

// GoPay Configuration (Czech payment gateway)
define('GOPAY_GOID', ''); // Your GoPay GO ID
define('GOPAY_CLIENT_ID', '');
define('GOPAY_CLIENT_SECRET', '');
define('GOPAY_TEST_MODE', true); // Set to false for production

// Stripe Configuration
define('STRIPE_PUBLIC_KEY', '');
define('STRIPE_SECRET_KEY', '');

// ComGate Configuration
define('COMGATE_MERCHANT_ID', '');
define('COMGATE_SECRET', '');
define('COMGATE_TEST_MODE', true);

// PayPal Configuration
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_SECRET', '');
define('PAYPAL_MODE', 'sandbox'); // 'sandbox' or 'live'

// Payment return URLs
define('PAYMENT_RETURN_URL', 'https://nechmerust.org/obchod/payment-return.php');
define('PAYMENT_NOTIFY_URL', 'https://nechmerust.org/api/payment-notify.php');

/**
 * Initialize payment gateway
 */
function initPaymentGateway() {
    switch (PAYMENT_GATEWAY) {
        case 'gopay':
            // GoPay SDK initialization
            // require_once __DIR__ . '/../vendor/gopay/payments-sdk-php/autoload.php';
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
            // Stripe SDK initialization
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
 * 
 * @param array $orderData Order information
 * @return array Payment URL and transaction ID
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
    // Example GoPay payment creation
    // Uncomment and configure when GoPay SDK is installed
    
    /*
    $gopay = \GoPay\payments([
        'goid' => $config['goid'],
        'clientId' => $config['clientId'],
        'clientSecret' => $config['clientSecret'],
        'isProductionMode' => $config['isProductionMode']
    ]);
    
    $response = $gopay->createPayment([
        'payer' => [
            'default_payment_instrument' => 'BANK_ACCOUNT',
            'allowed_payment_instruments' => ['BANK_ACCOUNT', 'PAYMENT_CARD'],
            'contact' => [
                'first_name' => $orderData['customer_name'],
                'email' => $orderData['customer_email'],
                'phone_number' => $orderData['customer_phone']
            ]
        ],
        'amount' => $orderData['total_amount'] * 100, // Amount in cents
        'currency' => 'CZK',
        'order_number' => $orderData['order_number'],
        'order_description' => 'Objednávka z Loučného obchůdku',
        'items' => array_map(function($item) {
            return [
                'name' => $item['product_name'],
                'amount' => $item['total_price'] * 100,
                'count' => $item['quantity']
            ];
        }, $orderData['items']),
        'callback' => [
            'return_url' => PAYMENT_RETURN_URL,
            'notification_url' => PAYMENT_NOTIFY_URL
        ]
    ]);
    
    if ($response->hasSucceed()) {
        return [
            'success' => true,
            'payment_url' => $response->json['gw_url'],
            'transaction_id' => $response->json['id']
        ];
    }
    
    return ['success' => false, 'error' => 'GoPay payment creation failed'];
    */
    
    // Placeholder response
    return [
        'success' => false,
        'error' => 'GoPay not configured. Please add GoPay credentials to config/payment.php'
    ];
}

/**
 * Stripe payment creation
 */
function createStripePayment($orderData, $config) {
    // Example Stripe payment creation
    // Uncomment when Stripe SDK is installed
    
    /*
    \Stripe\Stripe::setApiKey($config['secret_key']);
    
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => array_map(function($item) {
            return [
                'price_data' => [
                    'currency' => 'czk',
                    'product_data' => [
                        'name' => $item['product_name'],
                    ],
                    'unit_amount' => $item['unit_price'] * 100,
                ],
                'quantity' => $item['quantity'],
            ];
        }, $orderData['items']),
        'mode' => 'payment',
        'success_url' => PAYMENT_RETURN_URL . '?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://nechmerust.org/obchod/checkout.html',
        'customer_email' => $orderData['customer_email'],
        'metadata' => [
            'order_number' => $orderData['order_number']
        ]
    ]);
    
    return [
        'success' => true,
        'payment_url' => $session->url,
        'transaction_id' => $session->id
    ];
    */
    
    return [
        'success' => false,
        'error' => 'Stripe not configured. Please add Stripe credentials to config/payment.php'
    ];
}

/**
 * ComGate payment creation
 */
function createComGatePayment($orderData, $config) {
    $data = [
        'merchant' => $config['merchant_id'],
        'price' => $orderData['total_amount'] * 100, // Amount in cents
        'curr' => 'CZK',
        'label' => $orderData['order_number'],
        'refId' => $orderData['order_number'],
        'email' => $orderData['customer_email'],
        'method' => 'ALL',
        'prepareOnly' => 'true',
        'test' => $config['test'] ? 'true' : 'false',
        'country' => 'CZ',
        'account' => '',
        'phone' => $orderData['customer_phone'],
        'name' => $orderData['customer_name']
    ];
    
    // Create signature
    $data['secret'] = $config['secret'];
    ksort($data);
    $sign = hash('sha256', implode('', $data));
    unset($data['secret']);
    
    // Send request to ComGate
    $url = 'https://payments.comgate.cz/v1.0/create';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    // Parse response
    parse_str($response, $result);
    
    if ($result['code'] === '0') {
        return [
            'success' => true,
            'payment_url' => $result['redirect'],
            'transaction_id' => $result['transId']
        ];
    }
    
    return ['success' => false, 'error' => 'ComGate payment creation failed: ' . ($result['message'] ?? 'Unknown error')];
}
?>
