            <?php
            $default_side_buttons = [
                'bt1-l' => ['label' => '', 'action' => ''],
                'bt1-r' => ['label' => '', 'action' => ''],
                'bt2-l' => ['label' => '', 'action' => ''],
                'bt2-r' => ['label' => '', 'action' => ''],
                'bt3-l' => ['label' => '', 'action' => ''],
                'bt3-r' => ['label' => '', 'action' => ''],
                'bt4-l' => ['label' => '', 'action' => ''],
                'bt4-r' => ['label' => '', 'action' => ''],
            ];

            $side_buttons = array_merge($default_side_buttons, $side_buttons ?? []);
            $side_slots = array_keys($default_side_buttons);

            foreach ($side_slots as $slot):
                $cfg = $side_buttons[$slot];
                $arrow = substr($slot, -2) === '-l' ? '▶' : '◀';
                $label = trim((string) ($cfg['label'] ?? ''));
                $action = (string) ($cfg['action'] ?? '');
                $btn_text = $label !== '' ? $label : $arrow;
            ?>
                <button
                    type="button"
                    class="side-btn <?= htmlspecialchars($slot) ?><?= $label !== '' ? ' has-label' : '' ?>"
                    data-slot="<?= htmlspecialchars($slot) ?>"
                    data-action="<?= htmlspecialchars($action) ?>"
                    aria-label="<?= htmlspecialchars($label !== '' ? $label : 'Side button') ?>">
                    <span class="side-btn-text"><?= htmlspecialchars($btn_text) ?></span>
                </button>
            <?php endforeach; ?>

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
                                <a class="key key-cancel" href="dashboard.php">CANCEL</a>
                                <button type="button" class="key key-correct" onclick="correctKey()">CORRECT</button>
                                <button type="button" class="key"></button>
                                <button type="button" class="key key-confirm" onclick="confirmKey()">CONFIRM</button>
                            </div>
                        </div>
                    </div>
              
            </div>

            </div>
            </div>


            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const sideButtons = document.querySelectorAll('.side-btn[data-action]');

                    function runDefaultSideAction(action) {
                        if (!action) return false;

                        if (action.startsWith('href:')) {
                            window.location.href = action.slice(5);
                            return true;
                        }

                        if (action.startsWith('js:')) {
                            const fnName = action.slice(3);
                            if (typeof window[fnName] === 'function') {
                                window[fnName]();
                                return true;
                            }
                        }

                        return false;
                    }

                    sideButtons.forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            const action = btn.dataset.action || '';
                            const slot = btn.dataset.slot || '';

                            if (typeof window.handleSideButtonAction === 'function') {
                                const handled = window.handleSideButtonAction(action, slot);
                                if (handled === true) return;
                            }

                            runDefaultSideAction(action);
                        });
                    });
                });
            </script>

            <?= $page_script ?? '' ?>

            </body>

            </html>