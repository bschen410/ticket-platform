<?php

declare(strict_types=1);

// 表單共用 helper：datetime-local 轉換 + 驗證失敗時的 errors/old 暫存。

// HTML datetime-local（"2026-07-20T19:30"）→ MySQL DATETIME（"2026-07-20 19:30:00"）。
// 空字串維持空字串，交給驗證處理。
function to_datetime(string $input): string
{
    $input = trim($input);
    if ($input === '') {
        return '';
    }
    $input = str_replace('T', ' ', $input);
    // 補秒數
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $input)) {
        $input .= ':00';
    }
    return $input;
}

// DATETIME（"2026-07-20 19:30:00"）→ datetime-local input value（"2026-07-20T19:30"）。
function to_datetime_local(?string $value): string
{
    if (!$value) {
        return '';
    }
    return substr(str_replace(' ', 'T', $value), 0, 16);
}

// 驗證失敗時把錯誤與原輸入存進 session，重導後由表單頁讀回。
function stash_form_errors(array $errors, array $old = []): void
{
    $_SESSION['form_errors'] = $errors;
    $_SESSION['form_old']    = $old;
}

function pull_form_errors(): array
{
    $errors = $_SESSION['form_errors'] ?? [];
    unset($_SESSION['form_errors']);
    return $errors;
}

function pull_form_old(): array
{
    $old = $_SESSION['form_old'] ?? [];
    unset($_SESSION['form_old']);
    return $old;
}
