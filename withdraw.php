<?php

require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

secure_session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";
$user_id = $_SESSION['user_id'];

$stmt = $conn-> prepare("
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

if($_SERVER['REQUEST_METHOD'] === 'POST'){
     verify_csrf();
    $amount = trim($_POST['amount']);

    if(!is_numeric($amount) || empty($amount)){
        $error= "please enter a valid amount";
    } elseif ($amount <= 0)
        {$error= "amount must be more than zero";
    } elseif ($amount >= 100000){
        $error = "Amount must not be more than 100000 $";
    } elseif ($amount > $account['balance'])
        {$error = "Insufficient funds. Your balance is $" . number_format($account['balance'], 2);}
    else {
        
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
         $stmt->bind_param("di", $amount, $account['id'],);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Cash</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
       <div>
        <h1>Withdraw Cash</h1>

        <p>Available Balance: <strong>$<?php echo number_format($account['balance'], 2); ?></strong></p>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" action="withdraw.php">
            <?php csrf_field(); ?>
            <label for="amount">Amount to Withdraw</label>
            <input type="number" id="amount" name="amount" max="100000" min="1" step="0.01" placeholder="Enter amount" required>
            <button type="submit">Withdraw</button>
        </form>

        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</body>
</html>
