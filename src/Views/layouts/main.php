<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>演唱會票務平台</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">🎤 演唱會票務</a>
        <div class="ms-auto d-flex gap-2">
            <?php $u = current_user(); ?>
            <?php if ($u): ?>
                <span class="navbar-text text-light">Hi, <?= e($u['name']) ?></span>
                <?php if ($u['role'] === 'admin'): ?>
                    <a class="btn btn-sm btn-outline-warning" href="/admin/concerts">後台</a>
                <?php endif; ?>
                <a class="btn btn-sm btn-outline-light" href="/my/orders">我的訂單</a>
                <form method="post" action="/logout" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-light">登出</button>
                </form>
            <?php else: ?>
                <a class="btn btn-sm btn-outline-light" href="/login">登入</a>
                <a class="btn btn-sm btn-light" href="/register">註冊</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?php foreach (flash_all_pull() as $key => $message): ?>
        <div class="alert alert-<?= e($key === 'error' ? 'danger' : $key) ?>" role="alert">
            <?= e($message) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="container py-4 text-center text-muted small">
    動態網頁程式設計 期末專題 · <?= date('Y') ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
