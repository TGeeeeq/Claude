<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$pdo = getDbConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Get images for a product
    $productId = $_GET['product_id'] ?? '';
    
    if (empty($productId)) {
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
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>
