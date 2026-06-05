<?php $flashes = flash_all_pull(); ?>
<?php if (!empty($flashes)): ?>
    <div id="flash-container" class="fixed bottom-8 right-8 z-[60] flex flex-col gap-2">
        <?php foreach ($flashes as $key => $message): ?>
            <?php $cls = $key === 'error' ? 'bg-red-600' : ($key === 'success' ? 'bg-emerald-600' : 'bg-amber-500'); ?>
            <div class="flash-msg <?= $cls ?> text-white rounded-lg px-5 py-3 text-sm shadow-lg transition-all duration-500 opacity-100 translate-y-0">
                <?= e($message) ?>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
        (function () {
            document.querySelectorAll('.flash-msg').forEach(function (el) {
                setTimeout(function () {
                    el.style.opacity   = '0';
                    el.style.transform = 'translateY(8px)';
                    setTimeout(function () { el.remove(); }, 500);
                }, 5000);
            });
        })();
    </script>
<?php endif; ?>
