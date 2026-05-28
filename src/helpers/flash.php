<?php

declare(strict_types=1);

// 設定一次性訊息：flash('success', '已登入')
// 讀取（並清掉）：flash_pull('success')
function flash(string $key, string $message): void
{
    $_SESSION['_flash'][$key] = $message;
}

function flash_pull(string $key): ?string
{
    $message = $_SESSION['_flash'][$key] ?? null;
    if (isset($_SESSION['_flash'][$key])) {
        unset($_SESSION['_flash'][$key]);
    }
    return $message;
}

function flash_all_pull(): array
{
    $all = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $all;
}
