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

$imageId = $data['image_id'] ?? '';

if (empty($imageId)) {
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
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
