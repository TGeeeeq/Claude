<?php
/**
 * Add Product Image API - Admin only
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../config/session.php';

// Use requireAdmin() which now has CSRF and IP checking
requireAdmin();

$pdo = getDbConnection();
$data = json_decode(file_get_contents('php://input'), true);

$productId = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$imageUrl = isset($data['image_url']) ? filter_var($data['image_url'], FILTER_SANITIZE_URL) : '';

if ($productId <= 0 || empty($imageUrl)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

// Validate URL format
if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid image URL']);
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
    error_log("Image add error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to add image']);
}
?>