<?php
require_once 'config/session.php';
require_once 'config/db.php';
require_once 'config/security.php';

validate_session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = "";
$success = "";

$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-10 days'));
$date_to = $_GET['date_to'] ?? date('Y-m-d');

$stmt = $conn->prepare("
    SELECT t.type, t.amount, t.created_at
    FROM transactions t
    JOIN accounts a ON t.account_id = a.id
    WHERE a.user_id = ?
      AND DATE(t.created_at) BETWEEN ? AND ?
    ORDER BY t.created_at DESC
");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}


$stmt->bind_param("iss", $user_id, $date_from, $date_to);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
log_action($conn, $user_id, 'Statement_check');
require 'includes/atm_head.php';

?>

<div class="screen-inner screen_title">
    <h1>Account History Cash</h1>

    <div style="padding: 10px; overflow-y:scroll">
        

        <form method="GET" action="statement.php">
            <label for="date_from"> From
                <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
            </label>
            <label for="date_to"> To
                <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
            </label>
            <button type="submit">Filter</button>

        </form>
        <?php
        if (empty($transactions)): ?>
            <p>No transactions found for this period.</p>
        <?php else: ?>
            <table style="padding: 10px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i', strtotime($tx['created_at'])); ?></td>
                            <td class="<?php echo $tx['type']; ?>">
                                <?php echo ucfirst($tx['type']); ?>
                            </td>
                            <td>
                                <?php echo $tx['type'] === 'deposit' ? '+' : '-'; ?>
                                $<?php echo number_format($tx['amount'], 2); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
</div>
<?php

require 'includes/atm_foot.php';
?>