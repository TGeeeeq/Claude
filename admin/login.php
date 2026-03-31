<?php
require_once '../config/database.php';
require_once '../config/session.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
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
                <div class="form-group">
                    <label for="username">Uživatelské jméno</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        required 
                        autofocus
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">Heslo</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
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
