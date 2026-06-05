<?php

declare(strict_types=1);

// 演唱會資料存取：純靜態查詢方法，內部使用全域 query() / db() helper。
class Concert
{
    // 首頁：販售中的演唱會，依演出時間排序
    public static function onSaleList(): array
    {
        return query(
            "SELECT id, title, venue, performed_at, poster_url, program_intro, price_info, notices
               FROM concerts
              WHERE status = 'on_sale'
              ORDER BY performed_at ASC"
        )->fetchAll();
    }

    // 詳細頁：單一演唱會 + 各區域（含剩餘張數）
    public static function findWithZones(int $id): ?array
    {
        $concert = self::find($id);
        if ($concert === null) {
            return null;
        }
        $concert['zones'] = Zone::findByConcert($id);
        return $concert;
    }

    // 後台列表：所有狀態
    public static function all(): array
    {
        return query(
            'SELECT id, title, venue, performed_at, status, created_at
               FROM concerts
              ORDER BY performed_at DESC'
        )->fetchAll();
    }

    // 單筆（編輯表單用）
    public static function find(int $id): ?array
    {
        $row = query('SELECT * FROM concerts WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    // 新增，回傳新 id
    public static function create(array $data): int
    {
        query(
            'INSERT INTO concerts
                (title, venue, performed_at, poster_url, program_intro, price_info, notices,
                 sales_start_at, sales_end_at, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['title'],
                $data['venue'],
                $data['performed_at'],
                $data['poster_url'] !== '' ? $data['poster_url'] : null,
                $data['program_intro'] !== '' ? $data['program_intro'] : null,
                $data['price_info'] !== '' ? $data['price_info'] : null,
                $data['notices'] !== '' ? $data['notices'] : null,
                $data['sales_start_at'],
                $data['sales_end_at'],
                $data['status'],
            ]
        );
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        query(
            'UPDATE concerts
                SET title = ?, venue = ?, performed_at = ?, poster_url = ?,
                    program_intro = ?, price_info = ?, notices = ?,
                    sales_start_at = ?, sales_end_at = ?, status = ?
              WHERE id = ?',
            [
                $data['title'],
                $data['venue'],
                $data['performed_at'],
                $data['poster_url'] !== '' ? $data['poster_url'] : null,
                $data['program_intro'] !== '' ? $data['program_intro'] : null,
                $data['price_info'] !== '' ? $data['price_info'] : null,
                $data['notices'] !== '' ? $data['notices'] : null,
                $data['sales_start_at'],
                $data['sales_end_at'],
                $data['status'],
                $id,
            ]
        );
    }

    // hard delete；zones 透過 FK CASCADE 連帶刪除。
    // 若該場已有 orders（FK 無 CASCADE）會丟 PDOException，由 controller 攔截。
    public static function delete(int $id): bool
    {
        return query('DELETE FROM concerts WHERE id = ?', [$id])->rowCount() > 0;
    }
}
