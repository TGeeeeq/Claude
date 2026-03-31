<?php
require_once '../config/database.php';

$orderNumber = $_GET['order'] ?? $_GET['refId'] ?? '';
$status = $_GET['status'] ?? 'success';

$pdo = getDbConnection();

if ($orderNumber) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
    $stmt->execute([$orderNumber]);
    $order = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platba - Loučný Obchůdek</title>
    <link rel="stylesheet" href="/css/shop.css">
    <style>
        .payment-result {
            max-width: 600px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }
        .payment-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
        }
        .payment-icon.success {
            background: #4caf50;
            color: white;
        }
        .payment-icon.pending {
            background: #ff9800;
            color: white;
        }
        .payment-icon.failed {
            background: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <header class="shop-header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="/images/logo.png" alt="Nech Mě Růst">
                    <span>Nech Mě Růst</span>
                </a>
            </div>
        </div>
    </header>

    <div class="payment-result">
        <?php if ($status === 'success' || $status === 'PAID'): ?>
            <div class="payment-icon success">✓</div>
            <h1>Platba proběhla úspěšně!</h1>
            <p>Děkujeme za váš nákup v Loučném obchůdku.</p>
            <?php if (isset($order)): ?>
                <p>Číslo objednávky: <strong><?= htmlspecialchars($order['order_number']) ?></strong></p>
            <?php endif; ?>
            <p>Potvrzení jsme odeslali na váš email.</p>
            
        <?php elseif ($status === 'pending' || $status === 'WAITING'): ?>
            <div class="payment-icon pending">⏰</div>
            <h1>Čekáme na platbu</h1>
            <p>Vaše platba je ve zpracování.</p>
            <p>Jakmile bude platba potvrzena, pošleme vám email.</p>
            
        <?php else: ?>
            <div class="payment-icon failed">✕</div>
            <h1>Platba se nezdařila</h1>
            <p>Bohužel při zpracování platby došlo k chybě.</p>
            <p>Prosím zkuste to znovu nebo nás kontaktujte.</p>
        <?php endif; ?>

        <div style="margin-top: 30px;">
            <a href="/obchod" class="btn btn-primary">Zpět do obchodu</a>
        </div>
    </div>

    <footer class="shop-footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2025 Nech Mě Růst z.s.</p>
            </div>
        </div>
    </footer>
</body>
</html>
