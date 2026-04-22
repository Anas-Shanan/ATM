<?php
require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = trim($_POST['full_name']);
    $card_number = trim($_POST['card_number']);
    $pin =  trim($_POST['pin']);
    $pin_confirm = trim($_POST['pin_confirm']);

    if (empty($full_name)) {
        $errors[]  = "Full name is required.";
    }
    if (empty($card_number)) {
        $errors[] = "Card number is required.";
    } elseif (!preg_match('/^\d{16}$/', $card_number)) {
        $errors[] = "Card number must be 16 digits";
    }
    if (empty($pin)) {
        $errors[] = "Pin number is required";
    } elseif (!preg_match('/^\d{6}$/', $pin)) {
        $errors[] = "Pin number must be 6 digits";
    }
    if (empty($pin_confirm)) {
        $errors[] = "Pin confirmation is required";
    }
    if ($pin !== $pin_confirm) {
        $errors[] = "Pin numbers do not matching ";
    }
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card_number);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "This card number is already registered";
        }
        $stmt->close();
    }
    if (empty($errors)) {
        $pin_hash = password_hash($pin . PEPPER, PASSWORD_BCRYPT);

        // INSERT into users table
        $stmt = $conn->prepare("INSERT INTO users (full_name , card_number, pin_hash) VALUES (?,?,?) ");
        $stmt->bind_param("sss", $full_name, $card_number, $pin_hash);
        $stmt->execute();
        $new_user_id = $conn->insert_id;
        // or $new_user_id = $stmt->insert_id;
        $stmt->close();

        //  INSERT into accounts table
        $stmt = $conn->prepare(" INSERT INTO accounts (user_id, balance) VALUES (?, 0.00)");
        $stmt->bind_param("i", $new_user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash_success'] = 'Account created successfully! Please log in.';
        header('Location: login.php');
        exit;
    }
    $conn->close();
}

$page_title = "New Account – Geldautomat";

$page_script = <<<'JS'
<script>
        
    let activeInput = null;
    document.querySelectorAll('.screen-input').forEach(input => {
        input.addEventListener('focus', () => {
            activeInput = input;
        });
    });

    function addKey(d) {
        if (!activeInput) return;

        if (!/^\d+$/.test(d)) return;

        const id = activeInput.id;

        if (id === 'card_number') {
            if (activeInput.value.length >= 16) return;
            activeInput.value += d;
        }

        if (id === 'pin' || id === 'pin_confirm') {
            if (activeInput.value.length >= 6) return;
            activeInput.value += d;
        }
    }
    
    function correctKey() {
        if (!activeInput) return;
        activeInput.value = activeInput.value.slice(0, -1);
    }
    
    function confirmKey() {
            document.getElementById('registrationForm').submit();
        }

     document.addEventListener('DOMContentLoaded', function () {
        const btn = document.querySelector('.bt4-r');
        if (btn) {
            btn.onclick = function () {
                window.location.href = 'login.php';
            };}})

    function updateClock() {
        const el = document.getElementById('scr-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second:'2-digit' });
    }
    updateClock();
    setInterval(updateClock, 1000); 
</script>
JS;

require 'includes/atm_head.php';
?>




<div class="screen-inner ">
    <div class="scr-titlebar">
        <span class="scr-titlebar-text">NEW ACCOUNT</span>
        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <div id="scr-msg"
        class="scr-msg<?= !empty($errors) ? ' error' : '' ?>"
        style="<?= empty($errors) ? 'display:none' : '' ?>">
        <?php if (!empty($errors)): ?>
            <div>
                <?php foreach ($errors as $err): ?>
                    <p class="scr-msg.error"><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
                </div>
        <?php endif; ?>
    </div>

    <form action="registration.php" method="POST" id="registrationForm">
        <?php csrf_field(); ?>
        <label>
            Full Name:
            <input type="text" name="full_name" id="full_name" maxlength="50" placeholder="e.g. Maxi Shan" autofocus required>
        </label>

        <label>
            Card Number:
            <input type="text" name="card_number" id="card_number" class="screen-input" maxlength="16" placeholder="16 digits card number" inputmode="numeric" autocomplete="off" autofocus require>

        </label>


        <label>
            PIN Number:
            <input type="password" name="pin" id="pin" class="screen-input" maxlength="6" placeholder="6-digit PIN" inputmode="numeric" autofocus required>
        </label>

        <label>
            Confirm PIN Number:
            <input type="password" name="pin_confirm" id="pin_confirm" class="screen-input" maxlength="6" placeholder="Confirm PIN" inputmode="numeric" autofocus required>
        </label>

       
    </form>

  
     <button class="btn-LOGIN">
        <a href="login.php">You already have account -> LOGIN</a>
    </button>

 
</div>


<?php require 'includes/atm_foot.php'; ?>