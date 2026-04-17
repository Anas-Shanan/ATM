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
        verify_csrf();

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
            $remaining = max(0, 5 - $attempts);

            if ($remaining > 0) {
                $error = "Invalid card or PIN. {$remaining} attempts remaining ";
            } else {
                $error = "Too many failed attempts. try again in 5 minutes";
            }
        }
    }
}
$page_title = "Login – Geldautomat";

$page_script = <<<'JS'
<script>
    // Tracks which field is active and the raw card digits
    let activeField = 'card';
    let cardDigits  = '';
 
    function addKey(d) {
        if (activeField === 'card') {
            console.log("active field", activeField);
            const display = document.getElementById('card_display');
            const real    = document.getElementById('card_number_real');
            if (!display || !real) return;
            if (!/[0-9]/.test(d) || cardDigits.length >= 16) return;
 
            cardDigits += d;
 
            let masked = '';
            for (let i = 0; i < cardDigits.length; i++) {
                if (i > 0 && i % 4 === 0) masked += ' ';
                masked += (i >= 12) ? cardDigits[i] : '*';
            }
            display.value = masked;
            real.value    = cardDigits;
 
            // auto-jump to PIN once 16 digits entered
            if (cardDigits.length === 16) {
                activeField = 'pin';
                console.log("active field pin", activeField);
                document.getElementById('pin').focus();
            }
        } else {
            const pin = document.getElementById('pin');
            if (!pin) return;
            if (!/[0-9]/.test(d) || pin.value.length >= 6) return;
            pin.value += d;
        }
    }
 
    function correctKey() {
        if (activeField === 'card') {
            const display = document.getElementById('card_display');
            const real    = document.getElementById('card_number_real');
            if (!display || !real) return;
            cardDigits = cardDigits.slice(0, -1);
 
            let masked = '';
            for (let i = 0; i < cardDigits.length; i++) {
                if (i > 0 && i % 4 === 0) masked += ' ';
                masked += (i >= 12) ? cardDigits[i] : '*';
            }
            display.value = masked;
            real.value    = cardDigits;
        } else {
            const pin = document.getElementById('pin');
            if (pin) pin.value = pin.value.slice(0, -1);
        }
    }
 
    function confirmKey() {
        if (activeField === 'card' && cardDigits.length === 16) {
            activeField = 'pin';
            document.getElementById('pin').focus();
        } else {
            document.getElementById('loginForm').submit();
        }
    }
</script>
JS;

// ============================================================
// 3. OPEN ATM SHELL
// ============================================================
require 'includes/atm_head.php';
?>



<div class="screen-inner screen_title"> 

    <h1>User Login</h1>

    <?php if (isset($_SESSION['flash_success'])): ?>
        <p style="color:green;"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <p style="color:red;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- URL flag -->
    <?php
    if (isset($_GET['reason']) && $_GET['reason'] === 'expired') {
        echo "<div class='error'>Your session expired due to inactivity. Please log in again.</div>";
        header("Location: login.php");
        exit();
    }
    ?>

    <?php if (!empty($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="login.php" method="POST" id="loginForm">
        <?php csrf_field(); ?>

        <label>
            Card Number:
            <input type="text" id="card_display" maxlength="19" placeholder="16 digits card number" inputmode="numeric" autocomplete="off" required>
            <input type="hidden" id="card_number_real" name="card_number">

        </label>
        <br><br>

        <label>
            PIN Number:
            <input type="password" id="pin" name="pin" maxlength="6" placeholder="6-digit password" inputmode="numeric" autocomplete="off" required>
        </label>
        <br><br>

        <button type="submit">Login</button>
    </form>
    <a href="registration.php">New Account</a>

</div>

<?php require 'includes/atm_foot.php'; ?>