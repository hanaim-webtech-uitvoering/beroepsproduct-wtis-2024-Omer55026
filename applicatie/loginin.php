<?php
session_start();
require_once 'db_connectie.php';

if (!isset($_SESSION['username'])) {
    header('Location: ingelogd.php'); // Redirect naar inlogpagina als je niet bent ingelogd
    exit;
}

$db = maakVerbinding();

// Beheer het menu en bestellingen
$role = $_SESSION['role'];
$products = [];

// Haal de producten op uit de bestaande tabel
$stmt = $db->query('SELECT * FROM products'); // Zorg ervoor dat de tabelnaam correct is
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verwerken van een bestelaanvraag door een klant
if ($role === 'client' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_ids = isset($_SESSION['order_items']) ? $_SESSION['order_items'] : [];
    $item_ids[] = $_POST['item_id'];
    $_SESSION['order_items'] = $item_ids;
}

// Verwerken van een bestelling
if ($role === 'client' && isset($_POST['confirm_order'])) {
    $item_ids = implode(',', $_SESSION['order_items']);
    $stmt = $db->prepare("INSERT INTO orders (username, item_ids) VALUES (:username, :item_ids)");
    $stmt->execute(['username' => $_SESSION['username'], 'item_ids' => $item_ids]);
    unset($_SESSION['order_items']); // Reset de bestelling
    header('Location: ingelogd.php'); // Terug naar de ingelogde pagina
    exit;
}

// Voor personeel: Haal actieve bestellingen op
$active_orders = [];
if ($role === 'personeel') {
    $stmt = $db->query("SELECT * FROM orders WHERE status = 'Pending'");
    $active_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Wijzig de status van een bestelling
if ($role === 'personeel' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $new_status, 'id' => $order_id]);
    header('Location: ingelogd.php'); // Reload de pagina
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingelogd</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 50px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .logout-button {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        .menu-item {
            margin: 10px 0;
        }
        .winkelmandje {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 15px;
        }
        .winkelmandje h2 {
            margin-bottom: 10px;
        }
        .winkelmandje-item {
            padding: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welkom, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>!</h1>
        <p>Je bent ingelogd als: <strong><?php echo $_SESSION['role']; ?></strong></p>
        <form method="POST" action="logout.php">
            <input type="submit" class="logout-button" value="Uitloggen">
        </form>

        <?php if ($role === 'client'): ?>
            <h2>Menu</h2>
            <div>
                <?php foreach ($products as $product): ?>
                    <div class="menu-item">
                        <?php echo htmlspecialchars($product['name']); ?> - €<?php echo number_format($product['price'], 2); ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="item_id" value="<?php echo $product['id']; ?>">
                            <input type="submit" value="Voeg toe aan bestelling">
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2>Bestelling</h2>
            <ul class="winkelmandje">
                <?php if (isset($_SESSION['order_items'])): ?>
                    <?php foreach ($_SESSION['order_items'] as $id): ?>
                        <li class="winkelmandje-item"><?php echo htmlspecialchars($id); ?></li> <!-- Hier zou je de naam van het item willen tonen -->
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="winkelmandje-item">Geen items in je bestelling.</li>
                <?php endif; ?>
            </ul>
            <form method="POST">
                <input type="submit" name="confirm_order" value="Bevestig Bestelling">
            </form>
        
        <?php elseif ($role === 'personeel'): ?>
            <h2>Actieve Bestellingen</h2>
            <ul>
                <?php foreach ($active_orders as $order): ?>
                    <li>
                        Bestelling ID: <?php echo $order['id']; ?> - Status: <?php echo $order['status']; ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <option value="Preparing">Voorbereiden</option>
                                <option value="Completed">Voltooid</option>
                            </select>
                            <input type="submit" value="Wijzig Status">
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>