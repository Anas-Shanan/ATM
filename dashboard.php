<?php
session_start();

// AUTH GUARD — paste this at the top of every protected page
// In Express this was middleware. In PHP it's manual, but identical logic.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Pull the user's account info from the DB
require_once 'config/db.php';

// Get account balance for this user
// JOIN links the accounts table to users — like Mongoose .populate()
$stmt = $pdo->prepare("
    SELECT accounts.balance
    FROM accounts
    JOIN users ON accounts.user_id = users.id
    WHERE users.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ATM Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

    <div class="atm-card">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></h1>

        <div class="balance">
            <p>Current Balance</p>
            <!-- number_format() formats decimals nicely: 1000.5 → 1,000.50 -->
            <h2>$<?php echo number_format($account['balance'], 2); ?></h2>
        </div>

        <nav>
            <a href="withdraw.php">Withdraw</a>
            <a href="deposit.php">Deposit</a>
            <a href="statement.php">Statement</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>

</body>

</html>