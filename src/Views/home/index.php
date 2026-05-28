<?php /** @var string $dbStatus */ /** @var int $concertCount */ ?>
<div class="px-4 py-5 text-center">
    <h1 class="display-5 fw-bold">Hello, ticket platform 🎟️</h1>
    <p class="lead text-muted">第零步骨架已就位，三位組員可以開工了。</p>

    <div class="card mx-auto mt-4" style="max-width: 480px;">
        <div class="card-body text-start">
            <h5 class="card-title">系統檢查</h5>
            <ul class="mb-0">
                <li>DB 連線：
                    <?php if ($dbStatus === 'connected'): ?>
                        <span class="badge bg-success">connected</span>
                    <?php else: ?>
                        <span class="badge bg-danger"><?= e($dbStatus) ?></span>
                    <?php endif; ?>
                </li>
                <li>已種子演唱會：<strong><?= (int)$concertCount ?></strong> 場</li>
            </ul>
        </div>
    </div>

    <p class="mt-4 text-muted small">
        下一步：在 <code>public/index.php</code> 加入各自負責的路由，並到 <code>src/Controllers/</code> 與 <code>src/Views/</code> 開發。
    </p>
</div>
