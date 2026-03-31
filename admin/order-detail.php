<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pdo = getDbConnection();

$orderId = (int)($_GET['id'] ?? 0);

$order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$order->execute([$orderId]);
$order = $order->fetch();

if (!$order) {
    header('Location: /admin/orders.php');
    exit;
}

$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$orderId]);
$items = $items->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail objednávky - Administrace</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="page-header">
                <h1>Detail objednávky <?= htmlspecialchars($order['order_number']) ?></h1>
                <a href="/admin/orders.php" class="btn btn-secondary">Zpět na seznam</a>
            </div>
            
            <div class="order-detail-grid">
                <div class="section">
                    <h2>Informace o zákazníkovi</h2>
                    <div class="info-grid">
                        <div>
                            <strong>Jméno:</strong><br>
                            <?= htmlspecialchars($order['customer_name']) ?>
                        </div>
                        <div>
                            <strong>Email:</strong><br>
                            <a href="mailto:<?= htmlspecialchars($order['customer_email']) ?>">
                                <?= htmlspecialchars($order['customer_email']) ?>
                            </a>
                        </div>
                        <div>
                            <strong>Telefon:</strong><br>
                            <?= htmlspecialchars($order['customer_phone'] ?: 'Nezadáno') ?>
                        </div>
                        <div>
                            <strong>Doručovací adresa:</strong><br>
                            <?= nl2br(htmlspecialchars($order['shipping_address'])) ?>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2>Objednaté produkty</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Produkt</th>
                                <th>Množství</th>
                                <th>Cena za kus</th>
                                <th>Celkem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['product_name']) ?></td>
                                <td><?= $item['quantity'] ?>×</td>
                                <td><?= number_format($item['unit_price'], 0, ',', ' ') ?> Kč</td>
                                <td><?= number_format($item['total_price'], 0, ',', ' ') ?> Kč</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" style="text-align: right;"><strong>Celkem:</strong></td>
                                <td><strong><?= number_format($order['total_amount'], 0, ',', ' ') ?> Kč</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="section">
                    <h2>Detaily objednávky</h2>
                    <div class="info-grid">
                        <div>
                            <strong>Číslo objednávky:</strong><br>
                            <?= htmlspecialchars($order['order_number']) ?>
                        </div>
                        <div>
                            <strong>Datum vytvoření:</strong><br>
                            <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?>
                        </div>
                        <div>
                            <strong>Způsob platby:</strong><br>
                            <?= htmlspecialchars($order['payment_method']) ?>
                        </div>
                        <div>
                            <strong>Stav:</strong><br>
                            <span class="badge badge-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                        </div>
                    </div>
                    
                    <?php if ($order['notes']): ?>
                    <div style="margin-top: 20px;">
                        <strong>Poznámka zákazníka:</strong><br>
                        <?= nl2br(htmlspecialchars($order['notes'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    
    <style>
        .order-detail-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .filter-select, .status-select {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</body>
</html>
