<?php
session_start();
require_once 'db_connectie.php';

// Controleer of de gebruiker is ingelogd en de juiste rol heeft
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Personnel') {
    header('Location: inloggen.php'); // Redirect naar de inlogpagina indien niet ingelogd
    exit;
}

$db = maakVerbinding();

// Voorbereiding voor zoekopdracht
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search'];
}

// Haal actieve bestellingen op met details, zoek alleen op bestelling ID
$active_orders = [];
if ($search_query !== '') {
    $stmt = $db->prepare("
        SELECT o.order_id, o.client_name, o.datetime, o.status, o.address
        FROM Pizza_Order o
        WHERE o.order_id = :search
    ");
    $stmt->execute(['search' => $search_query]);
    $active_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $active_orders = $db->query("
        SELECT o.order_id, o.client_name, o.datetime, o.status, o.address
        FROM Pizza_Order o
    ")->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $stmt = $db->prepare("UPDATE Pizza_Order SET status = :status WHERE order_id = :id");
    $stmt->execute(['status' => $new_status, 'id' => $order_id]);

    header('Location: employee.php');
    exit;
}

$order_products = [];
foreach ($active_orders as $order) {
    $stmt = $db->prepare("SELECT product_name, quantity FROM Pizza_Order_Product WHERE order_id = :order_id");
    $stmt->execute(['order_id' => $order['order_id']]);
    $order_products[$order['order_id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f4f4f4;
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
        .buttons {
            display: flex;
            align-items: center;
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
        .logout-button {
            margin-left: 10px;
        }
        .search-bar {
            margin: 20px 0;
            text-align: center;
        }
        .order-table {
            margin: 20px;
            padding: 10px;
            background: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: calc(100% - 40px); 
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
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
    </style>
</head>
<body>
    <div class="banner">
        Welkom bij Pizzeria Sole Machina
    </div>
    <div class="header">
        <h1>Actieve Bestellingen</h1>
        <div class="user-info">Ingelogd als: <?php echo htmlspecialchars($_SESSION['first_name']); ?></div>
        <div class="buttons">
            <a href="logout.php">Uitloggen</a>
        </div>
    </div>

    <div class="search-bar">
        <form method="POST" action="">
            <input type="text" name="search" placeholder="Zoek op bestelling ID" value="<?php echo htmlspecialchars($search_query); ?>">
            <input type="submit" value="Zoek">
        </form>
    </div>

    <div class="order-table">
        <table>
            <thead>
                <tr>
                    <th>Bestelling ID</th>
                    <th>Klant</th>
                    <th>Datum</th>
                    <th>Status</th>
                    <th>Adres</th>
                    <th>Producten</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($active_orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($order['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['datetime']); ?></td>
                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                        <td>
                            <ul>
                                <?php foreach ($order_products[$order['order_id']] as $product): ?>
                                    <li><?php echo htmlspecialchars($product['product_name']) . ' (Aantal: ' . htmlspecialchars($product['quantity']) . ')'; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </td>
                        <td>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['order_id']); ?>">
                                <select name="status" required>
                                    <option value="1" <?php echo $order['status'] == 1 ? 'selected' : ''; ?>>Voorbereiden</option>
                                    <option value="2" <?php echo $order['status'] == 2 ? 'selected' : ''; ?>>Klaar voor bezorging</option>
                                    <option value="3" <?php echo $order['status'] == 3 ? 'selected' : ''; ?>>Onderweg</option>
                                    <option value="4" <?php echo $order['status'] == 4 ? 'selected' : ''; ?>>Bestelling is bezorgd</option>
                                </select>
                                <input type="submit" value="Wijzig Status">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <div class="footer-links">
            <a href="#">Wie zijn wij</a>
            <a href="#">Vacatures</a>
            <a href="#">Betalen</a>
            <a href="privacy.php">Voorwaarden</a>
        </div>
        &copy; 2025 Pizzeria Sole Machina. Alle rechten voorbehouden.
    </div>
</body>
</html>