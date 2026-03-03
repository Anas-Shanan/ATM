<?php
session_start(); // Like app.use(session()) in Express — must be first

// If user is already logged in, skip the login page entirely
// Like: if (req.session.user) return res.redirect('/dashboard')
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Pull in the DB connection (like require('./config/db.js') in Node)
require_once 'config/db.php';

$error = ""; // We'll use this to show error messages in the HTML below

// Only run this block if the form was submitted
// Like: if (req.method === 'POST')
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Grab form values from $_POST — like req.body in Express
    $card = trim($_POST['card_number']); // trim() removes accidental spaces
    $pin  = trim($_POST['pin']);

    // Basic validation — make sure fields aren't empty
    if (empty($card) || empty($pin)) {
        $error = "Please enter both card number and PIN.";
    } else {
        // Query the DB for a user with this card number
        // Like: User.findOne({ cardNumber: card })
        // The ? is a placeholder — PDO replaces it safely (prevents SQL injection)
        $stmt = $pdo->prepare("SELECT * FROM users WHERE card_number = ?");
        $stmt->execute([$card]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // Returns one row as an array, or false

        // Check user exists AND pin matches the hash
        // password_verify() = bcrypt.compare() in Node
        if ($user && password_verify($pin, $user['pin_hash'])) {

            // Credentials valid — create the session
            // Like: req.session.user_id = user.id
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name']; // handy to store for display

            // Redirect to dashboard
            // Like: res.redirect('/dashboard')
            header("Location: dashboard.php");
            exit; // ALWAYS exit after a redirect — stops code from running below

        } else {
            // Wrong card or PIN — intentionally vague message for security
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
            <!-- This is how you print PHP variables inside HTML -->
            <!-- Like JSX: {error && <p>{error}</p>} -->
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <!-- Form posts to itself (same file) — action="" means current URL -->
        <form method="POST" action="">
            <label for="card_number">Card Number</label>
            <input
                type="text"
                id="card_number"
                name="card_number"
                maxlength="16"
                placeholder="16-digit card number"
                required>

            <label for="pin">PIN</label>
            <input
                type="password"
                id="pin"
                name="pin"
                maxlength="6"
                placeholder="Enter PIN"
                required>

            <button type="submit">Login</button>
        </form>
    </div>

</body>

</html>