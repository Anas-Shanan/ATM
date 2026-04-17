<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DRK Geldautmat</title>
    <link rel="stylesheet" href="../assets/style.css">

</head>

<body>
    <div class="container_atm">
        <div class="container_body">
            <div class="container_header header">
                <div class="title">
                    <h1>Geldautmat</h1>
                </div>
            </div>
            <div class="card-panel">
                <div class="right-panel">
                    <div class="receipt-slot">
                        <div class="slot-header">
                            <span class="slot-header-text">RECEIPT</span>
                            <span class="slot-led"></span>
                        </div>
                        <div class="receipt-opening"></div>
                    </div>

                    <div class="card-slot-area ">
                        <div class="slot-header">
                            <span class="slot-header-text">CARD</span>
                            <span class="slot-led"></span>
                        </div>
                        <div class="card-opening"></div>
                    </div>

                    <div class="sensor-pad ">
                        <div class="sensor-indicator"></div>
                        <div class="sensor-inner">
                            <div class="img-container"><img src="../assets/chip_9405771.png" alt=""></div>
                            <div class="sensor-led"></div>
                        </div>
                    </div>
                    <div class="camera-box"></div>
                </div>

            </div>
            <div class="container_screen screen">screen is here</div>
            <div class="container_cash cash-deposit ">
                <div class="cash-slot-area">
                    <div class="cash-slot-label">
                        <span class="cash-slot-text">CASH</span>
                        <span class="slot-led"></span>
                    </div>
                    <div class="cash-slot"></div>
                   
                </div>
            </div>
            <div class="container-keypad keypad">
                <div class="keypad-wrapper">
                    <div class="keypad-inner">
                        <div class="keypad-nums">
                            <button class="key" onclick="addKey('1')">1</button>
                            <button class="key" onclick="addKey('2')">2</button>
                            <button class="key" onclick="addKey('3')">3</button>
                            <button class="key" onclick="addKey('4')">4</button>
                            <button class="key" onclick="addKey('5')">5</button>
                            <button class="key" onclick="addKey('6')">6</button>
                            <button class="key" onclick="addKey('7')">7</button>
                            <button class="key" onclick="addKey('8')">8</button>
                            <button class="key" onclick="addKey('9')">9</button>
                            <button class="key" onclick="addKey('.')">.</button>
                            <button class="key" onclick="addKey('0')">0</button>
                            <button class="key" onclick="addKey('00')">00</button>
                        </div>
                        <div class="keypad-actions">
                            <a class="key key-cancel" href="../dashboard.php" style="text-decoration:none">Cancel</a>
                            <button class="key key-correct">Correct</button>
                            <button class="key key-confirm">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>