<?php
/**
 * 共用演唱會表單欄位。使用前需先設定：
 * @var string $action       表單 POST 目標
 * @var string $submitLabel  送出按鈕文字
 * 由 render() 帶入：$errors, $statuses；edit 另帶 $concert；驗證失敗帶 $old
 */
$concert  = $concert  ?? [];
$old      = $old      ?? [];
$errors   = $errors   ?? [];
$statuses = $statuses ?? ['draft', 'on_sale', 'closed'];

// 值優先序：舊輸入 > 既有資料 > 預設
$val = static fn(string $k, string $d = ''): string => (string) ($old[$k] ?? $concert[$k] ?? $d);
// datetime 欄位轉成 datetime-local input 格式
$dt  = static fn(string $k): string => to_datetime_local($old[$k] ?? $concert[$k] ?? null);

$statusLabels = ['draft' => '草稿', 'on_sale' => '販售中', 'closed' => '已結束'];
?>

<form method="post" action="<?= e($action) ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">標題 <span class="text-red-500">*</span></label>
        <input type="text" name="title" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['title']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
               value="<?= e($val('title')) ?>" maxlength="120">
        <?php if (isset($errors['title'])): ?><div class="mt-1 text-sm text-red-600"><?= e($errors['title']) ?></div><?php endif; ?>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">場館 <span class="text-red-500">*</span></label>
        <input type="text" name="venue" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['venue']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
               value="<?= e($val('venue')) ?>" maxlength="120">
        <?php if (isset($errors['venue'])): ?><div class="mt-1 text-sm text-red-600"><?= e($errors['venue']) ?></div><?php endif; ?>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">演出時間 <span class="text-red-500">*</span></label>
        <input type="datetime-local" name="performed_at" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['performed_at']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
               value="<?= e($dt('performed_at')) ?>">
        <?php if (isset($errors['performed_at'])): ?><div class="mt-1 text-sm text-red-600"><?= e($errors['performed_at']) ?></div><?php endif; ?>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">開賣時間 <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="sales_start_at" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['sales_start_at']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                   value="<?= e($dt('sales_start_at')) ?>">
            <?php if (isset($errors['sales_start_at'])): ?><div class="mt-1 text-sm text-red-600"><?= e($errors['sales_start_at']) ?></div><?php endif; ?>
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">結束販售 <span class="text-red-500">*</span></label>
            <input type="datetime-local" name="sales_end_at" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['sales_end_at']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                   value="<?= e($dt('sales_end_at')) ?>">
            <?php if (isset($errors['sales_end_at'])): ?><div class="mt-1 text-sm text-red-600"><?= e($errors['sales_end_at']) ?></div><?php endif; ?>
        </div>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">海報網址</label>
        <input type="url" name="poster_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" value="<?= e($val('poster_url')) ?>" placeholder="https://...">
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">簡介</label>
        <textarea name="description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none" rows="3"><?= e($val('description')) ?></textarea>
    </div>

    <div>
        <label class="mb-1 block text-sm font-medium text-slate-700">狀態</label>
        <select name="status" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['status']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>">
            <?php $current = $val('status', 'draft'); ?>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s) ?>" <?= $current === $s ? 'selected' : '' ?>>
                    <?= e($statusLabels[$s] ?? $s) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['status'])): ?><div class="mt-1 text-sm text-red-600"><?= e($errors['status']) ?></div><?php endif; ?>
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700"><?= e($submitLabel) ?></button>
        <a href="/admin/concerts" class="text-sm font-medium text-slate-600 hover:text-slate-900">取消</a>
    </div>
</form>
