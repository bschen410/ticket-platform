<?php

declare(strict_types=1);

// 區域（票種）資料存取：純靜態查詢方法。
class Zone
{
    // 某場演唱會的所有區域，每筆附上剩餘張數 remaining
    public static function findByConcert(int $concertId): array
    {
        return query(
            'SELECT id, concert_id, name, price, total_seats, sold_seats,
                    (total_seats - sold_seats) AS remaining
               FROM zones
              WHERE concert_id = ?
              ORDER BY price DESC',
            [$concertId]
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = query('SELECT * FROM zones WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(int $concertId, array $data): int
    {
        query(
            'INSERT INTO zones (concert_id, name, price, total_seats)
             VALUES (?, ?, ?, ?)',
            [$concertId, $data['name'], $data['price'], $data['total_seats']]
        );
        return (int) db()->lastInsertId();
    }

    // 只允許改 name / price / total_seats；sold_seats 由訂票流程維護（Step 3）。
    public static function update(int $id, array $data): void
    {
        query(
            'UPDATE zones SET name = ?, price = ?, total_seats = ? WHERE id = ?',
            [$data['name'], $data['price'], $data['total_seats'], $id]
        );
    }

    // 已被 order_items 參照（已售票）時會丟 PDOException，由 controller 攔截。
    public static function delete(int $id): bool
    {
        return query('DELETE FROM zones WHERE id = ?', [$id])->rowCount() > 0;
    }

    // 訂票 transaction 開頭鎖住全場所有票區（ORDER BY id 固定順序），消除並行 transaction 的 deadlock 風險。
    public static function lockByConcert(int $concertId): void
    {
        query(
            'SELECT id FROM zones WHERE concert_id = ? ORDER BY id FOR UPDATE',
            [$concertId]
        )->fetchAll();
    }

    // 訂票 transaction 內鎖列：必須在 db()->beginTransaction() 之後呼叫，否則 FOR UPDATE 無效。
    public static function findForUpdate(int $zoneId, int $concertId): ?array
    {
        $row = query(
            'SELECT id, name, price, total_seats, sold_seats
               FROM zones
              WHERE id = ? AND concert_id = ?
              FOR UPDATE',
            [$zoneId, $concertId]
        )->fetch();
        return $row ?: null;
    }

    // 售出 qty 張（佔位），回傳是否有更新到列。
    public static function incrementSold(int $zoneId, int $qty): bool
    {
        return query(
            'UPDATE zones SET sold_seats = sold_seats + ? WHERE id = ?',
            [$qty, $zoneId]
        )->rowCount() > 0;
    }

    // 回庫 qty 張（過期單回收），GREATEST 保險避免扣成負數。
    public static function decrementSold(int $zoneId, int $qty): bool
    {
        return query(
            'UPDATE zones SET sold_seats = GREATEST(sold_seats - ?, 0) WHERE id = ?',
            [$qty, $zoneId]
        )->rowCount() > 0;
    }
}
