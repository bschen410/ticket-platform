<?php /** @var array $concerts */ ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0">🎤 熱賣中演唱會</h1>
</div>

<?php if (empty($concerts)): ?>
    <div class="alert alert-secondary">目前沒有販售中的演唱會，敬請期待。</div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($concerts as $c): ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($c['poster_url'])): ?>
                        <img src="<?= e($c['poster_url']) ?>" class="card-img-top" alt="<?= e($c['title']) ?>" style="height:180px;object-fit:cover;">
                    <?php else: ?>
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-dark text-white" style="height:180px;font-size:2.5rem;">🎟️</div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= e($c['title']) ?></h5>
                        <p class="card-text text-muted small mb-1">📍 <?= e($c['venue']) ?></p>
                        <p class="card-text text-muted small mb-3">🗓️ <?= e(date('Y-m-d H:i', strtotime($c['performed_at']))) ?></p>
                        <a href="/concerts/<?= (int) $c['id'] ?>" class="btn btn-primary mt-auto">查看詳情</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
