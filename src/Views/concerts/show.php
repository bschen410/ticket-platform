<?php /** @var array $concert */ ?>

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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($concert['zones'] as $z): ?>
                    <tr>
                        <td><?= e($z['name']) ?></td>
                        <td class="text-end">NT$ <?= number_format((float) $z['price']) ?></td>
                        <td class="text-end">
                            <?php if ((int) $z['remaining'] > 0): ?>
                                <span class="badge bg-success"><?= (int) $z['remaining'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary">售完</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php /* Step 3（C）：在此加入訂票表單 <form method="post" action="/orders">（選 zone + 張數 + CSRF） */ ?>
    <p class="text-muted small">訂票功能將於下一階段開放。</p>
<?php endif; ?>
