<?php /** @var array $concerts */ ?>

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">🎤 熱賣中演唱會</h1>
</div>

<?php if (empty($concerts)): ?>
    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">目前沒有販售中的演唱會，敬請期待。</div>
<?php else: ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($concerts as $c): ?>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <?php if (!empty($c['poster_url'])): ?>
                    <img src="<?= e($c['poster_url']) ?>" class="h-48 w-full object-cover" alt="<?= e($c['title']) ?>">
                <?php else: ?>
                    <div class="flex h-48 items-center justify-center bg-slate-900 text-4xl text-white">🎟️</div>
                <?php endif; ?>
                <div class="flex h-full flex-col p-5">
                    <h2 class="text-lg font-semibold text-slate-900"><?= e($c['title']) ?></h2>
                    <p class="mt-2 text-sm text-slate-500">📍 <?= e($c['venue']) ?></p>
                    <p class="text-sm text-slate-500">🗓️ <?= e(date('Y-m-d H:i', strtotime($c['performed_at']))) ?></p>
                    <a href="/concerts/<?= (int) $c['id'] ?>" class="mt-4 inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">查看詳情</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
