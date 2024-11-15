<?php
session_start();
session_destroy();
header('Location: homepagina.php'); // Redirect naar inlogpagina na uitloggen
exit;