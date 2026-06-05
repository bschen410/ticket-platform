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

<form method="post" action="<?= e($action) ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
        <label class="form-label">標題 <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
               value="<?= e($val('title')) ?>" maxlength="120">
        <?php if (isset($errors['title'])): ?><div class="invalid-feedback"><?= e($errors['title']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">場館 <span class="text-danger">*</span></label>
        <input type="text" name="venue" class="form-control <?= isset($errors['venue']) ? 'is-invalid' : '' ?>"
               value="<?= e($val('venue')) ?>" maxlength="120">
        <?php if (isset($errors['venue'])): ?><div class="invalid-feedback"><?= e($errors['venue']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">演出時間 <span class="text-danger">*</span></label>
        <input type="datetime-local" name="performed_at" class="form-control <?= isset($errors['performed_at']) ? 'is-invalid' : '' ?>"
               value="<?= e($dt('performed_at')) ?>">
        <?php if (isset($errors['performed_at'])): ?><div class="invalid-feedback"><?= e($errors['performed_at']) ?></div><?php endif; ?>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">開賣時間 <span class="text-danger">*</span></label>
            <input type="datetime-local" name="sales_start_at" class="form-control <?= isset($errors['sales_start_at']) ? 'is-invalid' : '' ?>"
                   value="<?= e($dt('sales_start_at')) ?>">
            <?php if (isset($errors['sales_start_at'])): ?><div class="invalid-feedback"><?= e($errors['sales_start_at']) ?></div><?php endif; ?>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">結束販售 <span class="text-danger">*</span></label>
            <input type="datetime-local" name="sales_end_at" class="form-control <?= isset($errors['sales_end_at']) ? 'is-invalid' : '' ?>"
                   value="<?= e($dt('sales_end_at')) ?>">
            <?php if (isset($errors['sales_end_at'])): ?><div class="invalid-feedback"><?= e($errors['sales_end_at']) ?></div><?php endif; ?>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label">海報網址</label>
        <input type="url" name="poster_url" class="form-control" value="<?= e($val('poster_url')) ?>" placeholder="https://...">
    </div>

    <div class="mb-3">
        <label class="form-label">簡介</label>
        <textarea name="description" class="form-control" rows="3"><?= e($val('description')) ?></textarea>
    </div>

    <div class="mb-4">
        <label class="form-label">狀態</label>
        <select name="status" class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>">
            <?php $current = $val('status', 'draft'); ?>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s) ?>" <?= $current === $s ? 'selected' : '' ?>>
                    <?= e($statusLabels[$s] ?? $s) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['status'])): ?><div class="invalid-feedback"><?= e($errors['status']) ?></div><?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
    <a href="/admin/concerts" class="btn btn-link">取消</a>
</form>
