<?php
/**
 * Admin Login - with CSRF protection
 */

require_once '../config/database.php';
require_once '../config.php';

$error = '';

// Rate limiting for login attempts
$ip = get_client_ip();
if (!check_rate_limit("login_{$ip}", 5, 300)) { // 5 attempts per 5 minutes
    $error = 'Příliš mnoho pokusů o přihlášení. Zkuste to znovu za 5 minut.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Verify CSRF token
    if (!verify_csrf_token($csrf_token)) {
        $error = 'Neplatný bezpečnostní token. Zkuste to znovu.';
    } elseif (!empty($username) && !empty($password)) {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            adminLogin($admin['id'], $admin['username']);
            header('Location: /admin/dashboard.php');
            exit;
        } else {
            $error = 'Nesprávné přihlašovací údaje';
        }
    } else {
        $error = 'Vyplňte všechna pole';
    }
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrace - Přihlášení</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="admin-login">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="/images/logo.png" alt="Nech Mě Růst" class="login-logo">
                <h1>Administrace</h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="username">Uživatelské jméno</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        required
                        autofocus
                        autocomplete="username"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Heslo</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Přihlásit se
                </button>
            </form>
        </div>
    </div>
</body>
</html>