<?php
session_start();
require_once 'db_connectie.php';

// Maak verbinding met de database
$db = maakVerbinding();

// Haal alle producten op zonder de 'image' kolom
$query = 'SELECT name, price FROM Product'; 
$data = $db->query($query);

$product_cards = '';

// Tel het aantal producten in het winkelmandje
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

while ($rij = $data->fetch(PDO::FETCH_ASSOC)) {
    $name = htmlspecialchars($rij['name']);
    $price = htmlspecialchars($rij['price']);

    $product_cards .= "
    <div class='product-card'>
        <h3 class='product-name'>$name</h3>
        <p class='product-price'>€$price</p>
        <form action='winkelmandje.php' method='POST'>
            <input type='hidden' name='name' value='$name'>
            <input type='hidden' name='price' value='$price'>
            <input type='hidden' name='action' value='add'>
            <button type='submit' class='order-button'>Bestel nu</button>
        </form>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f8f8;
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
        .buttons {
            display: flex;
            align-items: center;
        }
        .buttons a {
            padding: 10px 15px;
            background-color: white;
            color: #4CAF50;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
        }
        .buttons a:hover {
            background-color: #ddd;
        }
        .cart-icon {
            font-size: 24px;
            color: white;
            margin-left: 20px;
            position: relative;
        }
        .cart-count {
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
        }
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
            flex-grow: 1;
        }
        .product-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .product-name {
            font-size: 1.2em;
            margin: 10px 0;
        }
        .product-price {
            font-size: 1.1em;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .order-button {
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .order-button:hover {
            background-color: #45a049;
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
    <title>Home - Pizzeria Sole Machina</title>
</head>
<body>
    <div class="banner">
        Welkom bij Pizzeria Sole Machina
    </div>
    <div class="header">
        <h1>Menukaart</h1>
        <div class="buttons">
            <a href="inlogpagina.php">Inloggen</a>
            <a href="registratie.php">Registreren</a>
            <a href="winkelmandje.php" class="cart-icon">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-count"><?php echo $cart_count; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
    <div class="products">
        <?php echo $product_cards; ?>
    </div>
    <div class="footer">
        <div class="footer-links">
            <a href="#">Wie zijn wij</a>
            <a href="#">Vacatures</a>
            <a href="#">Betalen</a>
            <a href="#">Voorwaarden</a>
        </div>
        &copy; 2023 Pizzeria Sole Machina. Alle rechten voorbehouden.
    </div>
</body>
</html>