<?php
require_once 'config/session.php';
require_once 'config/db.php';

secure_session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Only show the logged-in user's own logs (safe for non-admin)
$stmt = $conn->prepare("
    SELECT action, amount, ip_address, created_at
    FROM user_logs
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Activity Log</title>
</head>
<link rel="stylesheet" href="assets/style.css">

<body>
    <h1>Your Activity Log</h1>
    <table>
        <thead>
            <tr>
                <th>Action</th>
                <th>Amount</th>
                <th>IP</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= htmlspecialchars($log['action']) ?></td>
                    <td><?= $log['amount'] ? '$' . number_format($log['amount'], 2) : '—' ?></td>
                    <td><?= htmlspecialchars($log['ip_address']) ?></td>
                    <td><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="dashboard.php">← Back</a>
</body>

</html>