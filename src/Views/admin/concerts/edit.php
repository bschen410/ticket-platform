<?php
/** @var array $concert */ /** @var array $zones */
/** @var array $errors */ /** @var array $old */ /** @var array $statuses */
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/concerts">演唱會管理</a></li>
        <li class="breadcrumb-item active" aria-current="page">編輯：<?= e($concert['title']) ?></li>
    </ol>
</nav>

<h1 class="h3 mb-4">編輯演唱會</h1>

<div class="card mb-4">
    <div class="card-body">
        <?php
            $action = '/admin/concerts/' . (int) $concert['id'];
            $submitLabel = '儲存變更';
            require __DIR__ . '/_form.php';
        ?>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="fw-bold">票區管理</span>
        <a href="/concerts/<?= (int) $concert['id'] ?>" class="btn btn-sm btn-outline-secondary" target="_blank">前台預覽</a>
    </div>
    <div class="card-body">
        <?php if (empty($zones)): ?>
            <p class="text-muted">尚無票區，請於下方新增。</p>
        <?php endif; ?>

        <?php foreach ($zones as $z): ?>
            <div class="d-flex align-items-end gap-2 mb-2 py-2 border-top flex-wrap">
                <form method="post" action="/admin/zones/<?= (int) $z['id'] ?>" class="d-flex align-items-end gap-2 flex-grow-1 flex-wrap mb-0">
                    <?= csrf_field() ?>
                    <div style="flex:2 1 160px;">
                        <label class="form-label small mb-0">區域名稱</label>
                        <input type="text" name="name" class="form-control form-control-sm" value="<?= e($z['name']) ?>">
                    </div>
                    <div style="flex:1 1 100px;">
                        <label class="form-label small mb-0">票價</label>
                        <input type="number" name="price" step="0.01" min="0" class="form-control form-control-sm" value="<?= e((string) (float) $z['price']) ?>">
                    </div>
                    <div style="flex:1 1 100px;">
                        <label class="form-label small mb-0">總座位</label>
                        <input type="number" name="total_seats" min="<?= (int) $z['sold_seats'] ?>" class="form-control form-control-sm" value="<?= (int) $z['total_seats'] ?>">
                    </div>
                    <div class="text-center" style="flex:0 0 50px;">
                        <label class="form-label small mb-0 d-block">已售</label>
                        <span class="badge bg-light text-dark"><?= (int) $z['sold_seats'] ?></span>
                    </div>
                    <button class="btn btn-sm btn-outline-primary">更新</button>
                </form>
                <form method="post" action="/admin/zones/<?= (int) $z['id'] ?>/delete" class="mb-0"
                      onsubmit="return confirm('確定刪除區域「<?= e($z['name']) ?>」？');">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger">刪除</button>
                </form>
            </div>
        <?php endforeach; ?>

        <!-- 新增區域 -->
        <form method="post" action="/admin/concerts/<?= (int) $concert['id'] ?>/zones" class="row g-2 align-items-center mt-3 pt-3 border-top">
            <?= csrf_field() ?>
            <div class="col-md-4"><input type="text" name="name" class="form-control form-control-sm" placeholder="新區域名稱"></div>
            <div class="col-md-2"><input type="number" name="price" step="0.01" min="0" class="form-control form-control-sm" placeholder="票價"></div>
            <div class="col-md-2"><input type="number" name="total_seats" min="1" class="form-control form-control-sm" placeholder="座位數"></div>
            <div class="col-md-1 text-center text-muted small">—</div>
            <div class="col-md-3 text-end">
                <button class="btn btn-sm btn-success">＋ 新增</button>
            </div>
        </form>
    </div>
</div>
