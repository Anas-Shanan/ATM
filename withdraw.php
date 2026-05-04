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
$user_id = $_SESSION['user_id'];
$values = [25, 50, 100, 150, 200, 500, 1000];

$side_buttons = [
    'bt1-l' => ['label' => '$25', 'action' => 'preset:25'],
    'bt1-r' => ['label' => '$50', 'action' => 'preset:50'],
    'bt2-l' => ['label' => '$100', 'action' => 'preset:100'],
    'bt2-r' => ['label' => '$200', 'action' => 'preset:200'],
    'bt3-l' => ['label' => '$500', 'action' => 'preset:500'],
    'bt3-r' => ['label' => '$1000', 'action' => 'preset:1000'],
    'bt4-l' => ['label' => 'CLR', 'action' => 'clear'],
    'bt4-r' => ['label' => 'GO', 'action' => 'submit'],
];

$stmt = $conn->prepare("
    SELECT accounts.id, accounts.balance
    FROM accounts
    JOIN users ON accounts.user_id = users.id
    WHERE users.id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();
if (!$account) {
    die("No account found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (isset($_POST['preset_amount'])) {
        $amount = $_POST['preset_amount'];
    } elseif (!empty($_POST['custom_amount'])) {
        $amount = trim($_POST['custom_amount']);
    } else {
        $amount = null;
    }

    if (!is_numeric($amount) || empty($amount)) {
        $error = "Please enter a valid amount.";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif ($amount >= 100000) {
        $error = "Amount must not be more than $100,000.";
        log_action($conn, $user_id, 'Err:exceed withdraw limit', $amount);
    } elseif ($amount > $account['balance']) {
        $error = "Insufficient funds. Your balance is $" . number_format($account['balance'], 2);
        log_action($conn, $user_id, 'Insufficient funds', $amount);
    } else {

        $conn->begin_transaction();
        try {
            // update balance
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $account["id"]);
            $stmt->execute();
            //record transaction
            $stmt = $conn->prepare("
        INSERT INTO transactions (amount, type, created_at, account_id)
        VALUES (?, 'withdraw', NOW(), ?)
        ");
            $stmt->bind_param("di", $amount, $account['id']);
            $stmt->execute();

            $conn->commit();

            $stmt = $conn->prepare("SELECT balance FROM accounts WHERE id = ?");
            $stmt->bind_param("i", $account["id"]);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $account['balance'] = $row['balance'];
            $stmt->close();

            $success = "Successfully withdrew $" . number_format($amount, 2);
            log_action($conn, $user_id, 'withdraw', $amount);
        } catch (Exception $e) {
            log_action($conn, $user_id, 'withdraw_transaction_error', $e->getMessage());
            $conn->rollback();
            $error = "Transaction failed. Please try again.";
        }
    }
}

$page_title = "Withdraw – Geldautomat";

// JS for the keypad keys on THIS page — injected by atm_foot.php

/* this called NEWDOC OR HEREDOC <<<JS*/
$page_script = <<<'JS'
<script>
    const amountInput = document.getElementById('withdrawAmount');

    function setWithdrawAmount(amount) {
        amountInput.value = String(amount);
        amountInput.focus();
    }

    function handleSideButtonAction(action) {
        if (!action) return false;

        if (action.startsWith('preset:')) {
            setWithdrawAmount(action.slice(7));
            return true;
        }

        if (action === 'clear') {
            amountInput.value = '';
            amountInput.focus();
            return true;
        }

        if (action === 'submit') {
            document.getElementById('withdrawForm').submit();
            return true;
        }

        return false;
    }

    function addKey(d) {
        if (d === '.' && amountInput.value.includes('.')) return;
        if (amountInput.value === '0' && d !== '.') amountInput.value = '';
        amountInput.value += d;
    }

    function correctKey() {
        amountInput.value = amountInput.value.slice(0, -1);
    }

    function confirmKey() {
        document.getElementById('withdrawForm').submit();
    }

    function updateClock() {
        const el = document.getElementById('scr-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    updateClock();
    setInterval(updateClock, 1000);
</script>
JS;

require 'includes/atm_head.php';

?>

<div class="screen-inner screen_title">
    <div class="scr-titlebar">
        <span class="scr-titlebar-text">Withdraw Cash</span>
        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <div class="screen-section">
     <p class="screen-balance-label">Current Balance
                <span class="screen-balance-amount"><span class="bracket"> [ </span><?= number_format($account['balance'], 2) ?> <span class="bracket"> ] </span> $ </span>
    
    </div>

    <?php if ($error): ?>
        <p class="scr-msg error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="scr-msg success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" action="withdraw.php" id="withdrawForm" class="screen-form-block">
        <?php csrf_field(); ?>

        <label for="withdrawAmount">Amount to Withdraw</label>

        <input type="text" inputmode="decimal" name="custom_amount" id="withdrawAmount" max="100000" min="1" step="0.01" placeholder="Other amount" class="screen-input">
    </form>
</div>
<?php
require 'includes/atm_foot.php';
?>