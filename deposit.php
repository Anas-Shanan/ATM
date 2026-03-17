<?php
require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

secure_session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
};

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

$stmt = $conn->prepare("
    SELECT accounts.id, accounts.balance
    FROM accounts
    JOIN users ON accounts.user_id = users.id
    WHERE users.id = ?
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$account = $stmt->get_result()->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    verify_csrf();
    $amount = trim($_POST['amount']);

    if (!$account) {
        die("No account found.");
    }
    if (!is_numeric($amount) || empty($amount)) {
        $error = "please enter a validd amount.";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than zero";
    } elseif ($amount >= 100000) {
        $error = "Amount must be less than 100000 $";
    } else {


        $conn->begin_transaction();

        try {
            // Update balance
            $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ?");
            $stmt->bind_param("di", $amount, $account["id"]);
            $stmt->execute();
            $stmt->close();

            //  Insert into transactions
            $stmt = $conn->prepare("
                 INSERT INTO transactions (amount, type, created_at, account_id)
                 VALUES (?, 'deposit', NOW(), ?)
             ");
            $stmt->bind_param("di", $amount, $account["id"]);
            $stmt->execute();
            $stmt->close();

            $conn->commit();


            $stmt = $conn->prepare("SELECT balance FROM accounts WHERE id = ?");
            $stmt->bind_param("i", $account["id"]);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $account['balance'] = $row['balance'];
            $stmt->close();

            $success = "Successfully deposited $" . number_format($amount, 2);
            log_action($conn, $user_id, 'deposit', $amount);
        } catch (Exception $e) {
            $conn->rollback();
            $error = "transaction failed, please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deposit Cash</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div>
        <h1> Deposit Cash</h1>
        <p> current balance: <Strong>$<?php echo number_format($account["balance"], 2); ?></Strong></p>
        <?php if ($error): ?>
            <p><?php echo htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if ($success): ?>
            <p><?php echo htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST" action="deposit.php">
            <?php csrf_field(); ?>
            <label> Amount to deposit
                <input type="number" min="1" max="100000" step="0.01" placeholder="Enter amount" name="amount" required> $
            </label>
            <button type="submit" name="submit">Enter</button>
        </form>

        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>

</html>