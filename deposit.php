<?php

require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

validate_session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error   = "";
$success = "";

$side_buttons = [
    'bt1-l' => ['label' => '$20', 'action' => 'preset:20'],
    'bt1-r' => ['label' => '$50', 'action' => 'preset:50'],
    'bt2-l' => ['label' => '$100', 'action' => 'preset:100'],
    'bt2-r' => ['label' => '$200', 'action' => 'preset:200'],
    'bt3-l' => ['label' => 'CLR', 'action' => 'clear'],
    'bt3-r' => ['label' => 'GO', 'action' => 'submit'],
    'bt4-l' => ['label' => 'MENU', 'action' => 'href:dashboard.php'],
    'bt4-r' => ['label' => 'STM', 'action' => 'href:statement.php'],
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $amount = trim($_POST['amount']);

    if (!is_numeric($amount) || empty($amount)) {
        $error = "Please enter a valid amount.";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif ($amount >= 100000) {
        $error = "Amount must be less than $100,000.";
        log_action($conn, $user_id, 'Err: exceeds deposit limit', $amount);
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $account["id"]);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO transactions (amount, type, created_at, account_id) VALUES (?, 'deposit', NOW(), ?)");
            $stmt->bind_param("di", $amount, $account["id"]);
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            // Refresh balance to show updated value
            $stmt = $conn->prepare("SELECT balance FROM accounts WHERE id = ?");
            $stmt->bind_param("i", $account["id"]);
            $stmt->execute();
            $account['balance'] = $stmt->get_result()->fetch_assoc()['balance'];
            $stmt->close();

            $success = "Successfully deposited $" . number_format($amount, 2);
            log_action($conn, $user_id, 'deposit', $amount);
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed. Please try again.";
        }
    }
}

// ============================================================
// SET VARIABLES the includes can use

$page_title = "Deposit – Geldautomat";

// JS for the keypad keys on THIS page — injected by atm_foot.php

/* this called NEWDOC OR HEREDOC <<<JS*/
$page_script = <<<'JS'

<script>
    const amountInput = document.getElementById('depositAmount');

    function setDepositAmount(amount) {
        amountInput.value = String(amount);
        amountInput.focus();
    }

    function handleSideButtonAction(action) {
        if (!action) return false;

        if (action.startsWith('preset:')) {
            setDepositAmount(action.slice(7));
            return true;
        }

        if (action === 'clear') {
            amountInput.value = '';
            amountInput.focus();
            return true;
        }

        if (action === 'submit') {
            document.getElementById('depositForm').submit();
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
        document.getElementById('depositForm').submit();
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


<!-- SCREEN CONTENT — only this part changes per page -->


<div class="screen-inner screen_title">
    <div class="scr-titlebar">
        <span class="scr-titlebar-text">Deposit Cash</span>
        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <div class="screen-section">
        <p class="screen-balance-label">Current Balance</p>
        <p class="screen-balance-amount">$ <?= number_format($account['balance'], 2) ?></p>
    </div>

    <?php if ($error): ?>
        <p class="scr-msg error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="scr-msg success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST" action="deposit.php" id="depositForm">
        <?php csrf_field(); ?>

        <div class="screen-field">
            <div class="screen-label">&#9658; Amount to Deposit ($)</div>
            <input
                type="text" inputmode="decimal"
                id="depositAmount"
                name="amount"
                class="screen-input"
                min="1" max="99999" step="0.01"
                placeholder="0.00"
                required>
        </div>
    </form>
</div>

<?php

// CLOSE (outputs keypad, closing tags, $page_script)

require 'includes/atm_foot.php';
?>