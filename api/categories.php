<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

$pdo = getDbConnection();

$categories = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
    GROUP BY c.id
    ORDER BY c.display_order, c.name
")->fetchAll();

echo json_encode([
    'success' => true,
    'categories' => $categories
]);
?>
