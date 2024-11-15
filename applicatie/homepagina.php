<?php
require_once 'db_connectie.php';

// Maak verbinding met de database
$db = maakVerbinding();

// Haal alle producten op zonder de 'image' kolom
$query = 'SELECT name, price FROM Product'; 
$data = $db->query($query);

$product_cards = '';

while ($rij = $data->fetch(PDO::FETCH_ASSOC)) {
    $name = htmlspecialchars($rij['name']);
    $price = htmlspecialchars($rij['price']);

    $product_cards .= "
    <div class='product-card' onclick='addToCart(\"$name\", \"$price\")'>
        <h3 class='product-name'>$name</h3>
        <p class='product-price'>€$price</p>
        <button class='order-button'>Bestel nu</button>
    </div>";
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f8f8;
        }
        .banner {
            background-color: #4CAF50;
            height: 150px; /* Verlaagde hoogte */
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            font-size: 2em;
        }
        .header {
            position: relative; /* Nodig voor positionering van de knoppen */
            display: flex;
            align-items: center;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
        }
        .header h1 {
            margin: 0;
            flex-grow: 1; /* Zorgt ervoor dat de titel ruimte inneemt */
        }
        .buttons {
            position: absolute;
            right: 20px; /* Plaats de knoppen rechtsboven */
            top: 50%;
            transform: translateY(-50%); /* Centreer verticaal in de header */
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
            cursor: pointer;
            color: white;
            margin-left: 20px;
        }
        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        .product-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin: 10px;
            padding: 10px;
            text-align: center;
            width: 200px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
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
        .cart-popup {
            position: fixed;
            right: 20px;
            top: 100px;
            width: 300px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 15px;
            display: none;
            z-index: 1000;
        }
        .cart-popup h2 {
            margin-top: 0;
        }
        .cart-item {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .remove-button {
            background-color: red;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            padding: 5px 10px;
        }
        .remove-button:hover {
            background-color: darkred;
        }
        .total-price {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
    <title>Home - Pizzeria Sole Machina</title>
</head>
<body>
    <div class="banner">
        Welkom bij Pizzeria Sole Machina
    </div>
    <div class="header">
        <h1>Onze Pizzas</h1>
        <div class="buttons">
            <a href="loginin.php">Inloggen</a>
            <i class="fas fa-shopping-cart cart-icon" onclick="toggleCart()"></i>
        </div>
    </div>
    <div class="products">
        <?php echo $product_cards; ?>
    </div>

    <!-- Winkelmandje Popup -->
    <div class="cart-popup" id="cartPopup">
        <h2>Winkelmandje</h2>
        <div id="cartItems"></div>
        <p class="total-price" id="totalPrice">Totaal: €0.00</p>
        <button onclick="closeCart()">Sluiten</button>
    </div>

    <script>
        let cart = [];

        function addToCart(name, price) {
            cart.push({ name: name, price: parseFloat(price) });
            updateCart();
            document.getElementById('cartPopup').style.display = 'block';
        }

        function updateCart() {
            const cartItemsDiv = document.getElementById('cartItems');
            cartItemsDiv.innerHTML = '';
            let total = 0;

            cart.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'cart-item';
                itemDiv.innerText = `${item.name} - €${item.price.toFixed(2)}`;
                
                const removeButton = document.createElement('button');
                removeButton.className = 'remove-button';
                removeButton.innerText = 'Verwijder';
                removeButton.onclick = function() {
                    removeFromCart(index);
                };

                itemDiv.appendChild(removeButton);
                cartItemsDiv.appendChild(itemDiv);
                total += item.price;
            });

            document.getElementById('totalPrice').innerText = `Totaal: €${total.toFixed(2)}`;
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCart();
        }

        function closeCart() {
            document.getElementById('cartPopup').style.display = 'none';
        }

        function toggleCart() {
            const cartPopup = document.getElementById('cartPopup');
            cartPopup.style.display = cartPopup.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</body>
</html>
