<?php

function failed_attempt($card_number)
{
    $attempt_key = "login_attempts_" . $card_number;
    $time_key = "login_lockout_until_" . $card_number;

    if (!isset($_SESSION[$attempt_key])) {
        $_SESSION[$attempt_key] = 0;
    }
    $_SESSION[$attempt_key]++;

    // lock for 15 min
    if ($_SESSION[$attempt_key] >= 5) {
        $_SESSION[$time_key] = time() + (5 * 60);
        $_SESSION[$attempt_key] = 0;
    }
}


function is_locked_out($card_number)
{
    $time_key = 'login_lockout_until_' . $card_number;

    if (isset($_SESSION[$time_key])) {
        if (time() < $_SESSION[$time_key]) {
            return true;
        } else {
            // lock expired
            unset($_SESSION[$time_key]);
        }
    }

    return false;
}

function lockout_remaining($card_number)
{
    $time_key = 'login_lockout_until_' . $card_number;

    if (!isset($_SESSION[$time_key])) return 0;

    return ceil(($_SESSION[$time_key] - time()) / 60);
}

function reset_attempts($card_number)
{
    unset($_SESSION["login_attempts_" . $card_number]);
    unset($_SESSION["login_lockout_until_" . $card_number]);
}

// CSRF TOKENS ////

// genrate a token
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// call inside every form (should be hidden)
function csrf_field()
{
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

// validate the token .
//need to be at the top of every POST request

function verify_csrf()
{
    $submitted = $_POST['csrf_token'] ?? '';
    $expected  = $_SESSION['csrf_token'] ?? '';

    if (!$expected || !hash_equals($expected, $submitted)) {
        //hash_equals() does a constant-time comparison, which prevents timing attacks
        http_response_code(403);
        die("Invalid or missing CSRF token. Please go back and try again.");
    }
    // Rotate the token after use
    unset($_SESSION['csrf_token']);
}


////////// USER LOG ////////

function log_action(mysqli $conn, $user_id = null, $action = "", $amount = null, $card = null): void {
    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $conn->prepare("
        INSERT INTO user_logs (user_id, card_number, action, amount, ip_address)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issds", $user_id, $card, $action, $amount, $ip);
    $stmt->execute();
    $stmt->close();
}