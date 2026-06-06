<?php

declare(strict_types=1);

namespace Admin;

class ConcertController
{
    private const STATUSES = ['draft', 'on_sale', 'closed'];

    public function index(): void
    {
        require_admin();
        render('admin/concerts/index', [
            'concerts' => \Concert::all(),
        ]);
    }

    public function create(): void
    {
        require_admin();
        render('admin/concerts/create', [
            'errors'   => pull_form_errors(),
            'old'      => pull_form_old(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(): void
    {
        require_admin();
        csrf_check();

        $data   = $this->normalize($_POST);
        $errors = $this->validate($data);

        if ($errors) {
            stash_form_errors($errors, $data);
            header('Location: /admin/concerts/new');
            exit;
        }

        $id = \Concert::create($data);
        flash('success', '已新增演唱會');
        header('Location: /admin/concerts/' . $id . '/edit');
        exit;
    }

    public function edit(int $id): void
    {
        require_admin();

        $concert = \Concert::find($id);
        if ($concert === null) {
            abort_404();
        }

        render('admin/concerts/edit', [
            'concert'  => $concert,
            'zones'    => \Zone::findByConcert($id),
            'errors'   => pull_form_errors(),
            'old'      => pull_form_old(),
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(int $id): void
    {
        require_admin();
        csrf_check();

        if (\Concert::find($id) === null) {
            abort_404();
        }

        $data   = $this->normalize($_POST);
        $errors = $this->validate($data);

        if ($errors) {
            stash_form_errors($errors, $data);
            header('Location: /admin/concerts/' . $id . '/edit');
            exit;
        }

        \Concert::update($id, $data);
        flash('success', '已更新演唱會');
        header('Location: /admin/concerts/' . $id . '/edit');
        exit;
    }

    public function destroy(int $id): void
    {
        require_admin();
        csrf_check();

        try {
            \Concert::delete($id);
            flash('success', '已刪除演唱會');
        } catch (\PDOException $e) {
            flash('error', '此演唱會已有訂單，無法刪除');
        }

        header('Location: /admin/concerts');
        exit;
    }

    // 把表單輸入整理成可入庫的格式（datetime-local → DATETIME）
    private function normalize(array $input): array
    {
        return [
            'title'          => trim($input['title'] ?? ''),
            'venue'          => trim($input['venue'] ?? ''),
            'performed_at'   => to_datetime($input['performed_at'] ?? ''),
            'poster_url'     => trim($input['poster_url'] ?? ''),
            'venue_map_url'  => trim($input['venue_map_url'] ?? ''),
            'program_intro'  => trim($input['program_intro'] ?? ''),
            'price_info'     => trim($input['price_info'] ?? ''),
            'notices'        => trim($input['notices'] ?? ''),
            'sales_start_at' => to_datetime($input['sales_start_at'] ?? ''),
            'sales_end_at'   => to_datetime($input['sales_end_at'] ?? ''),
            'status'         => $input['status'] ?? 'draft',
        ];
    }

    private function validate(array $data): array
    {
        $errors = [];

        if ($data['title'] === '') {
            $errors['title'] = '標題不能為空';
        } elseif (mb_strlen($data['title']) > 120) {
            $errors['title'] = '標題不能超過 120 字';
        }

        if ($data['venue'] === '') {
            $errors['venue'] = '場館不能為空';
        }

        if ($data['performed_at'] === '') {
            $errors['performed_at'] = '請填寫演出時間';
        }

        if ($data['sales_start_at'] === '') {
            $errors['sales_start_at'] = '請填寫開賣時間';
        }
        if ($data['sales_end_at'] === '') {
            $errors['sales_end_at'] = '請填寫結束販售時間';
        }
        if ($data['sales_start_at'] !== '' && $data['sales_end_at'] !== ''
            && $data['sales_start_at'] >= $data['sales_end_at']) {
            $errors['sales_end_at'] = '結束販售時間必須晚於開賣時間';
        }

        if (!in_array($data['status'], self::STATUSES, true)) {
            $errors['status'] = '狀態不正確';
        }

        return $errors;
    }
}
