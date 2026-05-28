<?php

declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? '';
    $expect = $_SESSION['csrf_token'] ?? '';
    if (!is_string($sent) || $expect === '' || !hash_equals($expect, $sent)) {
        http_response_code(419);
        echo 'CSRF token 驗證失敗，請重新整理頁面再試。';
        exit;
    }
}
