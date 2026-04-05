<?php
/**
 * Categories API - public read-only endpoint
 */

header('Content-Type: application/json');

// BEZPEČNÁ CORS KONFIGURACE - pouze whitelist povolených domén
$allowedOrigins = [
    'https://nechmerust.org',
    'https://www.nechmerust.org'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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