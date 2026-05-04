<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Geldautomat' ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/style-src.css">

</head>

<body>
    <div class="container_atm">
        <div class="container_body">

            <div class="container_header header">
                <div class="title">

                    <h1>Geld <span class="slot-led-header"></span> automat
                    </h1>
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

                    <div class="card-slot-area">
                        <div class="slot-header">
                            <span class="slot-header-text">CARD</span>
                            <span class="slot-led"></span>
                        </div>
                        <div class="card-opening"></div>
                    </div>

                    <div class="sensor-pad">
                        <div class="sensor-indicator"></div>
                        <div class="sensor-led"></div>
                        <div class="sensor-inner">
                            <div class="img-container">
                                <img src="assets/chip1.svg" alt="">
                            </div>
                        </div>
                    </div>

                    <div class="camera-box">
                        <span class="camera1">
                            <span class="camera2"><span class="camera-led"></span></span>
                        </span>
                        
                    </div>
                </div>
            </div>

            <div class="container_screen screen">