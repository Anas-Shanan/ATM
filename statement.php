<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'config/db.php';

// --- Optional date range filter ---
// If the form wasn't submitted, default to showing last 30 days
$date_from = $_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days'));
$date_to   = $_GET['date_to']   ?? date('Y-m-d'); // today

// --- The JOIN query ---
// transactions → accounts → users, filtered by logged-in user
// ORDER BY DESC = newest first
$stmt = $pdo->prepare("
    SELECT t.type, t.amount, t.created_at
    FROM transactions t
    JOIN accounts a ON t.account_id = a.id
    WHERE a.user_id = ?
      AND DATE(t.created_at) BETWEEN ? AND ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $date_from, $date_to]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC); // fetchAll = all rows, like .find() in Mongoose
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Statement</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="atm-card">
        <h1>Transaction History</h1>

        <!-- Date filter form — uses GET so filters show in the URL -->
        <form method="GET" action="">
            <label for="date_from">From</label>
            <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">

            <label for="date_to">To</label>
            <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">

            <button type="submit">Filter</button>
        </form>

        <?php if (empty($transactions)): ?>
            <p>No transactions found for this period.</p>
        <?php else: ?>
            <table>
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
                            <!-- date() formats the timestamp into something readable -->
                            <td><?php echo date('M d, Y  H:i', strtotime($tx['created_at'])); ?></td>

                            <!-- Add a CSS class based on type for green/red coloring -->
                            <td class="<?php echo $tx['type']; ?>">
                                <?php echo ucfirst($tx['type']); ?> <!-- deposit → Deposit -->
                            </td>

                            <td>
                                <!-- Show + for deposit, - for withdrawal -->
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
</body>

</html>