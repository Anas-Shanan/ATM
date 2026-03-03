<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

$error   = "";
$success = "";

// Fetch the user's account — we need both the balance AND the account id
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

    // --- Step 1: Input Validation ---
    if (empty($amount) || !is_numeric($amount)) {
        $error = "Please enter a valid amount.";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } elseif ($amount > $account['balance']) {
        // --- Step 2: Sufficient Balance Check ---
        $error = "Insufficient funds. Your balance is $" . number_format($account['balance'], 2);
    } else {
        // --- Steps 3 & 4: Update balance + Insert transaction ---
        // Wrap in a DB transaction so both succeed or both fail together
        // Like Promise.all() but at the database level
        try {
            $pdo->beginTransaction(); // Start — like opening a "safe zone"

            // Deduct the amount from balance
            $stmt = $pdo->prepare("
                UPDATE accounts
                SET balance = balance - ?
                WHERE id = ?
            ");
            $stmt->execute([$amount, $account['id']]);

            // Record the transaction
            $stmt = $pdo->prepare("
                INSERT INTO transactions (account_id, type, amount)
                VALUES (?, 'withdrawal', ?)
            ");
            $stmt->execute([$account['id'], $amount]);

            $pdo->commit(); // Both succeeded — make it permanent

            // Refresh the balance displayed on page
            $account['balance'] -= $amount;
            $success = "Successfully withdrew $" . number_format($amount, 2);
        } catch (Exception $e) {
            $pdo->rollBack(); // Something failed — undo everything
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
            <input
                type="number"
                id="amount"
                name="amount"
                min="1"
                step="0.01"
                placeholder="Enter amount"
                required>
            <button type="submit">Withdraw</button>
        </form>

        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>

</html>