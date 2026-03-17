<?php
define('SESSION_TIMEOUT', 15 * 60);

function secure_session_start()
{
    session_start();

    if (isset($_SESSION['user_id'])) {
        $last = $_SESSION['last_activity'] ?? time();

        if (time() - $last > SESSION_TIMEOUT) {

            session_unset();
            session_destroy();
            session_start();
            $_SESSION['flash_error'] = "Your session expired due to inactivity. Please log in again.";
            header("Location: login.php");
            exit();
        }
        $_SESSION['last_activity'] = time();
    }
}
