<?php
session_start();
require_once 'db_connectie.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $address = trim($_POST['address']);
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

    if (empty($errors)) {
        // Wachtwoord hashen
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Invoegen in de database
        $stmt = $db->prepare('INSERT INTO [User] (username, password, first_name, last_name, address, role) VALUES (:username, :password, :first_name, :last_name, :address, :role)');
        $stmt->execute([
            'username' => $username,
            'password' => $password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $address,
            'role' => $role
        ]);

        // Succesbericht
        $_SESSION['success_message'] = 'Gebruiker succesvol aangemaakt! Je kunt nu inloggen.';
        header('Location: inlogpagina.php');
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
            padding: 50px;
        }
        .registration-container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .registration-container h2 {
            margin-bottom: 20px;
        }
        .error {
            color: red;
        }
        input[type="text"], input[type="password"], input[type="email"] {
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
    </style>
</head>
<body>
    <div class="registration-container">
        <h2>Registratie</h2>
        <?php if (!empty($errors)): ?>
            <div class="error"><?php echo htmlspecialchars(implode('<br>', $errors), ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Gebruikersnaam" required>
            <input type="password" name="password" placeholder="Wachtwoord" required>
            <input type="text" name="first_name" placeholder="Voornaam" required>
            <input type="text" name="last_name" placeholder="Achternaam" required>
            <input type="text" name="address" placeholder="Adres">
            <input type="submit" value="Registreren">
        </form>
    </div>
</body>
</html>