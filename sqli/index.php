<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

require_once 'config/db.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card = trim($_POST['card_number']);
    $pin  = trim($_POST['pin']);

    if (empty($card) || empty($pin)) {
        $error = "Please enter both card number and PIN.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card); // "s" = string
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($pin, $user['pin_hash'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid card number or PIN.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>ATM Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="atm-card">
        <h1>ATM Machine</h1>
        <h2>Insert Card</h2>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="card_number">Card Number</label>
            <input type="text" id="card_number" name="card_number" maxlength="16" placeholder="16-digit card number" required>

            <label for="pin">PIN</label>
            <input type="password" id="pin" name="pin" maxlength="6" placeholder="Enter PIN" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>