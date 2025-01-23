<?php
session_start();

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $product = [
        'name' => htmlspecialchars(trim($_POST['name'])),
        'price' => floatval(trim($_POST['price']))
    ];
    $_SESSION['cart'][] = $product;
}

if (isset($_GET['remove'])) {
    $removeIndex = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$removeIndex])) {
        unset($_SESSION['cart'][$removeIndex]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
}

function calculateTotal($cart) {
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'];
    }
    return $total;
}

$totalPrice = calculateTotal($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelmandje</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .banner {
            background-color: #4CAF50;
            height: 150px;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            font-size: 2em;
        }
        .header {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
        }
        .header h1 {
            margin: 0;
            flex-grow: 1;
        }
        .cart-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .total {
            font-weight: bold;
            margin-top: 20px;
            text-align: right;
        }
        .checkout-button {
            display: block;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 20px;
        }
        .checkout-button:hover {
            background-color: #45a049;
        }
        .remove-button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .remove-button:hover {
            background-color: #d32f2f;
        }
        .footer {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 20px 0;
            width: 100%;
        }
        .footer-links {
            margin: 10px 0;
        }
        .footer-links a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
        }
        .footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="banner">
        Welkom bij Pizzeria Sole Machina
    </div>
    <div class="header">
        <h1>Menukaart</h1>
    </div>
    <div class="cart-container">
        <h2>Je Winkelmandje</h2>
        <?php if (empty($_SESSION['cart'])): ?>
            <p>Je winkelmandje is leeg.</p>
        <?php else: ?>
            <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                <div class="cart-item">
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span>€<?php echo number_format($item['price'], 2); ?></span>
                    <form action="?remove=<?php echo $index; ?>" method="GET" style="display:inline;">
                        <button type="submit" class="remove-button">Verwijder</button>
                    </form>
                </div>
            <?php endforeach; ?>
            <div class="total">Totaal: €<?php echo number_format($totalPrice, 2); ?></div>
            <a href="loginpage.php" class="checkout-button">Afrekenen</a>
        <?php endif; ?>
    </div>
</body>
</html>