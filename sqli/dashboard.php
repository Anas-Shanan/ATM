<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT accounts.balance
    FROM accounts
    JOIN users ON accounts.user_id = users.id
    WHERE users.id = ?
");
$stmt->bind_param("i", $user_id); // "i" = integer
$stmt->execute();

$account = $stmt->get_result()->fetch_assoc();
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