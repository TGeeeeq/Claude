<header class="admin-header">
    <div class="admin-header-content">
        <div class="admin-logo">
            <img src="/images/logo.png" alt="Nech Mě Růst">
            <span>Administrace</span>
        </div>
        <div class="admin-user">
            <span>Přihlášen jako: <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong></span>
            <a href="/admin/logout.php" class="btn btn-sm">Odhlásit se</a>
        </div>
    </div>
</header>
