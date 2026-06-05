<?php
/** @var array $concert */ /** @var array $zones */
/** @var array $errors */ /** @var array $old */ /** @var array $statuses */
?>

<nav class="mb-4 text-sm text-slate-500">
    <a href="/admin/concerts" class="hover:text-slate-900">演唱會管理</a>
    <span class="mx-2">/</span>
    <span class="text-slate-900">編輯：<?= e($concert['title']) ?></span>
</nav>

<h1 class="mb-4 text-2xl font-bold tracking-tight text-slate-900">編輯演唱會</h1>

<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <?php
            $action = '/admin/concerts/' . (int) $concert['id'];
            $submitLabel = '儲存變更';
            require __DIR__ . '/_form.php';
        ?>
</div>

<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
        <span class="font-semibold text-slate-900">票區管理</span>
        <a href="/concerts/<?= (int) $concert['id'] ?>" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50" target="_blank">前台預覽</a>
    </div>
    <div class="p-6">
        <?php if (empty($zones)): ?>
            <p class="text-sm text-slate-500">尚無票區，請於下方新增。</p>
        <?php endif; ?>

        <?php foreach ($zones as $z): ?>
            <div class="mb-4 border-t border-slate-200 pt-4">
                <form method="post" action="/admin/zones/<?= (int) $z['id'] ?>" class="flex flex-wrap items-end gap-3">
                    <?= csrf_field() ?>
                    <div class="min-w-[160px] flex-1">
                        <label class="mb-1 block text-sm font-medium text-slate-700">區域名稱</label>
                        <input type="text" name="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" value="<?= e($z['name']) ?>">
                    </div>
                    <div class="min-w-[120px] flex-1">
                        <label class="mb-1 block text-sm font-medium text-slate-700">票價</label>
                        <input type="number" name="price" step="0.01" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" value="<?= e((string) (float) $z['price']) ?>">
                    </div>
                    <div class="min-w-[120px] flex-1">
                        <label class="mb-1 block text-sm font-medium text-slate-700">總座位</label>
                        <input type="number" name="total_seats" min="<?= (int) $z['sold_seats'] ?>" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" value="<?= (int) $z['total_seats'] ?>">
                    </div>
                    <div class="text-center">
                        <label class="mb-1 block text-sm font-medium text-slate-700">已售</label>
                        <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700"><?= (int) $z['sold_seats'] ?></span>
                    </div>
                    <button class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">更新</button>
                </form>
                <form method="post" action="/admin/zones/<?= (int) $z['id'] ?>/delete" class="mt-3"
                        onsubmit="return confirm(<?= e(json_encode('確定刪除區域「' . $z['name'] . '」？', JSON_UNESCAPED_UNICODE)) ?>);">
                    <?= csrf_field() ?>
                    <button class="rounded-md border border-red-300 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50">刪除</button>
                </form>
            </div>
        <?php endforeach; ?>

        <!-- 新增區域 -->
        <form method="post" action="/admin/concerts/<?= (int) $concert['id'] ?>/zones" class="mt-6 border-t border-slate-200 pt-4">
            <?= csrf_field() ?>
            <div class="grid gap-3 md:grid-cols-4">
                <input type="text" name="name" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" placeholder="新區域名稱">
                <input type="number" name="price" step="0.01" min="0" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" placeholder="票價">
                <input type="number" name="total_seats" min="1" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" placeholder="座位數">
                <div class="flex items-center justify-end">
                    <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500">＋ 新增</button>
                </div>
            </div>
        </form>
    </div>
</div>
