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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $current_pin = $_POST['current_pin'];
    $new_pin = $_POST['new_pin'];
    $confirm_pin = $_POST['confirm_pin'];

    if (empty($current_pin) || empty($new_pin) || empty($confirm_pin)) {

        $error = "all fields are required.";
    } elseif (!preg_match('/^\d{6}$/', $new_pin)) {

        $error = "New PIN must be exactly 6 digits.";
    } elseif ($new_pin !== $confirm_pin) {
        $error = "New PIN and confirmation do not match.";
    } elseif ($new_pin === $current_pin) {
        $error = "New PIN must be different from current PIN.";
    } else {
        $stmt = $conn->prepare("SELECT pin_hash FROM users WHERE id=?");
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$user || !password_verify($current_pin . PEPPER, $user['pin_hash'])) {

            $error = "Current PIN is incorrect. Please try again";
        } else {

            $new_pin_hash = password_hash($new_pin . PEPPER, PASSWORD_DEFAULT);
            // update in DB

            $stmt = $conn->prepare("UPDATE users SET pin_hash = ? WHERE id=?");
            $stmt->bind_param("si", $new_pin_hash, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            $success = "PIN changed successfully!";
            log_action($conn, $_SESSION['user_id'], 'pin_change');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change PIN</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <div class="card">
        <h1>Change PIN</h1>

        <?php if (!empty($error)): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color:green;"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (empty($success)): // to hide the form after success 
        ?>

            <form action="pin_change.php" method="POST">
                <?php csrf_field(); ?>
                <label>
                    Current PIN number:
                    <input type="password" name="current_pin" id="current_pin" maxlength="6" placeholder="current PIN" required autocomplete="off">

                </label>
                <br><br>
                <label>
                    please enter your New 6 - digit PIN:
                    <input type="password" name="new_pin" id="new_pin" maxlength="6" placeholder="new PIN" required>
                </label>
                <label>
                    please confirm your New PIN:
                    <input type="password" name="confirm_pin" id="confirm_pin" maxlength="6" placeholder="confirm new PIN" required>
                </label>
                <br><br>

                <button type="submit">Change PIN</button>
            </form>
        <?php endif; ?>
        <form method="get" action="dashboard.php">
            <button type="submit" class="btn-back">← Back to Dashboard</button>
        </form>
    </div>
</body>

</html>