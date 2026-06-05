<?php /** @var array $order */ ?>
<?php
$statusMap = [
    'pending'   => ['warning', '待付款'],
    'paid'      => ['success', '已付款'],
    'expired'   => ['secondary', '已過期'],
    'cancelled' => ['secondary', '已取消'],
];
[$badge, $label] = $statusMap[$order['status']] ?? ['secondary', $order['status']];
$secondsLeft = max(0, (int) $order['seconds_left']);
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/my/orders">我的訂單</a></li>
        <li class="breadcrumb-item active" aria-current="page">訂單 #<?= (int) $order['id'] ?></li>
    </ol>
</nav>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h1 class="h4 mb-1"><?= e($order['concert_title']) ?></h1>
                <p class="text-muted mb-0">📍 <?= e($order['venue']) ?></p>
                <p class="text-muted">🗓️ <?= e(date('Y-m-d H:i', strtotime($order['performed_at']))) ?></p>
            </div>
            <span class="badge bg-<?= e($badge) ?> fs-6"><?= e($label) ?></span>
        </div>

        <?php if ($order['status'] === 'pending'): ?>
            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                <span id="countdown-note">請於倒數結束前完成付款，逾時訂單將自動取消並釋出座位。</span>
                <span class="fs-4 fw-bold" id="countdown" data-seconds="<?= $secondsLeft ?>">--:--</span>
            </div>
        <?php elseif ($order['status'] === 'paid'): ?>
            <div class="alert alert-success">✅ 已於 <?= e(date('Y-m-d H:i', strtotime($order['paid_at']))) ?> 完成付款，以下為您的電子票。</div>
        <?php elseif ($order['status'] === 'expired'): ?>
            <div class="alert alert-secondary">此訂單已過期，座位已釋出。請重新訂票。</div>
        <?php endif; ?>

        <h2 class="h6 mt-4">票券明細</h2>
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>票區</th>
                    <th class="text-end">單價</th>
                    <th class="text-end">張數</th>
                    <th>座位</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order['items'] as $item): ?>
                    <tr>
                        <td><?= e($item['zone_name']) ?></td>
                        <td class="text-end">NT$ <?= number_format((float) $item['unit_price']) ?></td>
                        <td class="text-end"><?= (int) $item['quantity'] ?></td>
                        <td>
                            <?php foreach ($item['seat_labels'] as $seat): ?>
                                <span class="badge bg-light text-dark border me-1 <?= $order['status'] === 'paid' ? '' : 'opacity-50' ?>"><?= e($seat) ?></span>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">總計</th>
                    <th>NT$ <?= number_format((float) $order['total_amount']) ?></th>
                </tr>
            </tfoot>
        </table>

        <?php if ($order['status'] === 'pending'): ?>
            <form method="post" action="/orders/<?= (int) $order['id'] ?>/pay" class="text-end">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success" id="pay-btn">確認付款</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script src="/assets/js/countdown.js"></script>
