<?php

declare(strict_types=1);

// 訂單項目資料存取：一筆訂單可含多區票券。seat_labels 以 JSON 儲存座位編號陣列。
class OrderItem
{
    public static function create(int $orderId, int $zoneId, int $qty, float $unitPrice, array $seatLabels): int
    {
        query(
            'INSERT INTO order_items (order_id, zone_id, quantity, unit_price, seat_labels)
             VALUES (?, ?, ?, ?, ?)',
            [$orderId, $zoneId, $qty, $unitPrice, json_encode($seatLabels, JSON_UNESCAPED_UNICODE)]
        );
        return (int) db()->lastInsertId();
    }

    // 某訂單的所有項目，附票區名稱；seat_labels 解碼成陣列。
    public static function findByOrder(int $orderId): array
    {
        $rows = query(
            'SELECT oi.id, oi.order_id, oi.zone_id, oi.quantity, oi.unit_price,
                    oi.seat_labels, z.name AS zone_name
               FROM order_items oi
               JOIN zones z ON z.id = oi.zone_id
              WHERE oi.order_id = ?',
            [$orderId]
        )->fetchAll();

        foreach ($rows as &$row) {
            $row['seat_labels'] = $row['seat_labels'] !== null
                ? (json_decode($row['seat_labels'], true) ?: [])
                : [];
        }
        return $rows;
    }
}
