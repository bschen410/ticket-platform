<?php
// 一次性：建立預設 admin 帳號
// 用法：php sql/init_admin.php

require_once __DIR__ . '/../src/helpers/db.php';

$email = 'admin@example.com';
$name  = '系統管理員';
$plain = 'admin1234';

$hash = password_hash($plain, PASSWORD_DEFAULT);

$pdo = db();
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "Admin 帳號 {$email} 已存在，略過。\n";
    exit(0);
}

$stmt = $pdo->prepare(
    'INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, "admin")'
);
$stmt->execute([$email, $hash, $name]);

echo "已建立 admin 帳號：\n";
echo "  Email: {$email}\n";
echo "  Password: {$plain}\n";
