<?php

declare(strict_types=1);

namespace Admin;

// 區域 CRUD：不另開頁面，操作後一律重導回所屬演唱會的編輯頁。
class ZoneController
{
    public function store(int $concertId): void
    {
        require_admin();
        csrf_check();

        if (\Concert::find($concertId) === null) {
            abort_404();
        }

        $data   = $this->normalize($_POST);
        $errors = $this->validate($data);

        if ($errors) {
            flash('error', '新增區域失敗：' . implode('、', $errors));
        } else {
            \Zone::create($concertId, $data);
            flash('success', '已新增區域');
        }

        $this->backToConcert($concertId);
    }

    public function update(int $id): void
    {
        require_admin();
        csrf_check();

        $zone = \Zone::find($id);
        if ($zone === null) {
            abort_404();
        }

        $data   = $this->normalize($_POST);
        $errors = $this->validate($data, (int) $zone['sold_seats']);

        if ($errors) {
            flash('error', '更新區域失敗：' . implode('、', $errors));
        } else {
            \Zone::update($id, $data);
            flash('success', '已更新區域');
        }

        $this->backToConcert((int) $zone['concert_id']);
    }

    public function destroy(int $id): void
    {
        require_admin();
        csrf_check();

        $zone = \Zone::find($id);
        if ($zone === null) {
            abort_404();
        }

        try {
            \Zone::delete($id);
            flash('success', '已刪除區域');
        } catch (\PDOException $e) {
            flash('error', '此區域已有售票，無法刪除');
        }

        $this->backToConcert((int) $zone['concert_id']);
    }

    private function normalize(array $input): array
    {
        return [
            'name'        => trim($input['name'] ?? ''),
            'price'       => $input['price'] ?? '',
            'total_seats' => $input['total_seats'] ?? '',
        ];
    }

    // $soldSeats：更新時不允許把 total_seats 改到比已售還少
    private function validate(array $data, int $soldSeats = 0): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = '區域名稱不能為空';
        }

        if (!is_numeric($data['price']) || (float) $data['price'] < 0) {
            $errors['price'] = '票價需為非負數字';
        }

        if (!ctype_digit((string) $data['total_seats']) || (int) $data['total_seats'] <= 0) {
            $errors['total_seats'] = '座位數需為正整數';
        } elseif ((int) $data['total_seats'] < $soldSeats) {
            $errors['total_seats'] = "座位數不能少於已售出（{$soldSeats}）";
        }

        return $errors;
    }

    private function backToConcert(int $concertId): void
    {
        header('Location: /admin/concerts/' . $concertId . '/edit');
        exit;
    }
}
