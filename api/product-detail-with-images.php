<?php
/**
 * Product Detail with Images API
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

// Sanitize slug
$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['slug']) : '';

if (empty($slug)) {
    echo json_encode(['success' => false, 'error' => 'Missing product slug']);
    exit;
}

$pdo = getDbConnection();

// Get product details
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.slug = ? AND p.is_active = 1
");
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'error' => 'Product not found']);
    exit;
}

// Get all images
$imagesStmt = $pdo->prepare("
    SELECT id, image_url, display_order
    FROM product_images
    WHERE product_id = ?
    ORDER BY display_order ASC
");
$imagesStmt->execute([$product['id']]);
$images = $imagesStmt->fetchAll();

// Use main image if no additional images
if (empty($images) && !empty($product['image_url'])) {
    $images = [
        [
            'id' => 0,
            'image_url' => $product['image_url'],
            'display_order' => 0
        ]
    ];
}

echo json_encode([
    'success' => true,
    'product' => $product,
    'images' => $images
]);
?>