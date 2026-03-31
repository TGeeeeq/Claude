<aside class="admin-sidebar">
    <nav class="admin-nav">
        <a href="/admin/dashboard.php" class="admin-nav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span>
            Dashboard
        </a>
        <a href="/admin/products.php" class="admin-nav-item <?= basename($_SERVER['PHP_SELF']) === 'products.php' || basename($_SERVER['PHP_SELF']) === 'product-edit.php' ? 'active' : '' ?>">
            <span class="nav-icon">📦</span>
            Produkty
        </a>
        <a href="/admin/orders.php" class="admin-nav-item <?= basename($_SERVER['PHP_SELF']) === 'orders.php' ? 'active' : '' ?>">
            <span class="nav-icon">🛒</span>
            Objednávky
        </a>
        <a href="/admin/categories.php" class="admin-nav-item <?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
            <span class="nav-icon">📁</span>
            Kategorie
        </a>
        <a href="/" class="admin-nav-item" target="_blank">
            <span class="nav-icon">🌐</span>
            Zobrazit web
        </a>
    </nav>
</aside>
