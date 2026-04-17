            <button class="side-btn bt1-l">&gt;</button>
            <button class="side-btn bt1-r">&lt;</button>

            <button class="side-btn bt2-l">&gt;</button>
            <button class="side-btn bt2-r">&lt;</button>

            <button class="side-btn bt3-l">&gt;</button>
            <button class="side-btn bt3-r">&lt;</button>

            <button class="side-btn bt4-l">&gt;</button>
            <button class="side-btn bt4-r">&lt;</button>
            </div>
            <!-- ↑↑↑ SCREEN CONTENT ends here ↑↑↑ -->

            <div class="container_cash cash-deposit">
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
                            <button type="button" class="key" onclick="addKey('1')">1</button>
                            <button type="button" class="key" onclick="addKey('2')">2</button>
                            <button type="button" class="key" onclick="addKey('3')">3</button>
                            <button type="button" class="key" onclick="addKey('4')">4</button>
                            <button type="button" class="key" onclick="addKey('5')">5</button>
                            <button type="button" class="key" onclick="addKey('6')">6</button>
                            <button type="button" class="key" onclick="addKey('7')">7</button>
                            <button type="button" class="key" onclick="addKey('8')">8</button>
                            <button type="button" class="key" onclick="addKey('9')">9</button>
                            <button type="button" class="key" onclick="addKey('.')">.</button>
                            <button type="button" class="key" onclick="addKey('0')">0</button>
                            <button type="button" class="key" onclick="addKey('00')">00</button>
                        </div>
                        <div class="keypad-actions">
                            <a class="key key-cancel" href="dashboard.php">Cancel</a>
                            <button type="button" class="key key-correct" onclick="correctKey()">Correct</button>
                            <button type="button" class="key key-confirm" onclick="confirmKey()">Confirm</button>
                        </div>
                    </div>
                </div>
            </div>

            </div>
            <!--       </div>
    </div>
    </div> -->


            <?= $page_script ?? '' ?>

            </body>

            </html>