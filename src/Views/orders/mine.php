<?php /** @var array $pending */ /** @var array $paid */ /** @var array $expired */ ?>
<?php
$tabs = [
    'pending' => ['待付款', $pending, 'warning'],
    'paid'    => ['已付款', $paid,    'success'],
    'expired' => ['已過期', $expired, 'secondary'],
];
?>

<h1 class="h4 mb-3">我的訂單</h1>

<ul class="nav nav-tabs" role="tablist">
    <?php $first = true; foreach ($tabs as $key => [$label, $list, $color]): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?= $first ? 'active' : '' ?>" id="tab-<?= $key ?>" data-bs-toggle="tab"
                    data-bs-target="#pane-<?= $key ?>" type="button" role="tab">
                <?= e($label) ?> <span class="badge bg-<?= e($color) ?>"><?= count($list) ?></span>
            </button>
        </li>
    <?php $first = false; endforeach; ?>
</ul>

<div class="tab-content border border-top-0 rounded-bottom p-3 bg-white">
    <?php $first = true; foreach ($tabs as $key => [$label, $list, $color]): ?>
        <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="pane-<?= $key ?>" role="tabpanel">
            <?php if (empty($list)): ?>
                <p class="text-muted mb-0 py-3 text-center">目前沒有<?= e($label) ?>的訂單。</p>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($list as $o): ?>
                        <a href="/orders/<?= (int) $o['id'] ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <strong><?= e($o['concert_title']) ?></strong>
                                <small class="text-muted d-block">
                                    訂單 #<?= (int) $o['id'] ?> · 建立於 <?= e(date('Y-m-d H:i', strtotime($o['created_at']))) ?>
                                </small>
                            </span>
                            <span class="text-end">
                                <span class="d-block">NT$ <?= number_format((float) $o['total_amount']) ?></span>
                                <span class="badge bg-<?= e($color) ?>"><?= e($label) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php $first = false; endforeach; ?>
</div>
