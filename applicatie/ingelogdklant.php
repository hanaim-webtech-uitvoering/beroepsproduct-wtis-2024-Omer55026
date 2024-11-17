<?php
session_start();
require_once 'db_connectie.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Client') {
    header('Location: inloggen.php');
    exit;
}

$db = maakVerbinding();
$products = $db->query('SELECT * FROM product')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_ids = isset($_SESSION['order_items']) ? $_SESSION['order_items'] : [];
    $item_ids[] = $_POST['item_id'];
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
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingelogd als Klant</title>
</head>
<body>
    <h1>Welkom, <?php echo $_SESSION['first_name']; ?>!</h1>
    <h2>Menu</h2>
    <div>
        <?php foreach ($products as $product): ?>
            <div>
                <?php echo htmlspecialchars($product['name']); ?> - €<?php echo number_format($product['price'], 2); ?>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="item_id" value="<?php echo $product['type_id']; ?>">
                    <input type="submit" value="Voeg toe aan bestelling">
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <h2>Bestelling</h2>
    <ul>
        <?php if (isset($_SESSION['order_items'])): ?>
            <?php foreach ($_SESSION['order_items'] as $id): ?>
                <li><?php echo htmlspecialchars($id); ?></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Geen items in je bestelling.</li>
        <?php endif; ?>
    </ul>
    <form method="POST">
        <input type="submit" name="confirm_order" value="Bevestig Bestelling">
    </form>
</body>
</html>