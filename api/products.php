<?php
/**
 * Products API - with security improvements
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? ''));
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

$pdo = getDbConnection();

// Get filter parameters - sanitize inputs
$category = isset($_GET['category']) ? preg_replace('/[^a-z0-9\-]/', '', $_GET['category']) : '';
$search = isset($_GET['search']) ? preg_replace('/[^a-zA-Z0-9\s\-čšěřžýáíéůúňďťľ]/', '', trim($_GET['search'])) : '';

// Build query with parameterized placeholders
$sql = "
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
";

$params = [];

if (!empty($category)) {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    // Use parameterized search with wildcards added in PHP
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY p.created_at DESC";

// Limit results to prevent abuse
$sql .= " LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

echo json_encode([
    'success' => true,
    'products' => $products
]);
?>