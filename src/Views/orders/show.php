<?php /** @var array $order */ ?>
<?php
$status      = $order['status'];
$secondsLeft = max(0, (int) $order['seconds_left']);
$isPending   = $status === 'pending';
$isPaid      = $status === 'paid';
$isInactive  = in_array($status, ['expired', 'cancelled'], true);
$initialStep = $isPaid ? 3 : 1;
?>

<!-- Breadcrumb -->
<nav class="mb-6 text-sm text-gray-500 font-inter">
    <a href="/my/orders" class="hover:text-gray-700 transition-colors">我的訂單</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700">訂單 #<?= (int) $order['id'] ?></span>
</nav>

<div class="max-w-2xl mx-auto">

<?php if (!$isInactive): ?>
<!-- ── Stepper ── -->
<div class="flex items-center mb-8 select-none" id="stepper">
    <?php
    $steps = ['確認訂單', '填寫付款資料', '完成訂單'];
    foreach ($steps as $i => $label):
        $n = $i + 1;
        $isDone   = $n < $initialStep;
        $isActive = $n === $initialStep;
    ?>
        <div class="flex flex-col items-center" id="stepper-node-<?= $n ?>">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-200
                        <?= $isDone   ? 'bg-[#ea6d4a] text-white' : '' ?>
                        <?= $isActive ? 'bg-[#ea6d4a] text-white ring-4 ring-[#ea6d4a]/20' : '' ?>
                        <?= (!$isDone && !$isActive) ? 'border-2 border-gray-300 text-gray-400' : '' ?>">
                <?php if ($isDone): ?>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <?php else: ?>
                    <?= $n ?>
                <?php endif; ?>
            </div>
            <span class="text-xs mt-1.5 font-medium transition-colors duration-200
                         <?= ($isDone || $isActive) ? 'text-gray-800' : 'text-gray-400' ?>">
                <?= $label ?>
            </span>
        </div>
        <?php if ($n < 3): ?>
        <div class="flex-1 h-[2px] mx-2 mb-4 rounded-full transition-colors duration-200
                    <?= $isDone ? 'bg-[#ea6d4a]' : 'bg-gray-300' ?>"
             id="stepper-line-<?= $n ?>"></div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($isPending): ?>
<!-- ── Countdown bar ── -->
<div class="flex items-center justify-between bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-6 text-sm">
    <span id="countdown-note" class="text-amber-800">請於倒數結束前完成付款，逾時訂單將自動取消並釋出座位。</span>
    <span class="text-lg font-bold font-manrope text-amber-700 ml-4 tabular-nums"
          id="countdown" data-seconds="<?= $secondsLeft ?>">--:--</span>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════
     Panel 1 — 確認訂單
     ════════════════════════════════════════ -->
<?php if ($isPending): ?>
<div id="step-panel-1" class="bg-[#F5F5F5] rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

    <!-- Concert header -->
    <div class="px-6 py-5 border-b border-[#E0E0E0]">
        <h1 class="text-lg font-semibold font-manrope text-gray-900 mb-2"><?= e($order['concert_title']) ?></h1>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <span><?= e($order['venue']) ?></span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <span><?= e(date('Y年m月d日 H:i', strtotime($order['performed_at']))) ?></span>
        </div>
    </div>

    <!-- Order meta -->
    <div class="px-6 py-4 bg-[#EBEBEB] border-b border-[#E0E0E0] flex gap-6 text-xs text-gray-500">
        <span>訂單編號 <span class="font-medium text-gray-700">#<?= (int) $order['id'] ?></span></span>
        <span>建立時間 <span class="font-medium text-gray-700"><?= e(date('Y-m-d H:i', strtotime($order['created_at']))) ?></span></span>
    </div>

    <!-- Ticket items -->
    <div class="px-6 py-4">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">票券明細</h2>
        <div class="divide-y divide-[#E0E0E0]">
            <?php foreach ($order['items'] as $item): ?>
            <div class="py-3 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-800"><?= e($item['zone_name']) ?></p>
                    <p class="text-xs text-gray-400 mt-0.5">NT$ <?= number_format((float) $item['unit_price']) ?> × <?= (int) $item['quantity'] ?> 張</p>
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        <?php foreach ($item['seat_labels'] as $seat): ?>
                            <span class="inline-block text-xs bg-gray-100 text-gray-400 rounded px-1.5 py-0.5 opacity-60"><?= e($seat) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <span class="text-sm font-semibold text-gray-800 whitespace-nowrap">
                    NT$ <?= number_format((float) $item['unit_price'] * (int) $item['quantity']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Total -->
        <div class="flex justify-between items-center pt-4 mt-2 border-t border-[#E0E0E0]">
            <span class="text-sm font-semibold text-gray-700">總計</span>
            <span class="text-xl font-bold font-manrope text-gray-900">NT$ <?= number_format((float) $order['total_amount']) ?></span>
        </div>
    </div>

    <!-- Footer action -->
    <div class="px-6 py-4 bg-[#EBEBEB] border-t border-[#E0E0E0] flex justify-end">
        <button type="button" onclick="goToStep(2)"
                class="bg-[#ea6d4a] text-white text-sm font-semibold px-6 py-2.5 rounded-xl hover:bg-[#d85c38] active:scale-95 transition-all">
            下一步：填寫付款資料 →
        </button>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════
     Panel 2 — 填寫付款資料
     ════════════════════════════════════════ -->
<?php if ($isPending): ?>
<div id="step-panel-2" class="hidden bg-[#F5F5F5] rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

    <!-- Amount reminder -->
    <div class="px-6 py-4 border-b border-[#E0E0E0] flex items-center justify-between gap-10">
        <span class="text-sm text-gray-500"><?= e($order['concert_title']) ?></span>
        <span class="text-xl font-bold font-manrope text-gray-900">NT$ <?= number_format((float) $order['total_amount']) ?></span>
    </div>

    <!-- Payment form -->
    <form method="post" action="/orders/<?= (int) $order['id'] ?>/pay" id="payment-form" class="px-6 py-6 space-y-4">
        <?= csrf_field() ?>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5" for="card-name">持卡人姓名</label>
            <input type="text" id="card-name" name="card_name" placeholder="王小明" required autocomplete="cc-name"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300
                          focus:outline-none focus:ring-2 focus:ring-[#ea6d4a]/20 focus:border-[#ea6d4a] transition">
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5" for="card-number">卡號</label>
            <input type="text" id="card-number" name="card_number" placeholder="1234 5678 9012 3456"
                   maxlength="19" required autocomplete="cc-number"
                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300
                          focus:outline-none focus:ring-2 focus:ring-[#ea6d4a]/20 focus:border-[#ea6d4a] transition font-mono tracking-wider">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5" for="card-expiry">有效期限</label>
                <input type="text" id="card-expiry" name="card_expiry" placeholder="MM / YY"
                       maxlength="7" required autocomplete="cc-exp"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300
                              focus:outline-none focus:ring-2 focus:ring-[#ea6d4a]/20 focus:border-[#ea6d4a] transition font-mono">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5" for="card-cvv">安全碼</label>
                <input type="text" id="card-cvv" name="card_cvv" placeholder="CVV"
                       maxlength="3" required autocomplete="cc-csc"
                       class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm text-gray-800 placeholder-gray-300
                              focus:outline-none focus:ring-2 focus:ring-[#ea6d4a]/20 focus:border-[#ea6d4a] transition font-mono">
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-2">
            <button type="button" onclick="goToStep(1)"
                    class="text-sm text-gray-500 hover:text-gray-800 transition-colors px-2 py-2">
                ← 上一步
            </button>
            <button type="submit" id="pay-btn"
                    class="bg-[#ea6d4a] text-white text-sm font-semibold px-6 py-2.5 rounded-xl hover:bg-[#d85c38] active:scale-95 transition-all
                           disabled:opacity-40 disabled:cursor-not-allowed disabled:scale-100">
                確認付款
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════
     Panel 3 — 完成訂單
     ════════════════════════════════════════ -->
<?php if ($isPaid): ?>
<div id="step-panel-3" class="bg-[#F5F5F5] rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

    <!-- Success header -->
    <div class="px-6 pt-8 pb-6 text-center border-b border-[#E0E0E0]">
        <div class="w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold font-manrope text-gray-900 mb-1">付款完成！</h1>
        <p class="text-sm text-gray-500">
            已於 <?= e(date('Y年m月d日 H:i', strtotime($order['paid_at']))) ?> 完成付款
        </p>
    </div>

    <!-- Concert info -->
    <div class="px-6 py-4 border-b border-[#E0E0E0]">
        <h2 class="text-base font-semibold font-manrope text-gray-900 mb-2"><?= e($order['concert_title']) ?></h2>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <span><?= e($order['venue']) ?></span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <span><?= e(date('Y年m月d日 H:i', strtotime($order['performed_at']))) ?></span>
        </div>
    </div>

    <!-- Electronic tickets -->
    <div class="px-6 py-4">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">電子票券</h2>
        <div class="divide-y divide-[#E0E0E0]">
            <?php foreach ($order['items'] as $item): ?>
            <div class="py-3">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-800"><?= e($item['zone_name']) ?></span>
                    <span class="text-sm font-semibold text-gray-800">× <?= (int) $item['quantity'] ?> 張</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <?php foreach ($item['seat_labels'] as $seat): ?>
                        <span class="inline-block text-xs bg-[#ea6d4a] text-white rounded-lg px-2.5 py-1 font-medium"><?= e($seat) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="flex justify-between items-center pt-4 mt-2 border-t border-[#E0E0E0]">
            <span class="text-sm font-semibold text-gray-700">總計</span>
            <span class="text-xl font-bold font-manrope text-gray-900">NT$ <?= number_format((float) $order['total_amount']) ?></span>
        </div>
    </div>

    <div class="px-6 py-4 bg-[#EBEBEB] border-t border-[#E0E0E0]">
        <a href="/my/orders" class="block text-center text-sm font-semibold text-[#ea6d4a] hover:text-[#d85c38] transition-colors">
            查看我的訂單
        </a>
    </div>
</div>
<?php endif; ?>

<!-- ════════════════════════════════════════
     Expired / Cancelled
     ════════════════════════════════════════ -->
<?php if ($isInactive): ?>
<div class="bg-[#F5F5F5] rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-[#E0E0E0]">
        <h1 class="text-lg font-semibold font-manrope text-gray-900 mb-2"><?= e($order['concert_title']) ?></h1>
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
            <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <span><?= e($order['venue']) ?></span>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <span><?= e(date('Y年m月d日 H:i', strtotime($order['performed_at']))) ?></span>
        </div>
    </div>
    <div class="px-6 py-8 text-center">
        <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-gray-600">
            <?= $status === 'expired' ? '此訂單已過期，座位已釋出。' : '此訂單已取消。' ?>
        </p>
        <a href="/concerts" class="inline-block mt-4 text-sm font-semibold text-[#ea6d4a] underline underline-offset-2 hover:no-underline">
            重新選購票券
        </a>
    </div>
</div>
<?php endif; ?>

</div><!-- /max-w-2xl -->

<?php if ($isPending): ?>
<script>
(function () {
    var STEP_CLASSES = {
        active:   ['bg-[#ea6d4a]', 'text-white', 'ring-4', 'ring-[#ea6d4a]/20'],
        done:     ['bg-[#ea6d4a]', 'text-white'],
        inactive: ['border-2', 'border-gray-300', 'text-gray-400'],
    };
    var LABEL_ACTIVE   = ['text-gray-800'];
    var LABEL_INACTIVE = ['text-gray-400'];

    function setNodeState(n, state) {
        var circle = document.querySelector('#stepper-node-' + n + ' div');
        var label  = document.querySelector('#stepper-node-' + n + ' span');
        if (!circle) return;

        Object.values(STEP_CLASSES).forEach(function (cls) { circle.classList.remove.apply(circle.classList, cls); });
        circle.classList.add.apply(circle.classList, STEP_CLASSES[state]);

        LABEL_ACTIVE.concat(LABEL_INACTIVE).forEach(function (c) { label.classList.remove(c); });
        label.classList.add.apply(label.classList, (state !== 'inactive') ? LABEL_ACTIVE : LABEL_INACTIVE);

        if (state === 'done') {
            circle.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
        } else {
            circle.textContent = n;
        }
    }

    function setLineState(n, filled) {
        var line = document.getElementById('stepper-line-' + n);
        if (!line) return;
        line.classList.toggle('bg-[#ea6d4a]', filled);
        line.classList.toggle('bg-gray-300', !filled);
    }

    window.goToStep = function (n) {
        document.getElementById('step-panel-1').classList.toggle('hidden', n !== 1);
        document.getElementById('step-panel-2').classList.toggle('hidden', n !== 2);

        if (n === 1) {
            setNodeState(1, 'active');
            setNodeState(2, 'inactive');
            setLineState(1, false);
        } else {
            setNodeState(1, 'done');
            setNodeState(2, 'active');
            setLineState(1, true);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // Format card number input with spaces
    var cardInput = document.getElementById('card-number');
    if (cardInput) {
        cardInput.addEventListener('input', function () {
            var v = this.value.replace(/\D/g, '').slice(0, 16);
            this.value = v.replace(/(.{4})/g, '$1 ').trim();
        });
    }

    // Format expiry input
    var expiryInput = document.getElementById('card-expiry');
    if (expiryInput) {
        expiryInput.addEventListener('input', function () {
            var v = this.value.replace(/\D/g, '').slice(0, 4);
            if (v.length >= 3) {
                this.value = v.slice(0, 2) + ' / ' + v.slice(2);
            } else {
                this.value = v;
            }
        });
    }
}());
</script>
<script src="/assets/js/countdown.js"></script>
<?php endif; ?>
