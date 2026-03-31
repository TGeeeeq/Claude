<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pdo = getDbConnection();

// Get statistics
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// Get recent orders
$recentOrders = $pdo->query("
    SELECT * FROM orders 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administrace</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="page-header">
                <h1>Dashboard</h1>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📦</div>
                    <div class="stat-details">
                        <div class="stat-value"><?= $totalProducts ?></div>
                        <div class="stat-label">Celkem produktů</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✓</div>
                    <div class="stat-details">
                        <div class="stat-value"><?= $activeProducts ?></div>
                        <div class="stat-label">Aktivních produktů</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-details">
                        <div class="stat-value"><?= $totalOrders ?></div>
                        <div class="stat-label">Celkem objednávek</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-details">
                        <div class="stat-value"><?= $pendingOrders ?></div>
                        <div class="stat-label">Čekajících objednávek</div>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <h2>Poslední objednávky</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Číslo objednávky</th>
                                <th>Zákazník</th>
                                <th>Celkem</th>
                                <th>Stav</th>
                                <th>Datum</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['order_number']) ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= number_format($order['total_amount'], 0, ',', ' ') ?> Kč</td>
                                <td><span class="badge badge-<?= $order['status'] ?>"><?= $order['status'] ?></span></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/orders.php?id=<?= $order['id'] ?>" class="btn btn-sm">Detail</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
