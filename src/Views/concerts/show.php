<?php /** @var array $concert */ ?>
<?php $u = current_user(); ?>

<nav class="mb-4 text-sm text-slate-500">
    <a href="/" class="hover:text-slate-900">演唱會</a>
    <span class="mx-2">/</span>
    <span class="text-slate-900"><?= e($concert['title']) ?></span>
</nav>

<div class="grid gap-6 lg:grid-cols-2">
    <div>
        <?php if (!empty($concert['poster_url'])): ?>
            <img src="<?= e($concert['poster_url']) ?>" class="h-[280px] w-full rounded-2xl object-cover shadow-sm" alt="<?= e($concert['title']) ?>">
        <?php else: ?>
            <div class="flex h-[280px] items-center justify-center rounded-2xl bg-slate-900 text-6xl text-white shadow-sm">🎟️</div>
        <?php endif; ?>
    </div>
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-slate-900"><?= e($concert['title']) ?></h1>
        <p class="mt-3 text-slate-500">📍 <?= e($concert['venue']) ?></p>
        <p class="text-slate-500">🗓️ <?= e(date('Y-m-d H:i', strtotime($concert['performed_at']))) ?></p>
        <?php if (!empty($concert['description'])): ?>
            <p class="mt-4 leading-7 text-slate-700"><?= nl2br(e($concert['description'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<h2 class="mt-10 mb-4 text-xl font-semibold text-slate-900">票區與票價</h2>

<?php if (empty($concert['zones'])): ?>
    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">尚未開放任何票區。</div>
<?php else: ?>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">票區</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">票價</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">剩餘張數</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">訂票</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                <?php foreach ($concert['zones'] as $z): ?>
                    <?php $remaining = (int) $z['remaining']; ?>
                    <tr>
                        <td class="px-4 py-4 text-sm font-medium text-slate-900"><?= e($z['name']) ?></td>
                        <td class="px-4 py-4 text-right text-sm text-slate-700">NT$ <?= number_format((float) $z['price']) ?></td>
                        <td class="px-4 py-4 text-right text-sm">
                            <?php if ($remaining > 0): ?>
                                <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700"><?= $remaining ?></span>
                            <?php else: ?>
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600">售完</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <?php if ($remaining <= 0): ?>
                                <span class="text-sm text-slate-400">—</span>
                            <?php elseif ($u === null): ?>
                                <a href="/login" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">登入後訂票</a>
                            <?php else: ?>
                                <form method="post" action="/orders" class="flex items-center justify-end gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="concert_id" value="<?= (int) $concert['id'] ?>">
                                    <input type="hidden" name="zone_id" value="<?= (int) $z['id'] ?>">
                                    <input type="number" name="qty" value="1" min="1" max="<?= $remaining ?>"
                                           class="w-20 rounded-md border border-slate-300 px-2 py-1 text-sm text-slate-900 focus:border-slate-500 focus:outline-none" aria-label="張數">
                                    <button type="submit" class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-slate-700">訂票</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php /* 訂票送到 POST /orders（OrderController::create，Step 3 C）：帶 concert_id + zone_id + qty + CSRF */ ?>
<?php endif; ?>
