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
}
