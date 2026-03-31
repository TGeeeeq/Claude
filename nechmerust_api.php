<?php
// Přejděte do adresáře projektu pro správné načtení autoloaderu
chdir(__DIR__);

// Nastavení hlaviček pro CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Kontrola, zda se jedná o metodu POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda není povolena.']);
    exit;
}

// Načtení konfigurace a PHPMailer autoloaderu
$config = require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Zpracování dat z formuláře
$data = json_decode(file_get_contents('php://input'), true);

// Získání a sanitizace dat
$event_title = filter_var($data['event_title'] ?? '', FILTER_SANITIZE_STRING);
$name = filter_var($data['name'] ?? '', FILTER_SANITIZE_STRING);
$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
$message = filter_var($data['message'] ?? '', FILTER_SANITIZE_STRING);

// Základní validace
if (empty($event_title) || empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
    exit;
}

// Funkce pro odeslání emailu pomocí PHPMailer
function send_phpmailer_email($to, $subject, $body_html, $smtp_config) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtp_config['Host'];
        $mail->SMTPAuth   = $smtp_config['SMTPAuth'];
        $mail->Username   = $smtp_config['Username'];
        $mail->Password   = $smtp_config['Password'];
        $mail->SMTPSecure = $smtp_config['SMTPSecure'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $smtp_config['Port'];
        $mail->CharSet    = 'UTF-8';
        
        $mail->setFrom($smtp_config['FromEmail'], $smtp_config['FromName']);
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags($body_html);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Error to {$to}: " . $mail->ErrorInfo);
        return false;
    }
}

// 1. Email pro admina
$admin_subject = "Nová registrace na událost: " . $event_title;
$admin_body = "<h2>Nová registrace na událost</h2><p><strong>Událost:</strong> {$event_title}</p><p><strong>Jméno:</strong> {$name}</p><p><strong>Email:</strong> {$email}</p><p><strong>Zpráva:</strong> " . nl2br(htmlspecialchars($message)) . "</p>";
$admin_sent = send_phpmailer_email('info@nechmerust.org', $admin_subject, $admin_body, $config['smtp']);

// 2. Potvrzovací email pro uživatele (zjednodušená verze pro ukázku)
$user_subject = "Potvrzení registrace: " . $event_title;
$user_body = "<h2>Dobrý den, {$name}</h2><p>Děkujeme za registraci na {$event_title}.</p>";
$user_sent = send_phpmailer_email($email, $user_subject, $user_body, $config['smtp']);

if ($admin_sent && $user_sent) {
    echo json_encode(['success' => true, 'message' => 'Registrace úspěšná!']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Chyba při odesílání emailu.']);
}
?>
