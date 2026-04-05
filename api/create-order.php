<?php
/**
 * Order creation API with security improvements
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

require_once '../config/database.php';
require_once '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Rate limiting - 5 orders per minute per IP
$ip = get_client_ip();
if (!check_rate_limit("order_{$ip}", 5, 60)) {
    echo json_encode(['success' => false, 'error' => 'Příliš mnoho požadavků. Zkuste to znovu za minutu.']);
    exit;
}

// Load vendor and config
require_once '../vendor/autoload.php';
$config = require '../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Neplatná data.']);
    exit;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Validate required fields
$required = ['customer_name', 'customer_email', 'shipping_address', 'items'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success' => false, 'error' => "Chybí pole: $field"]);
        exit;
    }
}

$customer_name  = sanitize($data['customer_name']);
$customer_email = filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL);
$customer_phone = sanitize($data['customer_phone'] ?? '');
$shipping_addr  = sanitize($data['shipping_address']);
$payment_method = sanitize($data['payment_method'] ?? 'bank_transfer');
$notes          = sanitize($data['notes'] ?? '');

if (!$customer_email) {
    echo json_encode(['success' => false, 'error' => 'Neplatný e-mail.']);
    exit;
}

// Validate items array
if (!is_array($data['items']) || empty($data['items'])) {
    echo json_encode(['success' => false, 'error' => 'Neplatné položky objednávky.']);
    exit;
}

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    $orderNumber = 'NMR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

    // Calculate and validate total from database (never trust client-side prices)
    $total_amount = 0;
    $itemsListText = "";

    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach ($data['items'] as $item) {
        $product_id = (int)($item['product_id'] ?? 0);
        $qty = max(0, (int)($item['quantity'] ?? 0));

        if ($product_id > 0 && $qty > 0) {
            // Get actual price from database
            $prodStmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ? AND is_active = 1");
            $prodStmt->execute([$product_id]);
            $product = $prodStmt->fetch();

            if ($product) {
                $u_price = (float)$product['price'];
                $t_price = $qty * $u_price;
                $p_name = sanitize($product['name']);

                $itemStmt->execute([null, $product_id, $p_name, $qty, $u_price, $t_price]);
                $itemsListText .= "- $p_name ($qty ks): " . number_format($t_price, 0, ',', ' ') . " Kč\n";
                $total_amount += $t_price;
            }
        }
    }

    if ($total_amount <= 0) {
        throw new Exception("Neplatná částka objednávky.");
    }

    // Insert order with calculated total
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, customer_name, customer_email, customer_phone,
            shipping_address, total_amount, payment_method, notes, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");

    $stmt->execute([
        $orderNumber, $customer_name, $customer_email, $customer_phone,
        $shipping_addr, $total_amount, $payment_method, $notes
    ]);

    $orderId = $pdo->lastInsertId();

    // Update order_items with correct order_id
    $pdo->prepare("UPDATE order_items SET order_id = ? WHERE order_id IS NULL")
        ->execute([$orderId]);

    $pdo->commit();
    $_SESSION['last_order_time'] = time();

    // Send confirmation emails
    $mail = new PHPMailer(true);
    $smtp = $config['smtp'];

    $mail->isSMTP();
    $mail->Host       = $smtp['Host'];
    $mail->SMTPAuth   = $smtp['SMTPAuth'];
    $mail->Username   = $smtp['Username'];
    $mail->Password   = $smtp['Password'];
    $mail->SMTPSecure = $smtp['SMTPSecure'];
    $mail->Port       = $smtp['Port'];
    $mail->CharSet    = 'UTF-8';

    // Customer email
    $mail->setFrom($smtp['FromEmail'], $smtp['FromName']);
    $mail->addAddress($customer_email, $customer_name);
    $mail->isHTML(true);
    $mail->Subject = "Potvrzení objednávky $orderNumber - Nech mě růst";

    $bankInfo = "
        <p><strong>Údaje pro platbu převodem:</strong><br>
        Číslo účtu: 2002645872 / 2010 (Fio banka, a.s.)<br>
        IBAN: CZ49 2010 2002 6400 0000 5872<br>
        SWIFT: FIOBCZPP<br>
        <strong>Variabilní symbol: " . preg_replace('/[^0-9]/', '', $orderNumber) . "</strong></p>
    ";

    $mail->Body = "
        <h2>Děkujeme za vaši objednávku!</h2>
        <p>Vaše objednávka č. <strong>$orderNumber</strong> byla úspěšně přijata.</p>
        <h3>Shrnutí objednávky:</h3>
        <pre>" . htmlspecialchars($itemsListText) . "</pre>
        <p><strong>Celková částka: " . number_format($total_amount, 0, ',', ' ') . " Kč</strong></p>
        $bankInfo
        <p>Jakmile platbu obdržíme, začneme na vaší objednávce pracovat.</p>
        <hr>
        <p>S úctou,<br>Tým Nech Mě Růst</p>
    ";

    $mail->send();

    // Admin notification
    $mail->clearAddresses();
    $mail->addAddress('info@nechmerust.org');
    $mail->Subject = "NOVÁ OBJEDNÁVKA: $orderNumber";
    $mail->Body = "
        <h2>Nová objednávka na webu</h2>
        <p><strong>Zákazník:</strong> $customer_name ($customer_email)<br>
        <strong>Telefon:</strong> $customer_phone<br>
        <strong>Adresa:</strong> $shipping_addr</p>
        <p><strong>Položky:</strong></p>
        <pre>" . htmlspecialchars($itemsListText) . "</pre>
        <p><strong>Celkem: " . number_format($total_amount, 0, ',', ' ') . " Kč</strong></p>
        <p><strong>Poznámka:</strong> $notes</p>
    ";
    $mail->send();

    echo json_encode(['success' => true, 'order_number' => $orderNumber, 'total' => $total_amount]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Order error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Chyba při zpracování objednávky.']);
}
?>