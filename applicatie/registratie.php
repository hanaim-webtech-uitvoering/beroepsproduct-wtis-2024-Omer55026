<?php
session_start();
require_once 'db_connectie.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
    $accept_terms = isset($_POST['accept_terms']); // Vinkknop voor voorwaarden
    $role = 'Client'; // Standaard rol is Client

    // Validatie
    $errors = [];
    if (empty($username) || empty($password) || empty($first_name) || empty($last_name)) {
        $errors[] = 'Vul alle verplichte velden in.';
    }

    // Controleer of de gebruikersnaam al bestaat
    $db = maakVerbinding();
    $stmt = $db->prepare('SELECT COUNT(*) FROM [User] WHERE username = :username');
    $stmt->execute(['username' => $username]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Gebruikersnaam is al in gebruik.';
    }

    // Controleer of de voorwaarden zijn geaccepteerd
    if (!$accept_terms) {
        $errors[] = 'Je moet de algemene privacyvoorwaarden accepteren.';
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare('INSERT INTO [User] (username, password, first_name, last_name, address, role) VALUES (:username, :password, :first_name, :last_name, :address, :role)');
        $stmt->execute([
            'username' => $username,
            'password' => $hashed_password, 
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $address,
            'role' => $role   
        ]);

        // Succesbericht met link naar inlogpagina
        $_SESSION['success_message'] = 'Gebruiker succesvol aangemaakt! Je kunt nu inloggen: <a href="inlogpagina.php">Inloggen</a>';
        header('Location: registratie.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registratie</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            margin-bottom: 20px; /* Ruimte tussen de banner en de registratiecontainer */
        }
        .registration-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            flex-grow: 1; /* Zorg ervoor dat de container de beschikbare ruimte opvult */
        }
        .registration-container h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #4CAF50;
        }
        .error {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px; /* Ruimte onder het succesbericht */
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .terms-container {
            margin: 10px 0;
            text-align: center;
        }
        .terms-container input[type="checkbox"] {
            margin-right: 5px;
        }
        .terms-container label {
            cursor: pointer;
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
    <div class="registration-container">
        <h2>Registratie</h2>
        <?php if (!empty($errors)): ?>
            <div class="error"><?php echo htmlspecialchars(implode('<br>', $errors), ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success"><?php echo $_SESSION['success_message']; ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Gebruikersnaam" required>
            <input type="password" name="password" placeholder="Wachtwoord" required>
            <input type="text" name="first_name" placeholder="Voornaam" required>
            <input type="text" name="last_name" placeholder="Achternaam" required>
            <input type="text" name="address" placeholder="Adres">
            <div class="terms-container">
                <input type="checkbox" name="accept_terms" id="accept_terms" required>
                <label for="accept_terms">Ik accepteer de <a href="privacy.php">algemene privacyvoorwaarden</a></label>
            </div>
            <input type="submit" value="Registreren">
        </form>
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