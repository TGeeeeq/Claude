<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pdo = getDbConnection();

// Handle product deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: /admin/products.php');
    exit;
}

// Get all products with categories
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkty - Administrace</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="page-header">
                <h1>Produkty</h1>
                <a href="/admin/product-edit.php" class="btn btn-primary">+ Přidat produkt</a>
            </div>
            
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Obrázek</th>
                            <th>Název</th>
                            <th>Kategorie</th>
                            <th>Cena</th>
                            <th>Sklad</th>
                            <th>Stav</th>
                            <th>Akce</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product['image_url']): ?>
                                    <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="" class="table-img">
                                <?php else: ?>
                                    <div class="table-img-placeholder">Bez obrázku</div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['category_name'] ?? 'Bez kategorie') ?></td>
                            <td><?= number_format($product['price'], 0, ',', ' ') ?> Kč</td>
                            <td><?= $product['stock_quantity'] ?> ks</td>
                            <td>
                                <span class="badge badge-<?= $product['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $product['is_active'] ? 'Aktivní' : 'Neaktivní' ?>
                                </span>
                            </td>
                            <td class="table-actions">
                                <a href="/admin/product-edit.php?id=<?= $product['id'] ?>" class="btn btn-sm">Upravit</a>
                                <a href="/admin/products.php?delete=<?= $product['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('Opravdu smazat tento produkt?')">Smazat</a>
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
