<?php /** @var array $order */ ?>

<!-- Breadcrumb -->
<nav class="mb-6 text-sm text-gray-500">
    <a href="/my/orders" class="hover:text-gray-700 transition-colors">我的訂單</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700">電子票券 #<?= (int) $order['id'] ?></span>
</nav>

<div class="max-w-xl mx-auto">

    <!-- Concert header -->
    <div class="mb-6">
        <?php if (!empty($order['poster_url'])): ?>
        <div class="w-full rounded-2xl overflow-hidden mb-4 shadow-sm" style="max-height: 220px;">
            <img src="<?= e($order['poster_url']) ?>"
                 alt="<?= e($order['concert_title']) ?>"
                 class="w-full object-cover" style="max-height: 220px;">
        </div>
        <?php endif; ?>

        <h1 class="text-2xl font-bold text-slate-900 mb-3" style="font-family: Manrope, sans-serif;">
            <?= e($order['concert_title']) ?>
        </h1>
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                </svg>
                <?= e($order['venue']) ?>
            </div>
            <div class="flex items-center gap-2 text-sm text-slate-500">
                <svg class="w-4 h-4 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
                <?= e(date('Y年m月d日 H:i', strtotime($order['performed_at']))) ?>
            </div>
        </div>
    </div>

    <!-- Ticket cards -->
    <div class="space-y-4 mb-8">
        <?php foreach ($order['items'] as $item): ?>
            <?php foreach ($item['seat_labels'] as $seatIndex => $seat): ?>
            <div class="ticket-scene" style="perspective: 900px;">
                <div class="ticket-card" style="
                    will-change: transform;
                    transform: translateZ(0);
                    transition: transform 0.12s ease-out;
                    border-radius: 16px;
                    overflow: hidden;
                    filter: drop-shadow(0 8px 24px rgba(0,0,0,0.10));
                    display: flex;
                    background: #F1F1F1;
                ">
                    <!-- Orange accent bar -->
                    <div style="width: 5px; background: #EA6D4A; flex-shrink: 0;"></div>

                    <!-- Ticket body -->
                    <div style="flex: 1; display: flex; flex-direction: column; overflow: hidden;">

                        <!-- Top section -->
                        <div style="padding: 18px 20px 14px 20px;">
                            <p style="font-family: Manrope, sans-serif; font-weight: 700; font-size: 15px; color: #0f172a; margin: 0 0 4px; line-height: 1.3;">
                                <?= e($order['concert_title']) ?>
                            </p>
                            <p style="font-size: 12px; color: #64748b; margin: 0;">
                                <?= e($item['zone_name']) ?>
                            </p>
                        </div>

                        <!-- Perforated divider -->
                        <div style="position: relative; margin: 0 20px; flex-shrink: 0;">
                            <div style="border-top: 2px dashed #e2e8f0;"></div>
                            <div style="position: absolute; left: -28px; top: -8px; width: 16px; height: 16px; background: #F5F5F5; border-radius: 50%;"></div>
                            <div style="position: absolute; right: -28px; top: -8px; width: 16px; height: 16px; background: #F5F5F5; border-radius: 50%;"></div>
                        </div>

                        <!-- Bottom section -->
                        <div style="padding: 14px 20px 18px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                            <div>
                                <p style="font-size: 10px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin: 0 0 3px;">座位</p>
                                <p style="font-family: Manrope, sans-serif; font-size: 24px; font-weight: 800; color: #EA6D4A; margin: 0; line-height: 1.1;">
                                    <?= e($seat) ?>
                                </p>
                            </div>

                            <!-- QR code — click to enlarge -->
                            <button
                                type="button"
                                onclick="openQr(<?= $seatIndex ?>, '<?= e(addslashes($seat)) ?>')"
                                title="點擊放大 QR Code"
                                style="background: #f8fafc; border-radius: 10px; padding: 8px; flex-shrink: 0; border: none; cursor: zoom-in; transition: transform 0.15s ease;"
                                onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'"
                            >
                                <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="2" width="22" height="22" rx="2" fill="none" stroke="#0f172a" stroke-width="2.5"/>
                                    <rect x="7" y="7" width="12" height="12" fill="#0f172a"/>
                                    <rect x="36" y="2" width="22" height="22" rx="2" fill="none" stroke="#0f172a" stroke-width="2.5"/>
                                    <rect x="41" y="7" width="12" height="12" fill="#0f172a"/>
                                    <rect x="2" y="36" width="22" height="22" rx="2" fill="none" stroke="#0f172a" stroke-width="2.5"/>
                                    <rect x="7" y="41" width="12" height="12" fill="#0f172a"/>
                                    <rect x="36" y="36" width="4" height="4" fill="#0f172a"/>
                                    <rect x="42" y="36" width="4" height="4" fill="#0f172a"/>
                                    <rect x="48" y="36" width="4" height="4" fill="#0f172a"/>
                                    <rect x="54" y="36" width="4" height="4" fill="#0f172a"/>
                                    <rect x="36" y="42" width="4" height="4" fill="#0f172a"/>
                                    <rect x="48" y="42" width="4" height="4" fill="#0f172a"/>
                                    <rect x="36" y="48" width="4" height="4" fill="#0f172a"/>
                                    <rect x="42" y="48" width="4" height="4" fill="#0f172a"/>
                                    <rect x="54" y="48" width="4" height="4" fill="#0f172a"/>
                                    <rect x="36" y="54" width="4" height="4" fill="#0f172a"/>
                                    <rect x="48" y="54" width="4" height="4" fill="#0f172a"/>
                                    <rect x="54" y="54" width="4" height="4" fill="#0f172a"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>

    <!-- Footer info -->
    <div class="bg-[#F5F5F5] rounded-2xl px-5 py-4 flex items-center justify-between text-sm">
        <div class="text-slate-500">
            訂單 #<?= (int) $order['id'] ?> ·
            付款於 <?= e(date('Y-m-d H:i', strtotime($order['paid_at']))) ?>
        </div>
        <div class="font-bold text-slate-900" style="font-family: Manrope, sans-serif;">
            NT$ <?= number_format((float) $order['total_amount']) ?>
        </div>
    </div>

</div>

<!-- QR code modal -->
<div id="qr-modal" onclick="closeQr()" style="
    display: none;
    position: fixed; inset: 0; z-index: 50;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
    cursor: pointer;
">
    <div onclick="event.stopPropagation()" style="
        background: white;
        border-radius: 24px;
        padding: 32px;
        max-width: 320px;
        width: 90%;
        text-align: center;
        cursor: default;
        animation: qr-pop 0.2s ease-out;
    ">
        <p id="qr-seat-label" style="font-family: Manrope, sans-serif; font-size: 13px; color: #64748b; margin: 0 0 16px;"></p>
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
            <svg width="200" height="200" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="2" y="2" width="22" height="22" rx="2" fill="none" stroke="#0f172a" stroke-width="2.5"/>
                <rect x="7" y="7" width="12" height="12" fill="#0f172a"/>
                <rect x="36" y="2" width="22" height="22" rx="2" fill="none" stroke="#0f172a" stroke-width="2.5"/>
                <rect x="41" y="7" width="12" height="12" fill="#0f172a"/>
                <rect x="2" y="36" width="22" height="22" rx="2" fill="none" stroke="#0f172a" stroke-width="2.5"/>
                <rect x="7" y="41" width="12" height="12" fill="#0f172a"/>
                <rect x="36" y="36" width="4" height="4" fill="#0f172a"/>
                <rect x="42" y="36" width="4" height="4" fill="#0f172a"/>
                <rect x="48" y="36" width="4" height="4" fill="#0f172a"/>
                <rect x="54" y="36" width="4" height="4" fill="#0f172a"/>
                <rect x="36" y="42" width="4" height="4" fill="#0f172a"/>
                <rect x="48" y="42" width="4" height="4" fill="#0f172a"/>
                <rect x="36" y="48" width="4" height="4" fill="#0f172a"/>
                <rect x="42" y="48" width="4" height="4" fill="#0f172a"/>
                <rect x="54" y="48" width="4" height="4" fill="#0f172a"/>
                <rect x="36" y="54" width="4" height="4" fill="#0f172a"/>
                <rect x="48" y="54" width="4" height="4" fill="#0f172a"/>
                <rect x="54" y="54" width="4" height="4" fill="#0f172a"/>
            </svg>
        </div>
        <p id="qr-seat-big" style="font-family: Manrope, sans-serif; font-size: 20px; font-weight: 800; color: #EA6D4A; margin: 0 0 20px;"></p>
        <button onclick="closeQr()" style="
            background: #f1f5f9; border: none; border-radius: 12px;
            padding: 10px 24px; font-size: 14px; font-weight: 600;
            color: #475569; cursor: pointer; width: 100%;
        ">關閉</button>
    </div>
</div>

<style>
@keyframes qr-pop {
    from { opacity: 0; transform: scale(0.88); }
    to   { opacity: 1; transform: scale(1); }
}
</style>

<script>
(function () {
    /* 3D tilt */
    var rafId = null;

    document.querySelectorAll('.ticket-card').forEach(function (card) {
        var targetX = 0, targetY = 0;
        var currentX = 0, currentY = 0;
        var isHovered = false;

        function animate() {
            if (!isHovered) return;
            currentX += (targetX - currentX) * 0.15;
            currentY += (targetY - currentY) * 0.15;
            card.style.transform = 'translateZ(0) rotateY(' + currentX + 'deg) rotateX(' + currentY + 'deg)';
            rafId = requestAnimationFrame(animate);
        }

        card.addEventListener('mouseenter', function () {
            isHovered = true;
            animate();
        });

        card.addEventListener('mousemove', function (e) {
            var rect = card.getBoundingClientRect();
            var x = (e.clientX - rect.left) / rect.width - 0.5;
            var y = (e.clientY - rect.top) / rect.height - 0.5;
            targetX = x * 12;
            targetY = -y * 12;
        });

        card.addEventListener('mouseleave', function () {
            isHovered = false;
            cancelAnimationFrame(rafId);
            card.style.transition = 'transform 0.35s ease-out';
            card.style.transform = 'translateZ(0) rotateY(0deg) rotateX(0deg)';
            setTimeout(function () {
                card.style.transition = 'transform 0.12s ease-out';
                currentX = 0; currentY = 0; targetX = 0; targetY = 0;
            }, 350);
        });
    });

    /* QR modal */
    var modal = document.getElementById('qr-modal');

    window.openQr = function (index, seat) {
        document.getElementById('qr-seat-label').textContent = seat;
        document.getElementById('qr-seat-big').textContent = seat;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeQr = function () {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeQr();
    });
}());
</script>
