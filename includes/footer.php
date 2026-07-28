<?php
?>
            </main>
            <footer class="app-footer">
                <div class="footer-container">
                    <p>&copy; <?= date('Y') ?> <strong>Grand Royale Hotel & Suites</strong>. All rights reserved.</p>
                    <p class="footer-meta">Powered by Antigravity HMS Engine v1.0</p>
                </div>
            </footer>
        </div>
    </div>

    <!-- Core JavaScript -->
    <script src="js/main.js"></script>
    <script src="js/validation.js"></script>
    
    <?php if (isset($extra_js) && is_array($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="js/<?= escape($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
