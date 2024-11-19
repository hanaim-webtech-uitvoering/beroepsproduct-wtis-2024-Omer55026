<?php
session_start();

require_once 'db_connectie.php';

// Controleer of de gebruiker is ingelogd en de juiste rol heeft
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Personnel') {
    header('Location: inloggen.php'); // Redirect naar de inlogpagina indien niet ingelogd
    exit;
}

$db = maakVerbinding();

// Haal actieve bestellingen op met details
$active_orders = $db->query("
    SELECT o.order_id, o.client_name, o.datetime, o.status, o.address, op.quantity, op.product_name
    FROM Pizza_Order o
    LEFT JOIN Pizza_Order_Product op ON o.order_id = op.order_id
")->fetchAll(PDO::FETCH_ASSOC);

// Verwerk statuswijziging van een bestelling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $stmt = $db->prepare("UPDATE Pizza_Order SET status = :status WHERE order_id = :id");
    $stmt->execute(['status' => $new_status, 'id' => $order_id]);

    header('Location: ingelogdpersoneel.php'); // Herlaad de pagina na statuswijziging
    exit;
}

// Verwerk het toevoegen van een nieuw product aan een bestelling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $order_id = $_POST['order_id'];
    $product_name = $_POST['product_name'];
    $quantity = $_POST['quantity'];

    // Controleer of productnaam en hoeveelheid zijn ingevuld en geldig zijn
    if (!empty($product_name) && is_numeric($quantity) && $quantity > 0) {
        $stmt = $db->prepare("INSERT INTO Pizza_Order_Product (order_id, product_name, quantity) VALUES (:order_id, :product_name, :quantity)");
        $stmt->execute(['order_id' => $order_id, 'product_name' => $product_name, 'quantity' => $quantity]);
    }

    header('Location: ingelogdpersoneel.php'); // Herlaad de pagina na het toevoegen van een product
    exit;
}

// Verwerk het verwijderen van een product uit een bestelling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product'])) {
    $product_id = $_POST['product_id'];

    $stmt = $db->prepare("DELETE FROM Pizza_Order_Product WHERE id = :id");
    $stmt->execute(['id' => $product_id]);

    header('Location: ingelogdpersoneel.php'); // Herlaad de pagina na het verwijderen van een product
    exit;
}

// Haal beschikbare producten op voor het toevoegen
$products = $db->query("SELECT name FROM Product")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingelogd als Personeel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #333;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background: #fff;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        form {
            display: inline;
        }
        .logout-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #d9534f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .add-product-form {
            margin-top: 20px;
            padding: 10px;
            background: #e7f3fe;
            border: 1px solid #b3c7e6;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Welkom, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
    <h2>Actieve Bestellingen</h2>
    <form method="POST" action="logout.php">
        <input type="submit" class="logout-button" value="Uitloggen">
    </form>
    <ul>
        <?php foreach ($active_orders as $order): ?>
            <li>
                Bestelling ID: <?php echo htmlspecialchars($order['order_id']); ?> - 
                Klant: <?php echo htmlspecialchars($order['client_name']); ?> - 
                Datum: <?php echo htmlspecialchars($order['datetime']); ?> - 
                Adres: <?php echo htmlspecialchars($order['address']); ?> - 
                
                <ul>
                    <?php if (isset($order['product_name'])): ?>
                        <li>Product: <?php echo htmlspecialchars($order['product_name']); ?> - Hoeveelheid: <?php echo htmlspecialchars($order['quantity']); ?></li>
                    <?php else: ?>
                        <li>Geen producten in deze bestelling.</li>
                    <?php endif; ?>
                </ul>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                    <select name="status">
                        <option value="1" <?php echo $order['status'] == 1 ? 'selected' : ''; ?>>Voorbereiden</option>
                        <option value="2" <?php echo $order['status'] == 2 ? 'selected' : ''; ?>>Voltooid</option>
                    </select>
                    <input type="submit" value="Wijzig Status">
                </form>
                <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($order['product_name'] ?? ''); ?>">
                    <input type="submit" name="remove_product" value="Verwijder Product">
                </form>
                <div class="add-product-form">
                    <h3>Voeg Product Toe</h3>
                    <form method="POST">
                        <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                        <select name="product_name" required>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo htmlspecialchars($product); ?>"><?php echo htmlspecialchars($product); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="quantity" placeholder="Hoeveelheid" required min="1">
                        <input type="submit" name="add_product" value="Toevoegen">
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <form method="POST" action="logout.php">
        <input type="submit" class="logout-button" value="Uitloggen">
    </form>
</body>
</html>