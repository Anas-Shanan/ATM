<?php
require_once 'config/db.php';

$users = [
    ['Ahmed Ali',   '1234567890123456', '1234', 5000.00],
    ['Sara Hassan', '9876543210987654', '5678', 12500.50],
    ['Omar Khaled', '1111222233334444', '9999', 800.00],
];

foreach ($users as $u) {
    $pin_hash = password_hash($u[2], PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (full_name, card_number, pin_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $u[0], $u[1], $pin_hash);
    $stmt->execute();

    $user_id = $conn->insert_id; // like pdo->lastInsertId()
    $stmt = $conn->prepare("INSERT INTO accounts (user_id, balance) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $u[3]);
    $stmt->execute();
}

echo "✅ Users and accounts seeded.<br>";

$result   = $conn->query("SELECT id FROM accounts ORDER BY id ASC");
$accounts = $result->fetch_all(MYSQLI_ASSOC);

$ahmed = $accounts[0]['id'];
$sara  = $accounts[1]['id'];
$omar  = $accounts[2]['id'];

$transactions = [
    [$ahmed, 'deposit',    2000.00, '2025-01-05 09:15:00'],
    [$ahmed, 'deposit',    1500.00, '2025-01-10 11:30:00'],
    [$ahmed, 'withdrawal',  300.00, '2025-01-12 14:00:00'],
    [$ahmed, 'withdrawal',  150.00, '2025-01-18 16:45:00'],
    [$ahmed, 'deposit',     500.00, '2025-02-01 08:00:00'],
    [$ahmed, 'withdrawal',  200.00, '2025-02-14 13:20:00'],
    [$sara,  'deposit',    5000.00, '2025-01-02 08:00:00'],
    [$sara,  'deposit',    3000.00, '2025-01-20 09:30:00'],
    [$sara,  'withdrawal',  200.00, '2025-02-05 12:00:00'],
    [$sara,  'deposit',    2000.00, '2025-02-20 15:00:00'],
    [$omar,  'deposit',    1000.00, '2025-01-15 10:00:00'],
    [$omar,  'withdrawal',  100.00, '2025-01-20 14:00:00'],
    [$omar,  'withdrawal',   50.00, '2025-02-10 16:00:00'],
];

$stmt = $conn->prepare("INSERT INTO transactions (account_id, type, amount, created_at) VALUES (?, ?, ?, ?)");

foreach ($transactions as $t) {
    $stmt->bind_param("isds", $t[0], $t[1], $t[2], $t[3]);
    $stmt->execute();
}

echo "✅ Transactions seeded.<br><br>";
echo "<strong>Test Accounts:</strong><br>";
echo "Card: 1234567890123456 | PIN: 1234 | Ahmed Ali<br>";
echo "Card: 9876543210987654 | PIN: 5678 | Sara Hassan<br>";
echo "Card: 1111222233334444 | PIN: 9999 | Omar Khaled<br>";
