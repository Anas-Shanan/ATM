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
        $error = "please enter a valid amount";
    } elseif ($amount <= 0) {
        $error = "amount must be more than zero";
    } elseif ($amount >= 100000) {
        $error = "Amount must not be more than 100000 $";
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
            log_action($conn, $user_id, 'wihdraw_transaction_error', $e->getMessage());
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
</script>
JS;

require 'includes/atm_head.php';

?>

<div class="screen-inner screen_title">
    <h1>Withdraw Cash</h1>

    <p class="screen-balance-label">Current Balance</p>
    <p class="screen-balance-amount">$ <?= number_format($account['balance'], 2) ?> </p>

    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST" action="withdraw.php" id="withdrawForm">
        <?php csrf_field(); ?>

        <label for="amount">Amount to Withdraw</label>

        <div style="display: block; margin:10px">
            <?php foreach ($values as $value): ?>
                <button style="padding: 5px;" type="submit" name="preset_amount" value="<?php echo $value; ?>"> <?php echo $value ?> $</button>
            <?php endforeach; ?>
        </div>
        <input type="text" inputmode="decimal" name="custom_amount" id="withdrawAmount" max="100000" min="1" step="0.01" placeholder="Other amount">

        <button type="submit">Withdraw</button>
    </form>

    <a href="dashboard.php"> ← Back to Dashboard</a>
</div>
<?php
require 'includes/atm_foot.php';
?>