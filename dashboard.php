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

$side_buttons = [
    'bt1-l' => ['label' => 'WDR', 'action' => 'href:withdraw.php'],
    'bt1-r' => ['label' => 'DEP', 'action' => 'href:deposit.php'],
    'bt2-l' => ['label' => 'STM', 'action' => 'href:statement.php'],
    'bt2-r' => ['label' => 'PIN', 'action' => 'href:pin_change.php'],
    'bt3-l' => ['label' => 'LOG', 'action' => 'href:logs.php'],
    'bt3-r' => ['label' => 'DEL', 'action' => 'href:delete.php'],
    'bt4-l' => ['label' => 'REF', 'action' => 'href:dashboard.php'],
    'bt4-r' => ['label' => 'OUT', 'action' => 'href:logout.php'],
];

$page_title = "Dashboard – Geldautomat";

$page_script = <<<'JS'
<script>

        function updateClock() {
        const el = document.getElementById('scr-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second:'2-digit' });
    }
    updateClock();
    setInterval(updateClock, 1000);
    </script>
JS;

require 'includes/atm_head.php';

?>
<div class="screen-inner screen_title">

    <div class="scr-titlebar">
        <div>
            <p class="scr-titlebar-text">Welcome, <strong><?php echo htmlspecialchars($user_name); ?></strong></p>
            <p class="card-num">Card: **** **** **** <?php echo substr($card_number, -4); ?></p>
            <p class="screen-balance-label">Current Balance
                <span class="screen-balance-amount"><span class="bracket"> [ </span><?= number_format($balance, 2) ?> <span class="bracket"> ] </span> $ </span>
            </p>
        </div>

        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <div class="screen-section">
        <p class="screen-section-title">Recent Transactions</p>

        <?php if (empty($transactions)): ?>
            <p>No transactions yet.</p>

        <?php else: ?>
            <div class="screen-table-wrap">
                <table class="atm-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr class="<?php echo htmlspecialchars($tx['type']); ?>">
                                <td> <?php echo $tx["type"] === "deposit" ? "⬆ Deposit" : " ⬇ Withdraw"; ?></td>
                                <td><?php echo number_format($tx["amount"], 2); ?> </td>
                                <td><?php echo date("M d, Y H.i", strtotime($tx["created_at"])) ?> </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
    </div>
    <button class="btn-LOGOUT">
   <a href="logout.php">EXIT</a>
</button>
</div>
<?php

require 'includes/atm_foot.php';
?>