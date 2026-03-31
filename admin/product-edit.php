<?php
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$pdo = getDbConnection();
$error = '';
$success = '';
$product = null;

// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY display_order, name")->fetchAll();

// Edit existing product
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $product = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $product->execute([$id]);
    $product = $product->fetch();
    
    if (!$product) {
        header('Location: /admin/products.php');
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $image_url = trim($_POST['image_url'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    if (empty($name) || empty($slug) || $price <= 0) {
        $error = 'Vyplňte všechna povinná pole';
    } else {
        try {
            if ($product) {
                // Update existing product
                $stmt = $pdo->prepare("
                    UPDATE products SET 
                        name = ?, slug = ?, description = ?, price = ?, 
                        stock_quantity = ?, category_id = ?, image_url = ?, is_active = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $slug, $description, $price, $stock_quantity, $category_id, $image_url, $is_active, $product['id']]);
                $success = 'Produkt byl úspěšně aktualizován';
            } else {
                // Create new product
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, slug, description, price, stock_quantity, category_id, image_url, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $slug, $description, $price, $stock_quantity, $category_id, $image_url, $is_active]);
                $success = 'Produkt byl úspěšně vytvořen';
                header('Location: /admin/products.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Chyba: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $product ? 'Upravit' : 'Přidat' ?> produkt - Administrace</title>
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        .product-images-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 1rem;
        }
        .image-item {
            padding: 10px;
            background: #f5f5f5;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .image-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 10px;
        }
        .image-item-content {
            flex: 1;
            display: flex;
            align-items: center;
        }
        .add-image-form {
            display: flex;
            gap: 10px;
            margin-top: 1rem;
        }
        .add-image-form input {
            flex: 1;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-content">
            <div class="page-header">
                <h1><?= $product ? 'Upravit produkt' : 'Přidat produkt' ?></h1>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="form-horizontal">
                <div class="form-group">
                    <label for="name">Název produktu *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?= htmlspecialchars($product['name'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label for="slug">URL slug *</label>
                    <input type="text" id="slug" name="slug" required 
                           value="<?= htmlspecialchars($product['slug'] ?? '') ?>">
                    <small>Např: tasticka-s-rucni-malbou (bez diakritiky a mezer)</small>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Kategorie</label>
                    <select id="category_id" name="category_id">
                        <option value="">-- Bez kategorie --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" 
                                <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="description">Popis</label>
                    <textarea id="description" name="description" rows="5"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Cena (Kč) *</label>
                        <input type="number" id="price" name="price" step="0.01" required 
                               value="<?= htmlspecialchars($product['price'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity">Skladem (ks)</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" 
                               value="<?= htmlspecialchars($product['stock_quantity'] ?? 0) ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image_url">Hlavní obrázek (URL)</label>
                    <input type="text" id="image_url" name="image_url" 
                           value="<?= htmlspecialchars($product['image_url'] ?? '') ?>">
                    <small>Nahrajte obrázek přes FTP do složky /images/products/ a zadejte cestu</small>
                </div>
                
                <?php if ($product): ?>
                <div class="form-group">
                    <label>Další fotografie produktu</label>
                    <div id="productImagesContainer" class="product-images-container">
                        <!-- Images will be loaded here -->
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label for="additional_image_url">Přidat další obrázek (URL)</label>
                        <div class="add-image-form">
                            <input type="text" id="additional_image_url" placeholder="/images/products/fotografie.webp">
                            <button type="button" class="btn btn-secondary" onclick="addProductImage()">Přidat</button>
                        </div>
                        <small>Nahrajte obrázek přes FTP do složky /images/products/ a zadejte cestu</small>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" 
                               <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
                        Aktivní produkt
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <?= $product ? 'Uložit změny' : 'Vytvořit produkt' ?>
                    </button>
                    <a href="/admin/products.php" class="btn btn-secondary">Zrušit</a>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        // Auto-generate slug from name
        document.getElementById('name').addEventListener('input', function(e) {
            const slug = e.target.value
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        });
        
        // Load product images if editing
        <?php if ($product): ?>
        loadProductImages(<?= $product['id'] ?>);
        <?php endif; ?>
        
        // Load product images
        async function loadProductImages(productId) {
            try {
                const response = await fetch(`/api/product-images.php?product_id=${productId}`);
                const data = await response.json();
                
                if (data.success && data.images.length > 0) {
                    const container = document.getElementById('productImagesContainer');
                    container.innerHTML = data.images.map((img, index) => `
                        <div class="image-item">
                            <div class="image-item-content">
                                <img src="${img.image_url}" alt="Fotografie ${index + 1}">
                                <span>${img.image_url}</span>
                            </div>
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteProductImage(${img.id})">Smazat</button>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading images:', error);
            }
        }
        
        // Add product image
        async function addProductImage() {
            const imageUrl = document.getElementById('additional_image_url').value.trim();
            if (!imageUrl) {
                alert('Zadejte URL obrázku');
                return;
            }
            
            const productId = new URLSearchParams(window.location.search).get('id');
            if (!productId) {
                alert('Nejdříve uložte produkt');
                return;
            }
            
            try {
                const response = await fetch('/api/product-images-add.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({product_id: productId, image_url: imageUrl})
                });
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('additional_image_url').value = '';
                    loadProductImages(productId);
                    alert('Obrázek byl přidán');
                } else {
                    alert('Chyba: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Chyba při přidávání obrázku');
            }
        }
        
        // Delete product image
        async function deleteProductImage(imageId) {
            if (!confirm('Opravdu smazat tuto fotografii?')) return;
            
            const productId = new URLSearchParams(window.location.search).get('id');
            try {
                const response = await fetch('/api/product-images-delete.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({image_id: imageId})
                });
                const data = await response.json();
                
                if (data.success) {
                    loadProductImages(productId);
                    alert('Fotografie byla smazána');
                } else {
                    alert('Chyba: ' + data.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Chyba při mazání fotografie');
            }
        }
    </script>
</body>
</html>
