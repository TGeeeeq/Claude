<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pdo = getDbConnection();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    header('Location: /admin/orders.php');
    exit;
}

// Get filter
$statusFilter = $_GET['status'] ?? '';

// Build query
$sql = "SELECT * FROM orders WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Objednávky - Administrace</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="page-header">
                <h1>Objednávky</h1>
                <select onchange="window.location.href='/admin/orders.php?status=' + this.value" class="filter-select">
                    <option value="">Všechny objednávky</option>
                    <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>Čekající</option>
                    <option value="paid" <?= $statusFilter === 'paid' ? 'selected' : '' ?>>Zaplacené</option>
                    <option value="processing" <?= $statusFilter === 'processing' ? 'selected' : '' ?>>Zpracovává se</option>
                    <option value="shipped" <?= $statusFilter === 'shipped' ? 'selected' : '' ?>>Odesláno</option>
                    <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>Dokončeno</option>
                    <option value="cancelled" <?= $statusFilter === 'cancelled' ? 'selected' : '' ?>>Zrušeno</option>
                </select>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Číslo objednávky</th>
                            <th>Zákazník</th>
                            <th>Email</th>
                            <th>Celkem</th>
                            <th>Stav</th>
                            <th>Datum</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($order['order_number']) ?></strong></td>
                            <td><?= htmlspecialchars($order['customer_name']) ?></td>
                            <td><?= htmlspecialchars($order['customer_email']) ?></td>
                            <td><?= number_format($order['total_amount'], 0, ',', ' ') ?> Kč</td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="status" onchange="this.form.submit()" class="status-select">
                                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Čekající</option>
                                        <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Zaplaceno</option>
                                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Zpracovává se</option>
                                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Odesláno</option>
                                        <option value="completed" <?= $order['status'] === 'completed' ? 'selected' : '' ?>>Dokončeno</option>
                                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Zrušeno</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <a href="/admin/order-detail.php?id=<?= $order['id'] ?>" class="btn btn-sm">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
