<?php
session_start();
require_once 'config/db.php';
require_once 'config/security.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card = $_POST['card_number'];
    $pin =  $_POST['pin'];

    if (is_locked_out($card)) {
        $min = lockout_remaining($card);
        $error = "Your card is locked due to login failed attempts . try again in {$min} minutes";
        log_action($conn, null, 'Card-locked', null, $card);
    } else {


        $stmt = $conn->prepare("SELECT * FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card);

        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();


        if ($user && password_verify($pin . PEPPER, $user['pin_hash'])) {

            reset_attempts($card);  // reset the attempts on success  
            log_action($conn, $user['id'], 'login_success', null, $card);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['card'] = $user['card_number'];
            $_SESSION['last_activity'] = time();

            header("location: dashboard.php");

            exit;
        } else {
            log_action($conn, null, 'login_failed', null, $card);
            failed_attempt($card);
            $attempts = $_SESSION["login_attempts_" . $card] ?? 0;
            $remaining = 5 - $attempts;
            if ($remaining > 0) {
                $error = "Invalid card or PIN. {$remaining} attempts remaining ";
            } else {
                $error = "Too many failed attempts. try again in 5 minutes";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bankautomat login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <h1>User Login</h1>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <p style="color:green;"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <p style="color:red;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST">

        <label>
            Card Number:
            <input type="text" id="card_display" maxlength="19" placeholder="16 digits card number" inputmode="numeric" autocomplete="off" required>
            <input type="hidden" id="card_number_real" name="card_number">

        </label>
        <br><br>

        <label>
            PIN Number:
            <input type="password" name="pin" maxlength="6" placeholder="6-digit password" inputmode="numeric" autocomplete="off" required>
        </label>
        <br><br>

        <button type="submit">Login</button>
    </form>
    <button><a href="registration.php">Create Account</a></button>

</body>

</html>
<script>
    // used AI here
    const display = document.getElementById('card_display');
    const real = document.getElementById('card_number_real');

    let digits = ''; // store real digits HERE, not in a hidden input

    display.addEventListener('keydown', (e) => {
        if (e.key >= '0' && e.key <= '9' && digits.length < 16) {
            digits += e.key;
        } else if (e.key === 'Backspace') {
            digits = digits.slice(0, -1);
        } else {
            e.preventDefault();
            return;
        }

        // Now build masked display from clean digits
        let masked = '';
        for (let i = 0; i < digits.length; i++) {
            if (i > 0 && i % 4 === 0) masked += ' ';
            masked += (i >= 12) ? digits[i] : '*';
        }
        display.value = masked;
        real.value = digits; // store clean digits
        e.preventDefault(); // prevent default so we control display
    });
</script>