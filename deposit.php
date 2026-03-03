<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

$error   = "";
$success = "";

$stmt = $pdo->prepare("
    SELECT accounts.id, accounts.balance
    FROM accounts
    JOIN users ON accounts.user_id = users.id
    WHERE users.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = trim($_POST['amount']);

    if (empty($amount) || !is_numeric($amount)) {
        $error = "Please enter a valid amount.";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } else {
        try {
            $pdo->beginTransaction();

            // Add to balance — only difference from withdraw
            $stmt = $pdo->prepare("
                UPDATE accounts
                SET balance = balance + ?
                WHERE id = ?
            ");
            $stmt->execute([$amount, $account['id']]);

            // Record as deposit
            $stmt = $pdo->prepare("
                INSERT INTO transactions (account_id, type, amount)
                VALUES (?, 'deposit', ?)
            ");
            $stmt->execute([$account['id'], $amount]);

            $pdo->commit();

            $account['balance'] += $amount;
            $success = "Successfully deposited $" . number_format($amount, 2);
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Transaction failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Deposit</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="atm-card">
        <h1>Deposit Cash</h1>

        <p>Current Balance: <strong>$<?php echo number_format($account['balance'], 2); ?></strong></p>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="amount">Amount to Deposit</label>
            <input
                type="number"
                id="amount"
                name="amount"
                min="1"
                step="0.01"
                placeholder="Enter amount"
                required>
            <button type="submit">Deposit</button>
        </form>

        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>

</html>