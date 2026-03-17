<?php
require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

secure_session_start();

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = trim($_POST['full_name']);
    $card_number = trim($_POST['card_number']);
    $pin =  trim($_POST['pin']);
    $pin_confirm = trim($_POST['pin_confirm']);

    if (empty($full_name)) {
        $errors[]  = "Full name is required.";
    }
    if (empty($card_number)) {
        $errors[] = "Card number is required.";
    } elseif (!preg_match('/^\d{16}$/', $card_number)) {
        $errors[] = "Card number must be 16 digits";
    }
    if (empty($pin)) {
        $errors[] = "Pin number is required";
    } elseif (!preg_match('/^\d{6}$/', $pin)) {
        $errors[] = "Pin number must be 6 digits";
    }
    if (empty($pin_confirm)) {
        $errors[] = "Pin confirmation is required";
    }
    if ($pin !== $pin_confirm) {
        $errors[] = "Pin numbers do not matching ";
    }
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card_number);
        $stmt->execute();

        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "This card number is already registered";
        }
        $stmt->close();
    }
    if (empty($errors)) {
        $pin_hash = password_hash($pin . PEPPER, PASSWORD_BCRYPT);

        // INSERT into users table
        $stmt = $conn->prepare("INSERT INTO users (full_name , card_number, pin_hash) VALUES (?,?,?) ");
        $stmt->bind_param("sss", $full_name, $card_number, $pin_hash);
        $stmt->execute();
        $new_user_id = $conn->insert_id;
        // or $new_user_id = $stmt->insert_id;
        $stmt->close();

        //  INSERT into accounts table
        $stmt = $conn->prepare(" INSERT INTO accounts (user_id, balance) VALUES (?, 0.00)");
        $stmt->bind_param("i", $new_user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash_success'] = 'Account created successfully! Please log in.';
        header('Location: login.php');
        exit;
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bankautomat registration</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

    <h1>User registration</h1>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form action="registration.php" method="POST">
         <?php csrf_field(); ?>
        <label>
            Full Name:
            <input type="text" name="full_name" id="full_name" maxlength="50" placeholder="e.g. Maxi Shan" required>
        </label>
        <br><br>
        <label>
            Card Number:
            <input type="text" name="card_number" id="card_number" maxlength="16" placeholder="16 digits card number" inputmode="numeric" autocomplete="off" required>

        </label>
        <br><br>

        <label>
            PIN Number:
            <input type="password" name="pin" id="pin" maxlength="6" placeholder="6-digit PIN" inputmode="numeric" required>
        </label>
        <br><br>
        <label>
            Confirm PIN Number:
            <input type="password" name="pin_confirm" id="pin_confirm" maxlength="6" placeholder="Confirm PIN" inputmode="numeric" required>
        </label>

        <button type="submit">Create Account</button>
    </form>
    <br><br>
    <div>
        <span>already registered?</span>
    </div>
    <a href="login.php">← Back to Login</a>
    </div>


    </div>


</body>

</html>