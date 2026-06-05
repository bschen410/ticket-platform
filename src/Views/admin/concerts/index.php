<?php /** @var array $concerts */ ?>

<?php
    $statusLabels = ['draft' => '草稿', 'on_sale' => '販售中', 'closed' => '已結束'];
    $statusBadges = ['draft' => 'bg-slate-100 text-slate-700', 'on_sale' => 'bg-emerald-100 text-emerald-700', 'closed' => 'bg-slate-200 text-slate-700'];
?>

<div class="mb-6 flex items-center justify-between gap-4">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">後台 · 演唱會管理</h1>
    <a href="/admin/concerts/new" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">＋ 新增演唱會</a>
</div>

<?php if (empty($concerts)): ?>
    <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 shadow-sm">目前沒有任何演唱會。</div>
<?php else: ?>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">#</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">標題</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">場館</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">演出時間</th>
                    <th class="px-4 py-3 text-left text-sm font-semibold text-slate-700">狀態</th>
                    <th class="px-4 py-3 text-right text-sm font-semibold text-slate-700">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                <?php foreach ($concerts as $c): ?>
                    <tr>
                        <td class="px-4 py-4 text-sm text-slate-500"><?= (int) $c['id'] ?></td>
                        <td class="px-4 py-4 text-sm font-medium text-slate-900"><?= e($c['title']) ?></td>
                        <td class="px-4 py-4 text-sm text-slate-700"><?= e($c['venue']) ?></td>
                        <td class="px-4 py-4 text-sm text-slate-700"><?= e(date('Y-m-d H:i', strtotime($c['performed_at']))) ?></td>
                        <td class="px-4 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold <?= e($statusBadges[$c['status']] ?? 'bg-slate-100 text-slate-700') ?>">
                                <?= e($statusLabels[$c['status']] ?? $c['status']) ?>
                            </span>
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="/admin/concerts/<?= (int) $c['id'] ?>/edit" class="inline-flex items-center rounded-md border border-slate-300 px-3 py-1.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50">編輯</a>
                            <form method="post" action="/admin/concerts/<?= (int) $c['id'] ?>/delete" class="inline"
                                    onsubmit="return confirm(<?= e(json_encode('確定刪除「' . $c['title'] . '」？此操作無法復原。', JSON_UNESCAPED_UNICODE)) ?>);">
                                <?= csrf_field() ?>
                                <button class="ml-2 inline-flex items-center rounded-md border border-red-300 px-3 py-1.5 text-sm font-medium text-red-700 transition hover:bg-red-50">刪除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
