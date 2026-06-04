<?php /** @var array $concerts */ ?>

<?php
    $statusLabels = ['draft' => '草稿', 'on_sale' => '販售中', 'closed' => '已結束'];
    $statusBadges = ['draft' => 'secondary', 'on_sale' => 'success', 'closed' => 'dark'];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">後台 · 演唱會管理</h1>
    <a href="/admin/concerts/new" class="btn btn-primary">＋ 新增演唱會</a>
</div>

<?php if (empty($concerts)): ?>
    <div class="alert alert-secondary">目前沒有任何演唱會。</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>標題</th>
                    <th>場館</th>
                    <th>演出時間</th>
                    <th>狀態</th>
                    <th class="text-end">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($concerts as $c): ?>
                    <tr>
                        <td><?= (int) $c['id'] ?></td>
                        <td><?= e($c['title']) ?></td>
                        <td><?= e($c['venue']) ?></td>
                        <td><?= e(date('Y-m-d H:i', strtotime($c['performed_at']))) ?></td>
                        <td>
                            <span class="badge bg-<?= e($statusBadges[$c['status']] ?? 'secondary') ?>">
                                <?= e($statusLabels[$c['status']] ?? $c['status']) ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="/admin/concerts/<?= (int) $c['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">編輯</a>
                            <form method="post" action="/admin/concerts/<?= (int) $c['id'] ?>/delete" class="d-inline"
                                    onsubmit="return confirm(<?= e(json_encode('確定刪除「' . $c['title'] . '」？此操作無法復原。', JSON_UNESCAPED_UNICODE)) ?>);">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger">刪除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
