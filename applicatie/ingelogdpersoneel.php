<?php
session_start();

require_once 'db_connectie.php';

// Controleer of de gebruiker is ingelogd en de juiste rol heeft
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Personnel') {
    header('Location: inloggen.php'); // Redirect naar de inlogpagina indien niet ingelogd
    exit;
}

$db = maakVerbinding();

// Haal actieve bestellingen op
$active_orders = $db->query("SELECT * FROM Pizza_Order")->fetchAll(PDO::FETCH_ASSOC);

// Verwerk statuswijziging van een bestelling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
    $stmt->execute(['status' => $new_status, 'id' => $order_id]);

    header('Location: ingelogdpersoneel.php'); // Herlaad de pagina na statuswijziging
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingelogd als Personeel</title>
</head>
<body>
    <h1>Welkom, <?php echo $_SESSION['first_name']; ?>!</h1>
    <h2>Actieve Bestellingen</h2>
    <ul>
        <?php foreach ($active_orders as $order): ?>
            <li>
                Bestelling ID: <?php echo $order['order_id']; ?> - Status: <?php echo $order['status']; ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                    <select name="status">
                        <option value="Preparing">Voorbereiden</option>
                        <option value="Completed">Voltooid</option>
                    </select>
                    <input type="submit" value="Wijzig Status">
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
    <form method="POST" action="logout.php">
                <input type="submit" class="logout-button" value="Uitloggen">
            </form>
</body>
</html>