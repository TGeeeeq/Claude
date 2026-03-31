<?php
/**
 * Newsletter Signup API - with security improvements
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metoda není povolena.']);
    exit;
}

// Rate limiting - 3 signups per minute per IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!isset($_SESSION['newsletter_rate'])) {
    $_SESSION['newsletter_rate'] = ['count' => 0, 'reset' => time() + 60];
}

$rate = $_SESSION['newsletter_rate'];
if (time() > $rate['reset']) {
    $_SESSION['newsletter_rate'] = ['count' => 1, 'reset' => time() + 60];
} else {
    if ($rate['count'] >= 3) {
        echo json_encode(['success' => false, 'message' => 'Příliš mnoho požadavků.']);
        exit;
    }
    $rate['count']++;
    $_SESSION['newsletter_rate'] = $rate;
}

// Load config
$config = require 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Validate and sanitize
$name = isset($data['name']) ? preg_replace('/[^a-zA-Z\s\-čšěřžýáíéůúňďťľČŠĚŘŽÝÁÍÉŮÚŇĎŤĽ]/', '', trim($data['name'])) : '';
$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : '';

// Validate
if (empty($name) || mb_strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatné jméno.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Neplatný e-mail.']);
    exit;
}

// Function to send email
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
$admin_subject = "Nový odběratel newsletteru: " . htmlspecialchars($name);
$admin_body = "<h2>Nový odběratel newsletteru</h2><p><strong>Jméno:</strong> " . htmlspecialchars($name) . "</p><p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
$admin_sent = send_phpmailer_email('info@nechmerust.org', $admin_subject, $admin_body, $smtp);

// User confirmation
$user_subject = "Vítejte v našem newsletteru! Nech mě růst.";
$html_email_template = file_get_contents(__DIR__ . '/newsletter-email.html');
$personalized_html = str_replace('Milí přátelé a podporovatelé,', 'Dobrý den, ' . htmlspecialchars($name) . ',', $html_email_template);
$user_sent = send_phpmailer_email($email, $user_subject, $personalized_html, $smtp);

if ($admin_sent && $user_sent) {
    echo json_encode(['success' => true, 'message' => 'Děkujeme za přihlášení!']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Chyba při odesílání e-mailu.']);
}
?>