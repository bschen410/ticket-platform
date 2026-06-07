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

        if ($concertId <= 0 || $zoneId <= 0 || $qty < 1 || $qty > 10) {
            flash('error', '訂票資料不正確（每次最多 10 張）');
            header('Location: /concerts/' . max($concertId, 0));
            return;
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            // 1. 以固定 id 升序鎖住全場所有票區，確保並行 transaction 鎖序一致，消除 deadlock 風險。
            Zone::lockByConcert($concertId);

            // 2. 驗證目標票區存在（鎖已由上一步取得，此處 FOR UPDATE 為複查）
            $zone = Zone::findForUpdate($zoneId, $concertId);
            if ($zone === null) {
                $pdo->rollBack();
                flash('error', '找不到票區');
                header('Location: /concerts/' . $concertId);
                return;
            }

            // 3. lazy expiration：把同場過期 pending 單回庫（全場 zone 已鎖，可安全回扣庫存）
            self::expireAndRestock($concertId);

            // 4. 回庫後重新讀剩餘
            $zone      = Zone::findForUpdate($zoneId, $concertId);
            $sold      = (int) $zone['sold_seats'];
            $remaining = (int) $zone['total_seats'] - $sold;
            if ($remaining < $qty) {
                $pdo->rollBack();
                flash('error', '此票區已售完或剩餘張數不足');
                header('Location: /concerts/' . $concertId);
                return;
            }

            // 5. 分配座位（第一版流水號；基準為回庫後、增量前的 sold）
            $seatLabels = [];
            for ($i = 1; $i <= $qty; $i++) {
                $seatLabels[] = $zone['name'] . ' 第' . ($sold + $i) . '張';
            }

            // 6. 扣庫存 → 建訂單 → 建項目
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

        // 開啟逾時的待付款單時，順手清理該場過期單並回庫，再重讀以反映 expired 狀態。
        if ($order['status'] === 'pending' && (int) $order['seconds_left'] <= 0) {
            self::expireConcert((int) $order['concert_id']);
            $order = Order::findWithDetails($id);
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

    // GET /orders/{id}/ticket — 電子票券頁（僅限已付款訂單）。
    public function ticket(int $id): void
    {
        require_login();

        $order = Order::findWithDetails($id);
        if ($order === null || (int) $order['user_id'] !== (int) current_user()['id']) {
            abort_404();
        }

        if ($order['status'] !== 'paid') {
            header('Location: /orders/' . $id);
            return;
        }

        render('orders/ticket', ['order' => $order]);
    }

    // GET /my/orders — 我的訂單，分 pending / paid / expired。
    public function mine(): void
    {
        require_login();

        $uid = (int) current_user()['id'];

        // 進頁前先清理本人有逾時待付款的場次，重新列表才會把逾時單歸到「已過期」並回庫。
        $concertIds = query(
            "SELECT DISTINCT concert_id FROM orders
              WHERE user_id = ? AND status = 'pending' AND expires_at < NOW()",
            [$uid]
        )->fetchAll(PDO::FETCH_COLUMN);
        foreach ($concertIds as $concertId) {
            self::expireConcert((int) $concertId);
        }

        render('orders/mine', [
            'pending' => Order::findMineByStatus($uid, 'pending'),
            'paid'    => Order::findMineByStatus($uid, 'paid'),
        ]);
    }

    // 某場過期 pending 單標記 expired 並回庫，自帶 transaction（供檢視類動作呼叫）。
    private static function expireConcert(int $concertId): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            self::expireAndRestock($concertId);
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('expireConcert failed for concert ' . $concertId . ': ' . $e->getMessage());
        }
    }

    // 過期 pending 單標記 expired，並把佔用的庫存回扣。呼叫端須已開啟 transaction。
    private static function expireAndRestock(int $concertId): void
    {
        foreach (Order::expirePending($concertId) as $expiredId) {
            foreach (OrderItem::findByOrder($expiredId) as $item) {
                Zone::decrementSold((int) $item['zone_id'], (int) $item['quantity']);
            }
        }
    }
}
