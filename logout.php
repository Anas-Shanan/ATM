<?php
session_start();

require_once 'config/db.php';
require_once 'config/security.php';

$user_id = $_SESSION['user_id'] ?? null;
$card = $_SESSION['card'] ?? null;

log_action($conn, $user_id, 'logout_success', null, $card);

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>