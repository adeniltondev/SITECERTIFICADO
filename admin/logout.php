<?php
require_once '../config/database.php';

// Destruir sessão
session_destroy();

// Redirecionar para login
header('Location: login.php');
exit;
?>
