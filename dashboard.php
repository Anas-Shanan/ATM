<?php
require_once 'config/session.php';
require_once 'config/db.php';

validate_session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT u.full_name, u.card_number, a.balance, a.id AS account_id
    FROM users u
    JOIN accounts a ON a.user_id = u.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$_SESSION['account_id'] = $data['account_id'];

// get the last 5 transactions 

$stmt2 = $conn->prepare("
SELECT type, amount, created_at
FROM transactions 
WHERE account_id = ?
ORDER BY created_at DESC
LIMIT 5
");

$stmt2->bind_param("i", $data["account_id"]);
$stmt2->execute();
$trans_result = $stmt2->get_result();
$transactions = $trans_result->fetch_all(MYSQLI_ASSOC);
/* $transactions = mysqli_fetch_all($trans_result, MYSQLI_ASSOC); */


$user_name = $data['full_name'];
$card_number = $data['card_number'];
$balance = $data['balance'];
/* var_dump($transactions); */
require 'includes/atm_head.php';

?>
<div class="screen-inner screen_title">

    <div>
        <p>Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
        <p class="card-num">Card: **** **** **** <?php echo substr($card_number, -4); ?></p>
        <h3 class="screen-balance-label">Current balance <p class="screen-balance-amount"> $ <?= number_format($balance, 2) ?> </p>
        </h3>
    </div>

    <div style="margin: 10px;">

        <?php if (empty($transactions)): ?>
            <p>no transactions yet </p>

        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr <?php echo $tx["type"] ?>>
                            <td> <?php echo $tx["type"] === "deposit" ? "⬆️ Deposit" : " ⬇️ Withdraw"; ?></td>
                            <td><?php echo number_format($tx["amount"], 2); ?> </td>
                            <td><?php echo date("M d, Y H.i", strtotime($tx["created_at"])) ?> </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
    <nav style="display:flex; gap:10px;">
        <a href="withdraw.php">withdraw</a>
        <a href="deposit.php">Deposit</a>
        <a href="Statement.php">Statement </a>
        <a href="pin_change.php">PIN change</a>
        <a href="delete.php">delete account</a>
        <a href="logs.php">User Logs</a>
        <a href="logout.php">Logout</a>
    </nav>
</div>
<?php

require 'includes/atm_foot.php';
?>