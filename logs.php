<?php
require_once 'config/session.php';
require_once 'config/db.php';

validate_session_start();

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

require 'includes/atm_head.php';
?>


<div class="screen-inner screen_title">
    <h1>Activity logs</h1>
    <div style="overflow-y: scroll;">
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
    </div>
</div>
<?php
require 'includes/atm_foot.php';
?>