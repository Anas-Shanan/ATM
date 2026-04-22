<?php
require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

validate_session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

$side_buttons = [
    'bt1-l' => ['label' => 'CUR', 'action' => 'focus:current_pin'],
    'bt1-r' => ['label' => 'NEW', 'action' => 'focus:new_pin'],
    'bt2-l' => ['label' => 'CNF', 'action' => 'focus:confirm_pin'],
    'bt2-r' => ['label' => 'GO', 'action' => 'submit'],
    'bt3-l' => ['label' => 'CLR', 'action' => 'clear'],
    'bt3-r' => ['label' => 'WDR', 'action' => 'href:withdraw.php'],
    'bt4-l' => ['label' => 'MENU', 'action' => 'href:dashboard.php'],
    'bt4-r' => ['label' => 'OUT', 'action' => 'href:logout.php'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $current_pin = $_POST['current_pin'];
    $new_pin = $_POST['new_pin'];
    $confirm_pin = $_POST['confirm_pin'];

    if (empty($current_pin) || empty($new_pin) || empty($confirm_pin)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/^\d{6}$/', $new_pin)) {

        $error = "New PIN must be exactly 6 digits.";
    } elseif ($new_pin !== $confirm_pin) {
        $error = "New PIN and confirmation do not match.";
    } elseif ($new_pin === $current_pin) {
        $error = "New PIN must be different from current PIN.";
    } else {
        $stmt = $conn->prepare("SELECT pin_hash FROM users WHERE id=?");
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$user || !password_verify($current_pin . PEPPER, $user['pin_hash'])) {

            $error = "Current PIN is incorrect. Please try again.";
        } else {

            $new_pin_hash = password_hash($new_pin . PEPPER, PASSWORD_DEFAULT);
            // update in DB

            $stmt = $conn->prepare("UPDATE users SET pin_hash = ? WHERE id=?");
            $stmt->bind_param("si", $new_pin_hash, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            $success = "PIN changed successfully!";
            log_action($conn, $_SESSION['user_id'], 'pin_change');
        }
    }
}

$page_title = "Pin Change Geldautomat";
$page_script = <<<'JS'

    <script>
    let activeInput = null;
    document.querySelectorAll('.screen-input').forEach(input => {
        input.addEventListener('focus', () => {
            activeInput = input;
        });
    });

    window.addEventListener('DOMContentLoaded', function() {
        const current = document.getElementById('current_pin');
        if (current) {
            activeInput = current;
            current.focus();
        }
    });

    function addKey(d) {
        if (!activeInput) return;

        if (!/^\d+$/.test(d)) return;

        const id = activeInput.id;

        if (id === 'current_pin' || id === 'new_pin' || id === 'confirm_pin') {
            if (activeInput.value.length >= 6) return;
            activeInput.value += d;
        }
    }
    
    function correctKey() {
        if (!activeInput) return;
        activeInput.value = activeInput.value.slice(0, -1);
    }

    function clearCurrentField() {
        if (!activeInput) return;
        activeInput.value = '';
        activeInput.focus();
    }

    function focusField(fieldId) {
        const target = document.getElementById(fieldId);
        if (!target) return;
        activeInput = target;
        target.focus();
    }

    function handleSideButtonAction(action) {
        if (!action) return false;

        if (action.startsWith('focus:')) {
            focusField(action.slice(6));
            return true;
        }

        if (action === 'clear') {
            clearCurrentField();
            return true;
        }

        if (action === 'submit') {
            document.getElementById('pin_changeForm').submit();
            return true;
        }

        return false;
    }
    
    function confirmKey() {
        document.getElementById('pin_changeForm').submit();
    }

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

<div class="screen-inner screen_title">
    <div class="scr-titlebar">
        <span class="scr-titlebar-text">PIN Change</span>
        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <?php if (!empty($error)): ?>
        <p class="scr-msg error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="scr-msg success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (empty($success)): // to hide the form after success 
    ?>

        <form action="pin_change.php" method="POST" id="pin_changeForm" class="screen-form-block">
            <?php csrf_field(); ?>
            <label>
                Current PIN number:
                <input type="password" name="current_pin" class="screen-input" id="current_pin" maxlength="6" placeholder="current PIN" required autocomplete="off" require autofocus>

            </label>
            <br><br>
            <label>
                Please enter your new 6-digit PIN:
                <input type="password" name="new_pin" class="screen-input" id="new_pin" maxlength="6" placeholder="new PIN" required autofocus>
            </label>
            <label>
                Please confirm your new PIN:
                <input type="password" name="confirm_pin" class="screen-input" id="confirm_pin" maxlength="6" placeholder="confirm new PIN" required autofocus>
            </label>
        </form>
    <?php endif; ?>

</div>
<?php require 'includes/atm_foot.php'; ?>