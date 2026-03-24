<?php
require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

secure_session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";
$deleted = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
     verify_csrf();

    $stmt = $conn->prepare("
    SELECT u.full_name, a.balance
    FROM users u
    JOIN accounts a ON a.user_id = u.id
    WHERE u.id = ?
    ");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($userData && $userData['balance'] > 0) {
        $error = "you still have $" . number_format($userData['balance'], 2) . " in your account. Please withdraw first.";
    } else {
        //here is all clear . start the transaction and delete 
        $conn->begin_transaction();
        try {
            //1: delete transactions
            $stmt = $conn->prepare("
            DELETE t FROM transactions t
            JOIN accounts a ON t.account_id = a.id
            WHERE a.user_id = ? 
            ");
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $stmt->close();

            // 2: delete account
            $stmt = $conn->prepare("DELETE FROM accounts WHERE user_id = ?");
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $stmt->close();

            // 3: delete user

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $_SESSION["user_id"]);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $savedName = $userData['full_name']; // for the goodbye message
            $user_id_to_log = $_SESSION['user_id']; // Save before destroying session

            log_action($conn, $user_id_to_log, 'account_deleted');
            
            session_destroy();
            $deleted = true;
            $success = "Account successfully deleted. Goodbye, " . htmlspecialchars($savedName) . "!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Something went wrong. Please try again.";
        }
    }
} else {

    $stmt = $conn->prepare("
        SELECT u.full_name, u.card_number, a.balance
        FROM users u 
        JOIN accounts a ON a.user_id = u.id
        WHERE u.id = ?
");

    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $userData = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete account</title>
</head>

<body>

    <h1>Delete Account</h1>
    <?php if ($error): ?>
        <p><?php echo htmlspecialchars($error) ?></p>
        <a href="withdraw.php">Withdraw Money</a>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <p><?php echo htmlspecialchars($success) ?></p>

        <a href="login.php">Return to Home</a>

    <?php else: ?>
        <p>Name: <?php echo htmlspecialchars($userData['full_name']); ?></p>
        <p>Card: **** **** ****<?php echo substr($userData['card_number'], -4); ?></p>
        <p>Balance: $<?php echo number_format($userData['balance'], 2); ?></p>

            <?php if ($userData['balance'] > 0): ?>
                <p>You must withdraw your balance before deleting your account.</p>
                <a href="withdraw.php">Withdraw Money</a>

            <?php else: ?>
                <!--    balance is zero here  -->
                <form action="delete.php" method="POST">
                    <?php csrf_field(); ?>
                    <button type="submit"> Yes, Delete My Account </button>
                </form>

            <?php endif; ?>

        <br>

        <a href="dashboard.php">Cancel</a>
    <?php endif; ?>

</body>

</html>