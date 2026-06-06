<?php /** @var array $concert */ ?>
<?php $u = current_user(); ?>

<nav class="mb-4 text-sm text-slate-500">
    <a href="/" class="hover:text-slate-900">演唱會</a>
    <span class="mx-2">/</span>
    <span class="text-slate-900"><?= e($concert['title']) ?></span>
</nav>

<?php if (!empty($concert['poster_url'])): ?>
    <div class="w-full overflow-hidden rounded-2xl shadow-sm">
        <img src="<?= e($concert['poster_url']) ?>" class="w-full object-contain" alt="<?= e($concert['title']) ?>">
    </div>
<?php else: ?>
    <div class="flex h-[464px] w-full items-center justify-center rounded-2xl bg-slate-900 text-6xl text-white shadow-sm">🎟️</div>
<?php endif; ?>

<div class="mt-6 flex items-center gap-8">
    <div class="flex-1">
        <h1 class="font-manrope text-[28px] font-bold leading-normal text-[#4b4b4b]"><?= e($concert['title']) ?></h1>
    </div>

    <div class="w-px self-stretch bg-[#a6a6a6]"></div>

    <div class="flex shrink-0 flex-col gap-2.5">
        <div class="flex items-center gap-3">
            <svg class="h-6 w-6 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
            </svg>
            <span class="font-inter text-[18px] font-medium text-[#4b4b4b]"><?= e($concert['venue']) ?></span>
        </div>
        <div class="flex items-center gap-3">
            <svg class="h-6 w-6 shrink-0 text-[#ea6d4a]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <span class="font-inter text-[18px] font-medium text-[#4b4b4b]"><?= e(date('Y.m.d', strtotime($concert['performed_at']))) ?></span>
        </div>
    </div>
</div>

<div class="mt-10 flex gap-8">

    <!-- 左側：Tab 內容 -->
    <div class="min-w-0 flex-1">
        <div class="flex border-b border-[#a6a6a6]">
            <button class="tab-btn border-b-2 border-slate-900 px-5 py-2 text-sm font-medium text-slate-900" data-tab="program">節目介紹</button>
            <button class="tab-btn border-b-2 border-transparent px-5 py-2 text-sm font-medium text-slate-500" data-tab="price">票價資訊</button>
            <button class="tab-btn border-b-2 border-transparent px-5 py-2 text-sm font-medium text-slate-500" data-tab="notices">注意事項</button>
        </div>

        <div id="tab-program" class="tab-panel mt-4 rounded border border-[#a6a6a6] px-7 py-5">
            <?php if (!empty($concert['program_intro'])): ?>
                <p class="leading-7 text-[#4b4b4b]"><?= nl2br(e($concert['program_intro'])) ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-400">尚無節目介紹。</p>
            <?php endif; ?>
        </div>

        <div id="tab-price" class="tab-panel mt-4 hidden rounded border border-[#a6a6a6] px-7 py-5">
            <?php if (!empty($concert['venue_map_url'])): ?>
                <img src="<?= e($concert['venue_map_url']) ?>" alt="場地圖" class="mb-5 w-full max-w-sm rounded object-contain">
            <?php endif; ?>
            <?php if (!empty($concert['price_info'])): ?>
                <p class="leading-7 text-[#4b4b4b]"><?= nl2br(e($concert['price_info'])) ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-400">尚無票價資訊。</p>
            <?php endif; ?>
        </div>

        <div id="tab-notices" class="tab-panel mt-4 hidden rounded border border-[#a6a6a6] px-7 py-5">
            <?php if (!empty($concert['notices'])): ?>
                <p class="leading-7 text-[#4b4b4b]"><?= nl2br(e($concert['notices'])) ?></p>
            <?php else: ?>
                <p class="text-sm text-slate-400">尚無注意事項。</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- 右側：票種選擇（固定顯示） -->
    <div class="w-[280px] shrink-0">
        <h2 class="px-5 py-2 text-sm font-medium text-[#4b4b4b]">票種選擇</h2>
        <div class="mt-4 space-y-3">
            <?php if (empty($concert['zones'])): ?>
                <p class="text-sm text-slate-400">尚未開放任何票區。</p>
            <?php else: ?>
                <?php foreach ($concert['zones'] as $z): ?>
                    <?php $remaining = (int) $z['remaining']; ?>
                    <div class="rounded-lg border border-slate-200 bg-slate-100 px-4 py-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-900"><?= e($z['name']) ?></span>
                            <?php if ($remaining > 0): ?>
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">剩 <?= $remaining ?></span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">售完</span>
                            <?php endif; ?>
                        </div>
                        <p class="mt-1 text-sm text-slate-600">NT$ <?= number_format((float) $z['price']) ?></p>
                        <div class="mt-3">
                            <?php if ($remaining <= 0): ?>
                                <button disabled class="w-full rounded-md bg-slate-100 px-3 py-1.5 text-sm font-medium text-slate-400 cursor-not-allowed">已售完</button>
                            <?php elseif ($u === null): ?>
                                <a href="/login" class="block w-full rounded-md border border-slate-300 px-3 py-1.5 text-center text-sm font-medium text-slate-700 transition hover:bg-slate-50">登入後訂票</a>
                            <?php else: ?>
                                <form method="post" action="/orders" class="flex items-center gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="concert_id" value="<?= (int) $concert['id'] ?>">
                                    <input type="hidden" name="zone_id" value="<?= (int) $z['id'] ?>">
                                    <input type="number" name="qty" value="1" min="1" max="<?= $remaining ?>"
                                           class="w-16 rounded-md border border-slate-300 px-2 py-1.5 text-sm text-slate-900 focus:border-slate-500 focus:outline-none" aria-label="張數">
                                    <button type="submit" class="flex-1 rounded-md bg-[#ea6d4a] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-[#d45f3c]">訂票</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</div>

<script>
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-panel').forEach(function(p) { p.classList.add('hidden'); });
        document.querySelectorAll('.tab-btn').forEach(function(b) {
            b.classList.remove('border-slate-900', 'text-slate-900');
            b.classList.add('border-transparent', 'text-slate-500');
        });
        document.getElementById('tab-' + btn.dataset.tab).classList.remove('hidden');
        btn.classList.remove('border-transparent', 'text-slate-500');
        btn.classList.add('border-slate-900', 'text-slate-900');
    });
});
</script>
