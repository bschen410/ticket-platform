<?php /** @var array $concert */ ?>
<?php $u = current_user(); ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">演唱會</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($concert['title']) ?></li>
    </ol>
</nav>

<div class="row g-4">
    <div class="col-md-5">
        <?php if (!empty($concert['poster_url'])): ?>
            <img src="<?= e($concert['poster_url']) ?>" class="img-fluid rounded shadow-sm" alt="<?= e($concert['title']) ?>">
        <?php else: ?>
            <div class="d-flex align-items-center justify-content-center bg-dark text-white rounded" style="height:280px;font-size:4rem;">🎟️</div>
        <?php endif; ?>
    </div>
    <div class="col-md-7">
        <h1 class="h3"><?= e($concert['title']) ?></h1>
        <p class="text-muted mb-1">📍 <?= e($concert['venue']) ?></p>
        <p class="text-muted mb-3">🗓️ <?= e(date('Y-m-d H:i', strtotime($concert['performed_at']))) ?></p>
        <?php if (!empty($concert['description'])): ?>
            <p><?= nl2br(e($concert['description'])) ?></p>
        <?php endif; ?>
    </div>
</div>

<h2 class="h5 mt-5 mb-3">票區與票價</h2>

<?php if (empty($concert['zones'])): ?>
    <div class="alert alert-secondary">尚未開放任何票區。</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th>票區</th>
                    <th class="text-end">票價</th>
                    <th class="text-end">剩餘張數</th>
                    <th class="text-end" style="width:14rem;">訂票</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($concert['zones'] as $z): ?>
                    <?php $remaining = (int) $z['remaining']; ?>
                    <tr>
                        <td><?= e($z['name']) ?></td>
                        <td class="text-end">NT$ <?= number_format((float) $z['price']) ?></td>
                        <td class="text-end">
                            <?php if ($remaining > 0): ?>
                                <span class="badge bg-success"><?= $remaining ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">售完</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <?php if ($remaining <= 0): ?>
                                <span class="text-muted">—</span>
                            <?php elseif ($u === null): ?>
                                <a href="/login" class="btn btn-sm btn-outline-primary">登入後訂票</a>
                            <?php else: ?>
                                <form method="post" action="/orders" class="d-flex gap-2 justify-content-end">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="concert_id" value="<?= (int) $concert['id'] ?>">
                                    <input type="hidden" name="zone_id" value="<?= (int) $z['id'] ?>">
                                    <input type="number" name="qty" value="1" min="1" max="<?= $remaining ?>"
                                           class="form-control form-control-sm" style="width:5rem;" aria-label="張數">
                                    <button type="submit" class="btn btn-sm btn-primary">訂票</button>
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
