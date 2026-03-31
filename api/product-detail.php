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

echo json_encode([
    'success' => true,
    'product' => $product
]);
?>
