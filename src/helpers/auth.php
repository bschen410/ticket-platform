<?php

declare(strict_types=1);

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached !== null && $cached['id'] === $_SESSION['user_id']) {
        return $cached;
    }
    $stmt = query('SELECT id, email, name, role FROM users WHERE id = ?', [$_SESSION['user_id']]);
    $cached = $stmt->fetch() ?: null;
    return $cached;
}

function require_login(): void
{
    if (current_user() === null) {
        $_SESSION['return_to'] = $_SERVER['REQUEST_URI'] ?? '/';
        header('Location: /login');
        exit;
    }
}

function require_admin(): void
{
    require_login();
    if ((current_user()['role'] ?? null) !== 'admin') {
        http_response_code(403);
        echo '禁止存取（需管理員權限）';
        exit;
    }
}
