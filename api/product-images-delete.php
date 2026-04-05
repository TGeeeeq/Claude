<?php
/**
 * Delete Product Image API - Admin only
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

requireAdmin();

$pdo = getDbConnection();
$data = json_decode(file_get_contents('php://input'), true);

$imageId = isset($data['image_id']) ? (int)$data['image_id'] : 0;

if ($imageId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Missing image_id']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM product_images WHERE id = ?");
    $stmt->execute([$imageId]);

    echo json_encode([
        'success' => true,
        'message' => 'Image deleted successfully'
    ]);
} catch (PDOException $e) {
    error_log("Image delete error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Failed to delete image']);
}
?>