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
$deleted = false;
$userData = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    verify_csrf();

    $stmt = $conn->prepare("
    SELECT u.full_name, u.card_number, a.balance
    FROM users u
    JOIN accounts a ON a.user_id = u.id
    WHERE u.id = ?
    ");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($userData && $userData['balance'] > 0) {
        $error = "you still have $" . number_format($userData['balance'], 2) . " in your account. Please withdraw first.";
    } else {
        //here is all clear . start the transaction and delete 
        $conn->begin_transaction();
        try {
            //1: delete transactions
            $stmt = $conn->prepare("
            DELETE t FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            WHERE a.user_id = ? 
            ");
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $stmt->close();

            // 2: delete account
            $stmt = $conn->prepare("DELETE FROM accounts WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $stmt->close();

            // 3: delete user

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $savedName = $userData['full_name']; // for the goodbye message
            log_action($conn, $_SESSION['user_id'], 'account_deleted');

            session_destroy();
            $deleted = true;
            $success = "Account successfully deleted. Goodbye, " . htmlspecialchars($savedName) . "!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Something went wrong. Please try again.";
        }
    }
} else {

    $stmt = $conn->prepare("
        SELECT u.full_name, u.card_number, a.balance
        FROM users u 
        JOIN accounts a ON a.user_id = u.id
        WHERE u.id = ?
");

    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$can_delete_now = !$deleted && !empty($userData) && (float) $userData['balance'] <= 0;

$side_buttons = [
    'bt1-l' => ['label' => 'DASH', 'action' => 'href:dashboard.php'],
    'bt1-r' => ['label' => 'WDR', 'action' => 'href:withdraw.php'],
    'bt2-l' => ['label' => 'DEP', 'action' => 'href:deposit.php'],
    'bt2-r' => ['label' => 'STM', 'action' => 'href:statement.php'],
    'bt3-l' => ['label' => 'LOG', 'action' => 'href:logs.php'],
    'bt3-r' => ['label' => 'PIN', 'action' => 'href:pin_change.php'],
    'bt4-l' => ['label' => 'OUT', 'action' => 'href:logout.php'],
    'bt4-r' => ['label' => $deleted ? 'HOME' : ($can_delete_now ? 'DEL' : 'WDR'), 'action' => $deleted ? 'href:login.php' : ($can_delete_now ? 'submit' : 'href:withdraw.php')],
];

$page_title = "Delete Account – Geldautomat";


$page_script = <<<'JS'
<script>
    function addKey() {
        return;
    }

    function correctKey() {
        return;
    }

    function handleSideButtonAction(action) {
        if (action === 'submit') {
            const form = document.getElementById('deleteForm');
            if (form) {
                form.submit();
            }
            return true;
        }

        return false;
    }

    function confirmKey() {
        const form = document.getElementById('deleteForm');
        if (form) {
            form.submit();
        }
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
        <span class="scr-titlebar-text">Delete Account</span>
        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <div class="screen-section">

        <?php if (!empty($error) && !$deleted): ?>
            <p class="scr-msg error"><?php echo htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($deleted): ?>
            <p class="scr-msg success"><?php echo htmlspecialchars($success) ?></p>

        <?php elseif (!empty($userData)): ?>
            <p>Name: <?php echo htmlspecialchars($userData['full_name']); ?></p>
            <p>Card: **** **** ****<?php echo substr($userData['card_number'], -4); ?></p>
            <p>Balance: $<?php echo number_format($userData['balance'], 2); ?></p>

            <?php if ($userData['balance'] > 0): ?>
                <p class="scr-msg warning">You must withdraw your balance before deleting your account.</p>

            <?php else: ?>
                <!--    balance is zero here  -->
                <form action="delete.php" method="POST" id="deleteForm">
                    <?php csrf_field(); ?>
                </form>

            <?php endif; ?>

        <?php else: ?>
            <p class="scr-msg error">Account information is not available right now.</p>

        <?php endif; ?>
    </div>
</div>
<?php

require 'includes/atm_foot.php';
?>