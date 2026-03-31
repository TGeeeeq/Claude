<?php
/**
 * Finální verze zpracování objednávky s e-mailovými notifikacemi
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
// Načtení PHPMailer přes autoload (pokud používáte Composer) nebo ručně
require_once '../vendor/autoload.php';
$config = require '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ochrana proti spamu
if (isset($_SESSION['last_order_time']) && (time() - $_SESSION['last_order_time'] < 20)) {
    echo json_encode(['success' => false, 'error' => 'Prosím, počkejte chvíli před další objednávkou.']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Neplatná data.']);
    exit;
}

function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Validace polí
$required = ['customer_name', 'customer_email', 'shipping_address', 'items', 'total_amount'];
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
$total_amount   = (float)$data['total_amount'];

if (!$customer_email) {
    echo json_encode(['success' => false, 'error' => 'Neplatný e-mail.']);
    exit;
}

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();
    
    $orderNumber = 'NMR-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
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
    
    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, total_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $itemsListText = "";
    foreach ($data['items'] as $item) {
        $p_name = sanitize($item['product_name'] ?? 'Produkt');
        $qty = (int)($item['quantity'] ?? 0);
        $u_price = (float)($item['unit_price'] ?? 0);
        $t_price = $qty * $u_price;

        if ($qty > 0) {
            $itemStmt->execute([$orderId, (int)$item['product_id'], $p_name, $qty, $u_price, $t_price]);
            $itemsListText .= "- $p_name ($qty ks): " . number_format($t_price, 0, ',', ' ') . " Kč\n";
        }
    }
    
    $pdo->commit();
    $_SESSION['last_order_time'] = time();

    // --- ODESÍLÁNÍ EMAILŮ ---
    $mail = new PHPMailer(true);
    $smtp = $config['smtp'];

    // Nastavení serveru
    $mail->isSMTP();
    $mail->Host       = $smtp['Host'];
    $mail->SMTPAuth   = $smtp['SMTPAuth'];
    $mail->Username   = $smtp['Username'];
    $mail->Password   = $smtp['Password'];
    $mail->SMTPSecure = $smtp['SMTPSecure'];
    $mail->Port       = $smtp['Port'];
    $mail->CharSet    = 'UTF-8';

    // 1. Email zákazníkovi
    $mail->setFrom($smtp['FromEmail'], $smtp['FromName']);
    $mail->addAddress($customer_email, $customer_name);
    $mail->isHTML(true);
    $mail->Subject = "Potvrzení objednávky $orderNumber - Nech mě růst";

    $bankInfo = "
        <p><strong>Údaje pro platbu převodem:</strong><br>
        Číslo účtu: 2002645872 / 2010 (Fio banka, a.s.)<br>
        IBAN: CZ49 2010 2002 6400 0000 5872<br>
        SWIFT: FIOBCZPP<br>
        <strong>Variabilní symbol: " . preg_replace('/[^0-9]/', '', $orderNumber) . "</strong> (nebo uveďte číslo objednávky do poznámky)</p>
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

    // 2. Email administrátorovi
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

    echo json_encode(['success' => true, 'order_number' => $orderNumber]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) { $pdo->rollBack(); }
    error_log("Chyba objednávky/emailu: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Chyba při zpracování.']);
}
?>