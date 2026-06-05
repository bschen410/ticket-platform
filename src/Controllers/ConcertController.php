<?php

declare(strict_types=1);

class ConcertController
{
    // 首頁：販售中演唱會列表
    public function index(): void
    {
        render('home/index', [
            'concerts' => Concert::onSaleList(),
        ], 'layouts/homepage');
    }

    // 詳細頁：演唱會資訊 + 各區票價與剩餘
    public function show(int $id): void
    {
        $concert = Concert::findWithZones($id);

        if ($concert === null) {
            abort_404();
        }

        render('concerts/show', ['concert' => $concert]);
    }
}
