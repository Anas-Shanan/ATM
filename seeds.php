<!-- <?php
        require_once 'config/db.php';

        // --- USERS & ACCOUNTS ---
        $users = [
            [
                'full_name'   => 'Ahmed Ali',
                'card_number' => '1234567890123456',
                'pin'         => '1234',
                'balance'     => 5000.00
            ],
            [
                'full_name'   => 'Sara Hassan',
                'card_number' => '9876543210987654',
                'pin'         => '5678',
                'balance'     => 12500.50
            ],
            [
                'full_name'   => 'Omar Khaled',
                'card_number' => '1111222233334444',
                'pin'         => '9999',
                'balance'     => 800.00
            ],
        ];

        foreach ($users as $user) {
            // Insert into users table
            $stmt = $pdo->prepare("
        INSERT INTO users (full_name, card_number, pin_hash)
        VALUES (?, ?, ?)
    ");
            $stmt->execute([
                $user['full_name'],
                $user['card_number'],
                password_hash($user['pin'], PASSWORD_DEFAULT)
            ]);

            $user_id = $pdo->lastInsertId();

            // Insert into accounts table
            $stmt = $pdo->prepare("
        INSERT INTO accounts (user_id, balance)
        VALUES (?, ?)
    ");
            $stmt->execute([$user_id, $user['balance']]);
        }

        echo "✅ Users and accounts seeded.<br>";

        // --- TRANSACTIONS ---
        // We'll grab account IDs to link transactions correctly
        $accounts = $pdo->query("SELECT id FROM accounts ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Account 1 — Ahmed (busy transaction history)
        $ahmed = $accounts[0]['id'];
        $transactions_ahmed = [
            ['deposit',    2000.00, '2025-01-05 09:15:00'],
            ['deposit',    1500.00, '2025-01-10 11:30:00'],
            ['withdrawal',  300.00, '2025-01-12 14:00:00'],
            ['withdrawal',  150.00, '2025-01-18 16:45:00'],
            ['deposit',     500.00, '2025-02-01 08:00:00'],
            ['withdrawal',  200.00, '2025-02-14 13:20:00'],
            ['deposit',    1000.00, '2025-03-03 10:10:00'],
            ['withdrawal',  400.00, '2025-03-15 17:00:00'],
        ];

        // Account 2 — Sara (mostly deposits)
        $sara = $accounts[1]['id'];
        $transactions_sara = [
            ['deposit',    5000.00, '2025-01-02 08:00:00'],
            ['deposit',    3000.00, '2025-01-20 09:30:00'],
            ['withdrawal',  200.00, '2025-02-05 12:00:00'],
            ['deposit',    2000.00, '2025-02-20 15:00:00'],
            ['withdrawal',  500.00, '2025-03-01 11:00:00'],
        ];

        // Account 3 — Omar (low balance, mostly withdrawals)
        $omar = $accounts[2]['id'];
        $transactions_omar = [
            ['deposit',    1000.00, '2025-01-15 10:00:00'],
            ['withdrawal',  100.00, '2025-01-20 14:00:00'],
            ['withdrawal',   50.00, '2025-02-10 16:00:00'],
            ['withdrawal',   50.00, '2025-03-01 09:00:00'],
        ];

        // Merge all with their account id
        $all_transactions = array_merge(
            array_map(fn($t) => array_merge([$ahmed], $t), $transactions_ahmed),
            array_map(fn($t) => array_merge([$sara],  $t), $transactions_sara),
            array_map(fn($t) => array_merge([$omar],  $t), $transactions_omar)
        );

        $stmt = $pdo->prepare("
    INSERT INTO transactions (account_id, type, amount, created_at)
    VALUES (?, ?, ?, ?)
");

        foreach ($all_transactions as $t) {
            $stmt->execute($t);
        }

        echo "✅ Transactions seeded.<br>";
        echo "<br><strong>Test Accounts:</strong><br>";
        echo "Card: 1234567890123456 | PIN: 1234 | Name: Ahmed Ali<br>";
        echo "Card: 9876543210987654 | PIN: 5678 | Name: Sara Hassan<br>";
        echo "Card: 1111222233334444 | PIN: 9999 | Name: Omar Khaled<br>";
        ?>
```

---

Visit `localhost/atm-project/seed.php` once, you should see:
```
✅ Users and accounts seeded.
✅ Transactions seeded.

Test Accounts:
Card: 1234567890123456 | PIN: 1234 | Name: Ahmed Ali
Card: 9876543210987654 | PIN: 5678 | Name: Sara Hassan
Card: 1111222233334444 | PIN: 9999 | Name: Omar Khaled -->