<?php
session_start();
require_once 'db_connectie.php';

// Maak verbinding met de database
$db = maakVerbinding();

// Haal alle producten op zonder de 'image' kolom
$query = 'SELECT name, price, type_id FROM Product'; 
$data = $db->query($query);

$product_cards = '';

// Tel het aantal producten in het winkelmandje
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

while ($rij = $data->fetch(PDO::FETCH_ASSOC)) {
    $name = htmlspecialchars($rij['name']);
    $price = htmlspecialchars($rij['price']);
    $type_id = htmlspecialchars($rij['type_id']);

    $product_cards .= "
    <div class='product-card'>
        <h3 class='product-name'>$name</h3>
        <p class='product-price'>€$price</p>
        <form action='' method='POST'>
            <input type='hidden' name='item_id' value='$type_id'>
            <button type='submit' class='order-button'>Voeg toe aan bestelling</button>
        </form>
    </div>";
}

// Voeg item toe aan de bestelling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_ids = isset($_SESSION['order_items']) ? $_SESSION['order_items'] : [];
    if (!in_array($_POST['item_id'], $item_ids)) {
        $item_ids[] = $_POST['item_id'];
    }
    $_SESSION['order_items'] = $item_ids;
}

// Bestelling bevestigen
if (isset($_POST['confirm_order'])) {
    $item_ids = implode(',', $_SESSION['order_items']);
    $stmt = $db->prepare("INSERT INTO orders (username, item_ids) VALUES (:username, :item_ids)");
    $stmt->execute(['username' => $_SESSION['username'], 'item_ids' => $item_ids]);
    unset($_SESSION['order_items']);
    header('Location: ingelogdklant.php');
    exit;
}

// Verwijder item uit de bestelling
if (isset($_POST['remove_item_id'])) {
    $item_ids = isset($_SESSION['order_items']) ? $_SESSION['order_items'] : [];
    $item_ids = array_filter($item_ids, function($id) {
        return $id !== $_POST['remove_item_id'];
    });
    $_SESSION['order_items'] = array_values($item_ids);
}

// Haal de naam en achternaam van de ingelogde gebruiker
$full_name = isset($_SESSION['first_name']) && isset($_SESSION['last_name']) ? 
    htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Gast';
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
        .user-info {
            color: white;
            margin-right: 20px;
            font-weight: bold;
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
            margin-top: auto;
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
        .order-summary {
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 20px;
        }
        .remove-button {
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            padding: 5px;
            font-weight: bold;
        }
        .remove-button:hover {
            background-color: #d32f2f;
        }
    </style>
    <title>Ingelogd als Klant - Pizzeria Sole Machina</title>
</head>
<body>
    <div class="banner">
        Welkom bij Pizzeria Sole Machina
    </div>
    <div class="header">
        <h1>Menukaart</h1>
        <div class="user-info">Ingelogd als: <?php echo $full_name; ?></div>
        <div class="buttons">
            <a href="logout.php">Uitloggen</a>
        </div>
    </div>
    <div class="products">
        <?php echo $product_cards; ?>
    </div>

    <div class="order-summary">
        <h2>Bestelling</h2>
        <ul>
            <?php if (isset($_SESSION['order_items']) && !empty($_SESSION['order_items'])): ?>
                <?php foreach ($_SESSION['order_items'] as $id): ?>
                    <li>
                        Product: <?php echo htmlspecialchars($id); ?>
                        <form action="" method="POST" style="display:inline;">
                            <input type="hidden" name="remove_item_id" value="<?php echo htmlspecialchars($id); ?>">
                            <button type="submit" class="remove-button">Verwijder</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Geen items in je bestelling.</li>
            <?php endif; ?>
        </ul>
        <?php if (isset($_SESSION['order_items']) && !empty($_SESSION['order_items'])): ?>
            <form method="POST">
                <input type="submit" name="confirm_order" value="Bevestig Bestelling" class="order-button">
            </form>
        <?php endif; ?>
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