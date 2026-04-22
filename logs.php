<?php
require_once 'config/session.php';
require_once 'config/db.php';

validate_session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$side_buttons = [
    'bt1-l' => ['label' => 'DASH', 'action' => 'href:dashboard.php'],
    'bt1-r' => ['label' => 'STM', 'action' => 'href:statement.php'],
    'bt2-l' => ['label' => 'WDR', 'action' => 'href:withdraw.php'],
    'bt2-r' => ['label' => 'DEP', 'action' => 'href:deposit.php'],
    'bt3-l' => ['label' => 'PIN', 'action' => 'href:pin_change.php'],
    'bt3-r' => ['label' => 'DEL', 'action' => 'href:delete.php'],
    'bt4-l' => ['label' => 'REF', 'action' => 'href:logs.php'],
    'bt4-r' => ['label' => 'OUT', 'action' => 'href:logout.php'],
];

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

$page_title = "Logs – Geldautomat";
$page_script = <<<'JS'
<script>
    function addKey() {
        return;
    }

    function correctKey() {
        return;
    }

    function confirmKey() {
        window.location.href = 'dashboard.php';
    }

    function updateClock() {
        const el = document.getElementById('scr-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    updateClock();
    setInterval(updateClock, 1000);
</script>
JS;

require 'includes/atm_head.php';
?>


<div class="screen-inner screen_title">
    <div class="scr-titlebar">
        <span class="scr-titlebar-text">Activity Logs</span>
        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <div class="screen-section">
        <div class="screen-table-wrap">
            <table class="atm-table">
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
        </div>
    </div>
</div>
<?php
require 'includes/atm_foot.php';
?>