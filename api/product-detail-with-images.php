<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$slug = $_GET['slug'] ?? '';

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

// Get all images for this product
$imagesStmt = $pdo->prepare("
    SELECT id, image_url, display_order
    FROM product_images
    WHERE product_id = ?
    ORDER BY display_order ASC
");
$imagesStmt->execute([$product['id']]);
$images = $imagesStmt->fetchAll();

// If no images in product_images table, use the main image_url
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
