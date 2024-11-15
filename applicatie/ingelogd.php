<?php
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: homepagina.php'); // Redirect naar inlogpagina als je niet bent ingelogd
    exit;
}

// Hier kun je eventueel winkelmandje-inhoud ophalen uit de sessie of database
$winkelmandje = isset($_SESSION['winkelmandje']) ? $_SESSION['winkelmandje'] : [];
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
        .header {
            margin-bottom: 20px;
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
        <div class="header">
            <h1>Welkom, <?php echo $_SESSION['first_name'] . ' ' . $_SESSION['last_name']; ?>!</h1>
            <p>Je bent ingelogd als: <strong><?php echo $_SESSION['role']; ?></strong></p>
            <form method="POST" action="logout.php">
                <input type="submit" class="logout-button" value="Uitloggen">
            </form>
        </div>

        <div class="winkelmandje">
            <h2>Winkelmandje</h2>
            <?php if (empty($winkelmandje)): ?>
                <p>Je winkelmandje is leeg.</p>
            <?php else: ?>
                <?php foreach ($winkelmandje as $item): ?>
                    <div class="winkelmandje-item">
                        <?php echo htmlspecialchars($item); // Veiligheid bij het weergeven van gebruikersinvoer ?> 
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>