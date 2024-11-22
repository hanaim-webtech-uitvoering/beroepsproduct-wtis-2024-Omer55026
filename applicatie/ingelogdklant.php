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
            <input type='hidden' name='item_id' value='$name'>
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
    // Haal gebruikersinformatie
    $username = $_SESSION['username'];
    $full_name = htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
    $address = isset($_POST['address']) ? htmlspecialchars($_POST['address']) : 'Onbekend'; // Haal het adres op
    $personnel_username = 'rdeboer'; // Zorg ervoor dat deze waarde bestaat in de User tabel
    $datetime = date('Y-m-d H:i:s');
    $status = 0; // Assuming 0 is for 'pending'

    // Insert order into Pizza_Order
    $stmt = $db->prepare("INSERT INTO Pizza_Order (client_username, client_name, personnel_username, datetime, status, address) VALUES (:username, :client_name, :personnel_username, :datetime, :status, :address)");
    
    try {
        $stmt->execute([
            'username' => $username,
            'client_name' => $full_name,
            'personnel_username' => $personnel_username,
            'datetime' => $datetime,
            'status' => $status,
            'address' => $address
        ]);
    } catch (PDOException $e) {
        echo "Fout bij het invoegen van de bestelling: " . $e->getMessage();
        exit;
    }

    // Get the last inserted order ID
    $order_id = $db->lastInsertId();

    // Insert products into Pizza_Order_Product met hoeveelheden
    foreach ($_SESSION['order_items'] as $item_name) {
        // Haal de hoeveelheid op uit de POST-gegevens
        $quantity = isset($_POST['quantity'][$item_name]) ? (int)$_POST['quantity'][$item_name] : 1; // Standaard naar 1
        $stmt = $db->prepare("INSERT INTO Pizza_Order_Product (order_id, product_name, quantity) VALUES (:order_id, :product_name, :quantity)");
        $stmt->execute([
            'order_id' => $order_id,
            'product_name' => $item_name,
            'quantity' => $quantity
        ]);
    }

    // Leeg de sessievariabelen
    unset($_SESSION['order_items']); 
    unset($_SESSION['order_quantities']); // Clear the quantities from session
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

// Haal bestelgeschiedenis op
class OrderHistory {
    private $db;
    private $username;

    public function __construct($db, $username) {
        $this->db = $db;
        $this->username = $username;
    }

    public function getOrders() {
        $query = $this->db->prepare("SELECT * FROM Pizza_Order WHERE client_username = :username ORDER BY datetime DESC");
        $query->execute(['username' => $this->username]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderProducts($order_id) {
        $stmt = $this->db->prepare("SELECT product_name, quantity FROM Pizza_Order_Product WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$orderHistory = new OrderHistory($db, $_SESSION['username']);
$orders = $orderHistory->getOrders();
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
        .order-history {
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
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
        <form method="POST">
            <ul>
                <?php if (isset($_SESSION['order_items']) && !empty($_SESSION['order_items'])): ?>
                    <?php foreach ($_SESSION['order_items'] as $id): ?>
                        <li>
                            Product: <?php echo htmlspecialchars($id); ?>
                            <label for="quantity_<?php echo htmlspecialchars($id); ?>">Aantal:</label>
                            <input type="number" name="quantity[<?php echo htmlspecialchars($id); ?>]" id="quantity_<?php echo htmlspecialchars($id); ?>" min="1" value="<?php echo $_SESSION['order_quantities'][$id] ?? 1; ?>" style="width: 50px;">
                            <input type="hidden" name="remove_item_id" value="<?php echo htmlspecialchars($id); ?>">
                            <button type="submit" class="remove-button" name="remove">Verwijder</button>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>Geen items in je bestelling.</li>
                <?php endif; ?>
            </ul>
            <?php if (isset($_SESSION['order_items']) && !empty($_SESSION['order_items'])): ?>
                <label for="address">Afleveradres:</label>
                <input type="text" name="address" id="address" required>
                <input type="submit" name="confirm_order" value="Bevestig Bestelling" class="order-button">
            <?php endif; ?>
        </form>
    </div>

    <!-- Bestelgeschiedenis sectie -->
    <div class="order-history">
        <h2>Bestelgeschiedenis</h2>
        <?php if ($orders): ?>
            <table>
                <thead>
                    <tr>
                        <th>Bestelling ID</th>
                        <th>Datum</th>
                        <th>Status</th>
                        <th>Adres</th>
                        <th>Producten</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($order['datetime']); ?></td>
                            <td><?php echo htmlspecialchars($order['status']); ?></td>
                            <td><?php echo htmlspecialchars($order['address']); ?></td>
                            <td>
                                <ul>
                                    <?php
                                    // Haal de producten voor deze bestelling op
                                    $order_id = $order['order_id'];
                                    $products = $orderHistory->getOrderProducts($order_id);
                                    foreach ($products as $product):
                                    ?>
                                        <li><?php echo htmlspecialchars($product['product_name']) . ' (Aantal: ' . htmlspecialchars($product['quantity']) . ')'; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Geen eerdere bestellingen gevonden.</p>
        <?php endif; ?>
    </div>

    <div class="footer">
        <div class="footer-links">
            <a href="#">Wie zijn wij</a>
            <a href="#">Vacatures</a>
            <a href="#">Betalen</a>
            <a href="#">Voorwaarden</a>
        </div>
        &copy; 2025 Pizzeria Sole Machina. Alle rechten voorbehouden.
    </div>
</body>
</html>