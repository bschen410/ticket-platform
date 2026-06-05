<?php

declare(strict_types=1);

class OrderController
{
    // POST /orders — 核心搶票 transaction（FOR UPDATE 防超賣 + lazy expiration 回庫）。
    public function create(): void
    {
        require_login();
        csrf_check();

        $user      = current_user();
        $concertId = (int) ($_POST['concert_id'] ?? 0);
        $zoneId    = (int) ($_POST['zone_id'] ?? 0);
        $qty       = (int) ($_POST['qty'] ?? 0);

        if ($concertId <= 0 || $zoneId <= 0 || $qty < 1) {
            flash('error', '訂票資料不正確');
            header('Location: /concerts/' . max($concertId, 0));
            return;
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            // 1. 鎖住票區列
            $zone = Zone::findForUpdate($zoneId, $concertId);
            if ($zone === null) {
                $pdo->rollBack();
                flash('error', '找不到票區');
                header('Location: /concerts/' . $concertId);
                return;
            }

            // 2. lazy expiration：把同場過期 pending 單回庫
            foreach (Order::expirePending($concertId) as $expiredId) {
                foreach (OrderItem::findByOrder($expiredId) as $item) {
                    Zone::decrementSold((int) $item['zone_id'], (int) $item['quantity']);
                }
            }

            // 3. 回庫後重新讀剩餘
            $zone      = Zone::findForUpdate($zoneId, $concertId);
            $sold      = (int) $zone['sold_seats'];
            $remaining = (int) $zone['total_seats'] - $sold;
            if ($remaining < $qty) {
                $pdo->rollBack();
                flash('error', '此票區已售完或剩餘張數不足');
                header('Location: /concerts/' . $concertId);
                return;
            }

            // 4. 分配座位（第一版流水號；基準為回庫後、增量前的 sold）
            $seatLabels = [];
            for ($i = 1; $i <= $qty; $i++) {
                $seatLabels[] = $zone['name'] . ' 第' . ($sold + $i) . '張';
            }

            // 5. 扣庫存 → 建訂單 → 建項目
            $unitPrice = (float) $zone['price'];
            Zone::incrementSold($zoneId, $qty);
            $orderId = Order::create((int) $user['id'], $concertId, $unitPrice * $qty);
            OrderItem::create($orderId, $zoneId, $qty, $unitPrice, $seatLabels);

            $pdo->commit();
            header('Location: /orders/' . $orderId);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            flash('error', '訂票失敗，請稍後再試');
            header('Location: /concerts/' . $concertId);
        }
    }

    // GET /orders/{id} — 訂單詳情（倒數 / 付款 / 電子票）。
    public function show(int $id): void
    {
        require_login();

        $order = Order::findWithDetails($id);
        if ($order === null || (int) $order['user_id'] !== (int) current_user()['id']) {
            abort_404();
        }

        render('orders/show', ['order' => $order]);
    }

    // POST /orders/{id}/pay — 模擬付款。
    public function pay(int $id): void
    {
        require_login();
        csrf_check();

        $order = Order::find($id);
        if ($order === null || (int) $order['user_id'] !== (int) current_user()['id']) {
            abort_404();
        }

        if (Order::markPaid($id)) {
            flash('success', '付款成功，電子票已產生');
        } else {
            flash('error', '訂單已過期或無法付款');
        }
        header('Location: /orders/' . $id);
    }

    // GET /my/orders — 我的訂單，分 pending / paid / expired。
    public function mine(): void
    {
        require_login();

        $uid = (int) current_user()['id'];
        render('orders/mine', [
            'pending' => Order::findMineByStatus($uid, 'pending'),
            'paid'    => Order::findMineByStatus($uid, 'paid'),
            'expired' => Order::findMineByStatus($uid, 'expired'),
        ]);
    }
}
