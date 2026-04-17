<?php

require_once 'config/db.php';
require_once 'config/security.php';

$user_id = $_SESSION['user_id'] ?? null;
$card = $_SESSION['card'] ?? null;

log_action($conn, $user_id, 'logout_success', null, $card);

session_unset();
session_destroy();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),  // usually PHPSESSID
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}
header("Location: login.php");
exit();
?>
