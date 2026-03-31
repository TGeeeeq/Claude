<?php
/**
 * Product Images API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Sanitize product ID
    $productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

    if ($productId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Missing product_id']);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT id, image_url, display_order
        FROM product_images
        WHERE product_id = ?
        ORDER BY display_order ASC
    ");
    $stmt->execute([$productId]);
    $images = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'images' => $images
    ]);
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>