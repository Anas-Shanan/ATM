<?php 
$host = "localhost";
$dbname = "bankautomate";
$user = "root";
$password = "";
/* try{
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password); // this database connector 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // throw error
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // to return results as associative arrays
    }catch(PDOException $e)
    {
        die("DB Connection faild". $e->getMessage());
    } */

        //MySQLi (object-oriented style) 

    $conn = new mysqli($host, $user, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    define('PEPPER', 's3cr3tPepper!@#');
?>