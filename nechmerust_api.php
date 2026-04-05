<?php
/**
 * Event Registration API - with security improvements
 */

chdir(__DIR__);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda není povolena.']);
    exit;
}

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!isset($_SESSION['event_rate'])) {
    $_SESSION['event_rate'] = ['count' => 0, 'reset' => time() + 60];
}

$rate = $_SESSION['event_rate'];
if (time() > $rate['reset']) {
    $_SESSION['event_rate'] = ['count' => 1, 'reset' => time() + 60];
} else {
    if ($rate['count'] >= 5) {
        echo json_encode(['success' => false, 'message' => 'Příliš mnoho požadavků.']);
        exit;
    }
    $rate['count']++;
    $_SESSION['event_rate'] = $rate;
}

$config = require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents('php://input'), true);

// Sanitize inputs
$event_title = isset($data['event_title']) ? preg_replace('/[^a-zA-Z0-9\s\-čšěřžýáíéůúňďťľ]/', '', trim($data['event_title'])) : '';
$name = isset($data['name']) ? preg_replace('/[^a-zA-Z\s\-čšěřžýáíéůúňďťľ]/', '', trim($data['name'])) : '';
$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';
$message = isset($data['message']) ? preg_replace('/[^a-zA-Z0-9\s\-čšěřžýáíéůúňďťľ.,!?\n]/', '', trim($data['message'])) : '';

// Validate
if (empty($event_title) || empty($name) || mb_strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatná data.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatný e-mail.']);
    exit;
}

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

$smtp = $config['smtp'];

// Admin notification
$admin_subject = "Nová registrace na událost: " . htmlspecialchars($event_title);
$admin_body = "<h2>Nová registrace na událost</h2><p><strong>Událost:</strong> " . htmlspecialchars($event_title) . "</p><p><strong>Jméno:</strong> " . htmlspecialchars($name) . "</p><p><strong>Email:</strong> " . htmlspecialchars($email) . "</p><p><strong>Zpráva:</strong> " . nl2br(htmlspecialchars($message)) . "</p>";
$admin_sent = send_phpmailer_email('info@nechmerust.org', $admin_subject, $admin_body, $smtp);

// User confirmation
$user_subject = "Potvrzení registrace: " . htmlspecialchars($event_title);
$user_body = "<h2>Dobrý den, " . htmlspecialchars($name) . "</h2><p>Děkujeme za registraci na " . htmlspecialchars($event_title) . ".</p>";
$user_sent = send_phpmailer_email($email, $user_subject, $user_body, $smtp);

if ($admin_sent && $user_sent) {
    echo json_encode(['success' => true, 'message' => 'Registrace úspěšná!']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Chyba při odesílání e-mailu.']);
}
?>