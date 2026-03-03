<?php
// 1. Create variables
$fruits = ["apple", "banana", "orange"];
$prices = [
    "apple" => 1.50,
    "banana" => 0.80,
    "orange" => 2.00
];

// 2. Function to calculate total
function calculateTotal($items, $prices)
{
    $total = 0;
    foreach ($items as $item) {
        if (isset($prices[$item])) {
            $total += $prices[$item];
        }
    }
    return $total;
}

// 3. Use the function
$cart = ["apple", "orange", "apple"];
$total = calculateTotal($cart, $prices);
?>

<!DOCTYPE html>
<html>

<body>
    <h1>Shopping Cart</h1>

    <ul>
        <?php foreach ($cart as $item): ?>
            <li>
                <?php echo $item; ?> - $
                <?php echo $prices[$item]; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <h2>Total: $
        <?php echo number_format($total, 2); ?>
    </h2>

    <?php if ($total < 9):
        array_push($cart, "kiwi", "lemon") && $prices += ["kiwi" => 1, "lemon" => 4];
    ?>
        <p style="color: red;">no enough items in card cart!</p>
        <ul>
            <?php foreach ($cart as $item): ?>
                <li><?php echo $item; ?> - $<?php echo $prices[$item]; ?></li>
            <?php endforeach; ?>
        </ul>
        <h2>Total: $
            <?php echo number_format($total, 2); ?>
        </h2>
    <?php else: ?>
        <p style="color: green;">Good deal!</p>
    <?php endif; ?>
</body>

</html>