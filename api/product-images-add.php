<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/session.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$pdo = getDbConnection();
$data = json_decode(file_get_contents('php://input'), true);

$productId = $data['product_id'] ?? '';
$imageUrl = $data['image_url'] ?? '';

if (empty($productId) || empty($imageUrl)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Get the highest display order
    $stmt = $pdo->prepare("SELECT MAX(display_order) as max_order FROM product_images WHERE product_id = ?");
    $stmt->execute([$productId]);
    $result = $stmt->fetch();
    $nextOrder = ($result['max_order'] ?? -1) + 1;
    
    // Insert new image
    $stmt = $pdo->prepare("
        INSERT INTO product_images (product_id, image_url, display_order)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$productId, $imageUrl, $nextOrder]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Image added successfully',
        'image_id' => $pdo->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
