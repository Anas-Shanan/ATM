# general ideas
    -`SQL` (Structured Query Language) is a language.
    -`MySQL` is a database management system (DBMS). it is a software that manages database 
    -`SQL`= is a language VS `MySQL` = A person who understands and speaks that language
   
    -MySQLi = MySQL Improved



## how to connent MYSQL 
```php
<?php
    $servername = "localhost";
    $username = "usermname";
    $passwprd = "password";
    $dbname  = "mydb"

    $conn = new mysqli($servername, $username, $password, $dbname);

    if($conn->connect_error){
        die("Connection failed: ".$conn->connect_error);
    }
    echo "Connected successfully";

    // 
?>
```

### create a table 

```sql 
   CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY;
        name VARCHAR(50) NOT NULL,
        email VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL;
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
```
     
   
    AUTO_INCREMENT: MySQL automatically increases the value of the field by 1 each time a new record is added
    PRIMARY KEY: Used to uniquely identify the rows in a table. The column with PRIMARY KEY setting is often an ID number, and is often used with AUTO_INCREMENT
    NOT NULL: Each row must contain a value for that column, null values are not allowed
    FOREIGN KEY: is a field (or collection of fields) in one table, that refers to the PRIMARY KEY in another table. 


### insert DATA 

 ```SQL
    INSERT INTO users (name, email, password)
    VALUES ('Anas', 'anas@gmail.com', '123456'),
    ('Mary', 'mary@example.com',"7654321"),
    ('Max', 'julie@example.com','max123');

    SELECT * FROM users WHERE email = 'anas@gmail.com';
    SELECT id, name, email FROM users WHERE id=1;
  
    UPDATE users SET name = 'John' WHERE id = 1;
    UPDATE users SET balance = balance + 500 WHERE id=1
    SELECT * FROM transcations WHERE id = 2 ORDER BY created_at DESC LIMIT 10;
 ```

 ```sql 
// the initial db for the project:

 CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    card_number VARCHAR(16) UNIQUE NOT NULL,
    pin_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

 CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
    CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    type ENUM('deposit', 'withdraw') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    net_balance DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id)
);

 ```

 users: 

 user id- 1:/ max sha 1234 /card number 1111222233334444 / acount id 3;
 user id_ 2:/ Sara Ali 4567 / card number 5555666677778888 / acount id 4


how to use join 

```sql 
-- Get user info + their account balance in ONE query
SELECT 
    users.full_name,
    accounts.account_number,
    accounts.balance
FROM users
JOIN accounts ON accounts.user_id = users.id
WHERE users.card_number = '1234567890123456';
```