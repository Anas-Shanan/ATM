<?php
define('SESSION_TIMEOUT', 15 * 60);

function validate_session_start()
{
    session_start();

    // 1 Redirect immediately if not logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // 2 Check session timeout
    $last = $_SESSION['last_activity'] ?? time();
    if (time() - $last > SESSION_TIMEOUT) {

      
        session_unset();
        session_destroy();

        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        
        header("Location: login.php?reason=expired");
        exit();
    }

    //  Update last activity timestamp
    $_SESSION['last_activity'] = time();
}