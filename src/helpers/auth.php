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

function user_exists(string $email): bool
{
    $stmt = query('SELECT id FROM users WHERE email = ?', [$email]);
    return $stmt->fetch() !== false;
}

function create_user(string $name, string $email, string $password): int
{
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = query(
        'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)',
        [$name, $email, $password_hash]
    );
    return (int)db()->lastInsertId();
}

function validate_register(array $input): array
{
    $errors = [];

    // 名字驗證
    $name = trim($input['name'] ?? '');
    if (empty($name)) {
        $errors['name'] = '名字不能為空';
    } elseif (strlen($name) > 80) {
        $errors['name'] = '名字不能超過 80 個字元';
    }

    // Email 驗證
    $email = trim($input['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email 不能為空';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email 格式不正確';
    } elseif (user_exists($email)) {
        $errors['email'] = '此 Email 已被註冊';
    }

    // 密碼驗證
    $password = $input['password'] ?? '';
    if (empty($password)) {
        $errors['password'] = '密碼不能為空';
    } elseif (strlen($password) < 6) {
        $errors['password'] = '密碼至少需要 6 個字元';
    }

    // 確認密碼
    $password_confirm = $input['password_confirm'] ?? '';
    if ($password !== $password_confirm) {
        $errors['password_confirm'] = '兩次輸入的密碼不符';
    }

    return $errors;
}
