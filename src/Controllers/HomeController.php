<?php

declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $dbStatus  = 'unknown';
        $concertCount = 0;

        try {
            $row = query('SELECT COUNT(*) AS c FROM concerts')->fetch();
            $concertCount = (int)($row['c'] ?? 0);
            $dbStatus = 'connected';
        } catch (\Throwable $e) {
            $dbStatus = 'error: ' . $e->getMessage();
        }

        render('home/index', [
            'dbStatus'     => $dbStatus,
            'concertCount' => $concertCount,
        ]);
    }
}
