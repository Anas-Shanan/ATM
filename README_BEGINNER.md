# ATM2 Beginner Guide

This guide is for users with no PHP background.

## 1) What You Need

- XAMPP (Apache + MySQL)
- a project folder should be inside XAMPP\htdocs

## 2) Quick Setup (Windows + XAMPP)

1. Install XAMPP.
2. Move this project to:
   - `C:\xampp\htdocs\ATM`
3. Open XAMPP Control Panel.
4. Start:
   - Apache
   - MySQL
5. Open phpMyAdmin:
   - `http://localhost/phpmyadmin`
6. Create a database named:
   - `atm_db`
7. Run the SQL script in section 4 below.
8. Open the app:
   - `http://localhost/ATM/login.php`

## 3) Database Config

Default config is already set in [config/db.php](config/db.php):

- host: `localhost`
- database: `atm_db`
- user: `root`
- password: empty

If your local setup is different, update [config/db.php](config/db.php).

## 4) SQL Script (Copy and Run Once)

In phpMyAdmin, select `atm_db`, open SQL tab, and run this:

```sql
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    card_number VARCHAR(16) UNIQUE NOT NULL,
    pin_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
   FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    type ENUM('deposit', 'withdraw') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
   FOREIGN KEY (account_id) REFERENCES accounts(id)
);

CREATE TABLE IF NOT EXISTS user_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    card_number VARCHAR(16) NULL,
    action VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_logs_user_id (user_id),
    INDEX idx_user_logs_created_at (created_at)
);
```

## 5) First Usage

1. Open [registration.php](registration.php) to create an account.
2. Log in from [login.php](login.php).
3. Navigate using side buttons.
4. Use keypad Confirm for submit actions.

## 6) Troubleshooting

- If page shows DB connection error:
  - verify database name and credentials in [config/db.php](config/db.php)
- If tables are missing:
  - run section 4 SQL script again
- If Apache/MySQL cannot start:
  - check port conflicts in XAMPP (usually 80/443 for Apache, 3306 for MySQL)

## 7) Frontend Features Highlights

Current frontend features in this project:

- ATM-style machine layout with side action buttons and numeric keypad.
- Page-level screen sections with consistent title bar and live clock.
- Side-button navigation model across dashboard and operation pages.
- Keypad-friendly input flow for login, deposit, withdraw, and PIN change.
- Compact ATM button labels (WDR, DEP, STM, PIN, etc.) for quick navigation.
- Transaction tables with a consistent dark ATM visual style.
- Reusable status message blocks for success, warning, and error states.
