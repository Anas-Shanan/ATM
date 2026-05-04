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

$side_buttons = [
    'bt1-l' => ['label' => '7D', 'action' => 'range:7'],
    'bt1-r' => ['label' => '30D', 'action' => 'range:30'],
    'bt2-l' => ['label' => 'CLR', 'action' => 'range:clear'],
    'bt2-r' => ['label' => 'GO', 'action' => 'submit'],
    'bt3-l' => ['label' => 'WDR', 'action' => 'href:withdraw.php'],
    'bt3-r' => ['label' => 'DEP', 'action' => 'href:deposit.php'],
    'bt4-l' => ['label' => 'MENU', 'action' => 'href:dashboard.php'],
    'bt4-r' => ['label' => 'OUT', 'action' => 'href:logout.php'],
];

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

$page_title = "Statement – Geldautomat";
$page_script = <<<'JS'
<script>
    function addKey() {
        return;
    }

    function correctKey() {
        return;
    }

    function confirmKey() {
        document.getElementById('statementFilterForm').submit();
    }

    function setDateRange(days) {
        const now = new Date();
        const toVal = now.toISOString().slice(0, 10);

        const fromDate = new Date(now);
        fromDate.setDate(fromDate.getDate() - days);
        const fromVal = fromDate.toISOString().slice(0, 10);

        document.getElementById('date_to').value = toVal;
        document.getElementById('date_from').value = fromVal;
    }

    function clearDateRange() {
        document.getElementById('date_to').value = '';
        document.getElementById('date_from').value = '';
    }

    function handleSideButtonAction(action) {
        if (!action) return false;

        if (action.startsWith('range:')) {
            const mode = action.slice(6);
            if (mode === 'clear') {
                clearDateRange();
            } else {
                setDateRange(Number(mode));
            }
            return true;
        }

        if (action === 'submit') {
            document.getElementById('statementFilterForm').submit();
            return true;
        }

        return false;
    }

    function updateClock() {
        const el = document.getElementById('scr-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }

    updateClock();
    setInterval(updateClock, 1000);
</script>
JS;

log_action($conn, $user_id, 'Statement_check');
require 'includes/atm_head.php';

?>

<div class="screen-inner screen_title">
    <div class="scr-titlebar">
        <span class="scr-titlebar-text">Statement</span>
        <span class="scr-clock" id="scr-clock">--:--:--</span>
    </div>

    <div class="screen-section">
        <form method="GET" action="statement.php" id="statementFilterForm" class="screen-form-block">
            <label for="date_from"> From
                <input type="date" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
            </label>
            <label for="date_to"> To
                <input type="date" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
            </label>
        </form>
    </div>

    <div class="screen-section">
        <p class="screen-section-title">Transactions</p>
        <?php
        if (empty($transactions)): ?>
            <p>No transactions found for this period.</p>
        <?php else: ?>
            <div class="screen-table-wrap">
                <table class="atm-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr class="<?php echo $tx['type']; ?>">
                                <td><?php echo date('M d, Y H:i', strtotime($tx['created_at'])); ?></td>
                                <td >
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
            </div>
        <?php endif; ?>
    </div>

</div>
<?php

require 'includes/atm_foot.php';
?>