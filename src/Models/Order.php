<?php

declare(strict_types=1);

// 訂單資料存取：純靜態查詢方法。庫存相關寫入須包在 db() transaction 內（見 OrderController::create）。
class Order
{
    // 建立 pending 訂單，10 分鐘後過期。回傳新訂單 id。
    public static function create(int $userId, int $concertId, float $total): int
    {
        query(
            "INSERT INTO orders (user_id, concert_id, status, total_amount, expires_at, created_at)
             VALUES (?, ?, 'pending', ?, NOW() + INTERVAL 10 MINUTE, NOW())",
            [$userId, $concertId, $total]
        );
        return (int) db()->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $row = query('SELECT * FROM orders WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    // 訂單詳情頁用：附演唱會資訊、items、以及 DB 端算的剩餘秒數（避免 PHP/MySQL 時區差）。
    public static function findWithDetails(int $id): ?array
    {
        $order = query(
            'SELECT o.*, c.title AS concert_title, c.venue, c.performed_at,
                    TIMESTAMPDIFF(SECOND, NOW(), o.expires_at) AS seconds_left
               FROM orders o
               JOIN concerts c ON c.id = o.concert_id
              WHERE o.id = ?',
            [$id]
        )->fetch();
        if (!$order) {
            return null;
        }
        $order['items'] = OrderItem::findByOrder($id);
        return $order;
    }

    // 我的訂單：依狀態取登入者的訂單（pending / paid / expired）。
    public static function findMineByStatus(int $userId, string $status): array
    {
        return query(
            'SELECT o.id, o.concert_id, o.status, o.total_amount, o.expires_at,
                    o.paid_at, o.created_at, c.title AS concert_title, c.performed_at
               FROM orders o
               JOIN concerts c ON c.id = o.concert_id
              WHERE o.user_id = ? AND o.status = ?
              ORDER BY o.created_at DESC',
            [$userId, $status]
        )->fetchAll();
    }

    // 付款：只在仍是 pending 且未過期時生效（DB 端 NOW() 為權威），回傳是否成功。
    public static function markPaid(int $id): bool
    {
        return query(
            "UPDATE orders
                SET status = 'paid', paid_at = NOW()
              WHERE id = ? AND status = 'pending' AND expires_at > NOW()",
            [$id]
        )->rowCount() > 0;
    }

    // lazy expiration：把某場已過期的 pending 單標記為 expired，回傳這些 order id（庫存回扣由呼叫端處理）。
    public static function expirePending(int $concertId): array
    {
        $ids = query(
            "SELECT id FROM orders
              WHERE concert_id = ? AND status = 'pending' AND expires_at < NOW()",
            [$concertId]
        )->fetchAll(PDO::FETCH_COLUMN);

        if (!$ids) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        query("UPDATE orders SET status = 'expired' WHERE id IN ($placeholders)", $ids);

        return array_map('intval', $ids);
    }
}
