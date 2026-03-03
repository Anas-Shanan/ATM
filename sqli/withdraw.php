<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

$error   = "";
$success = "";
$user_id = $_SESSION['user_id'];

// Fetch account id and balance
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
    $amount = trim($_POST['amount']);

    if (empty($amount) || !is_numeric($amount)) {
        $error = "Please enter a valid amount.";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif ($amount > $account['balance']) {
        $error = "Insufficient funds. Your balance is $" . number_format($account['balance'], 2);
    } else {
        try {
            $conn->begin_transaction();

            // Deduct balance
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $account['id']); // "d" = double, "i" = integer

            $stmt->execute();

            // Record transaction
            $stmt = $conn->prepare("INSERT INTO transactions (account_id, type, amount) VALUES (?, 'withdrawal', ?)");
            $stmt->bind_param("id", $account['id'], $amount);
            $stmt->execute();

            $conn->commit();

            $account['balance'] -= $amount;
            $success = "Successfully withdrew $" . number_format($amount, 2);
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Withdraw</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="atm-card">
        <h1>Withdraw Cash</h1>

        <p>Available Balance: <strong>$<?php echo number_format($account['balance'], 2); ?></strong></p>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="amount">Amount to Withdraw</label>
            <input type="number" id="amount" name="amount" min="1" step="0.01" placeholder="Enter amount" required>
            <button type="submit">Withdraw</button>
        </form>

        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>

</html>