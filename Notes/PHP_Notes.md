# PHP code is executed on the server, and the result is returned to the browser as HTML

# What Can PHP Do?

    - PHP can generate dynamic page content
    - PHP can create, open, read, write, delete, and close files on the server
    - PHP can collect form data
    - PHP can send and receive cookies
    - PHP can add, delete, modify data in your database
    - PHP can be used to control user-access
    - PHP can encrypt data

# Basics

    - browser = customer
    - Apache → Web server = waiter
    - PHP → Backend language = Chef
    - MySQL (MariaDB) → Database = Refrigerator (database)
    - phpMyAdmin → Database management tool

# flow

Flow:

    - Browser → sends request
    - Apache receives it
    - PHP runs the file
    - PHP connects to MySQL server
    - PHP sends SQL query
    - MySQL returns data
    - PHP converts data into HTML
    - Browser shows result

## echo vs print

    - echo has no return value, while print has a return value of 1 so it can be used in expressions
    - echo can take multiple parameters, while print can take only one argument
    - echo is marginally faster than print

## single or double quotes

    -When using double quotes, variables can be inserted to the string:
      echo "<p>Study PHP at $txt2</p>";

    -When using single quotes, variables have to be inserted using the . operator:
    echo '<p>Study PHP at ' . $txt2 . '</p>';

## stings functions

    -strlen() function returns the length of a string.
    -str_word_count()
    -str_contains()
    -strtoupper()
    -strtolower()
    -str_replace()
    - explode() function splits a string into an array.
    - substr($x, -5, 3); three letters from index -5

## numbers functions

    -is_int()
    -is_float()
    -is_nan()
    -is_nomeric()
    - intval() : Get the integer value of a variable:
    rand(10, 100): give a random number between 10 and 100

## constant

define(CONSTANT_NAME, value);
const CONSTANT-NAME = value

## var_dump()

Debug variables
Check data types
Inspect arrays or objects
Understand what's being stored in a variable

## Match

    -The match expression evaluates an expression against multiple alternatives (using strict comparison) and returns a value

_Here are the key differences between match and switch:_
-A match expression has a more readable syntax than switch
-A match expression returns a value, while switch does not
-A match expression breaks automatically after a match, while switch requires break;
-A match expression has strict comparison (===), while switch uses loose comparison (==)

    ```php
        $d = 3;

        $text = match($d) {
        1, 2, 3, 4, 5 => "The week feels so long!",
        6, 0 => "Weekends are best!",
        default => "Invalid day",
        };

        echo $text; // return the week feeils so long

    ------------------------------------------------

    $favcolor = "pink"; // no conditions will match this

    try {
    $text = match($favcolor) {
        "red" => "Your favorite color is red!",
        "blue" => "Your favorite color is blue!",
        "green" => "Your favorite color is green!",
    };
    } catch (\UnhandledMatchError $e) {
    var_dump($e);
    }

    echo $text;//
    ```

## loops (same js ) two things extra:

_(break)_ to break the loop and stop it. _empty return_ do the same in JS

    ```php
    for ($x = 0; $x < 10; $x++) {
    if ($x == 4) {
        break;
    }
    echo "The number is: $x <br>";
    }
    ```

_(continue)_
The PHP continue statement is used to skip the current iteration of a loop, and continue with the next iteration.

## superglobals

$GOLBALS _ an array that contains refernces to all the $golabl variables 
$\_SERVER inof about the web server
$_REQUEST an array containing data from _GET _POST $_COOKIES
$\_SESSION an array of session variables
$\_FILES An array of items uploaded to the current script via the HTTP POST method (filename, type, size)

    ```php
    echo "<pre>";
    print_r($_SERVER);
    echo "</pre>";

    ```

    ```php

    <form method="post" action="<?php echo $_SERVER["PHP_SELF"];?>">

    ```

### The htmlspecialchars() function

converts special characters to HTML entities to avoid exploit attempt, for security.

    ```php
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">

    ```

### data and time

    ```php
    $ts = time()
    $curDate = date('Y-m-d H:i:s', $ts);
    ```

## cookies

setcookie(name(just this required), value, expire, path, domain, secure, httponly);
storing information: - Username - Maintain login status - Shopping cart contents - Language preferences

## session functions

    - session_start() - Starts a new session
    - $_SESSION - Stores and access session variables
    - unset() - Removes a specific session variable (e.g unset($_SESSION["favcolor"]))
    - session_destroy() - Destroys all data associated with the current session
    - session_unset() - Frees all session variables

    ```php

        session_start();

    $_SESSION['user_id'] = $user['id'];

    // then in protected pages
    session_start();

    if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
    }

    ```

### filters

there are two main types
    -validation filters: check email, url, integer and ip address.
    -santization filters: remove HTML tags ,special characters and clean email strings.

 filter_var() filters a variable
 filter_input() filters external input

### JSON
    -json_decode()
    -json_encode()

