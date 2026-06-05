<?php /** @var string $content */ ?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>演唱會票務平台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">

<nav class="border-b border-slate-200 bg-slate-900 text-white shadow-sm">
    <?php $u = current_user(); ?>
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
        <a class="navbar-brand text-xl font-semibold text-white" href="/">JIOJIAN</a>

        <div class="hidden items-center gap-2 md:flex">
            <?php if ($u): ?>
                <span class="text-sm text-slate-200">Hi, <?= e($u['name']) ?></span>
                <?php if ($u['role'] === 'admin'): ?>
                    <a class="rounded-md border border-amber-400 px-3 py-1.5 text-sm font-medium text-amber-300 transition hover:bg-amber-400 hover:text-slate-900" href="/admin/concerts">後台</a>
                <?php endif; ?>
                <a class="rounded-md border border-slate-400 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-white hover:text-slate-900" href="/my/orders">我的訂單</a>
                <form method="post" action="/logout" class="inline">
                    <?= csrf_field() ?>
                    <button class="rounded-md border border-slate-400 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-white hover:text-slate-900">登出</button>
                </form>
            <?php else: ?>
                <a class="rounded-md border border-slate-400 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-white hover:text-slate-900" href="/login">登入</a>
                <a class="rounded-md bg-white px-3 py-1.5 text-sm font-semibold text-slate-900 transition hover:bg-slate-100" href="/register">註冊</a>
            <?php endif; ?>
        </div>

        <button id="nav-toggle" class="inline-flex items-center rounded-md border border-slate-600 p-2 text-slate-100 transition hover:bg-slate-800 md:hidden" aria-expanded="false" aria-controls="nav-menu" type="button">
            <span class="sr-only">切換選單</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <div id="nav-menu" class="hidden border-t border-slate-700 px-4 pb-4 md:hidden">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 pt-4">
            <?php if ($u): ?>
                <span class="text-sm text-slate-200">Hi, <?= e($u['name']) ?></span>
                <?php if ($u['role'] === 'admin'): ?>
                    <a class="rounded-md border border-amber-400 px-3 py-2 text-sm font-medium text-amber-300" href="/admin/concerts">後台</a>
                <?php endif; ?>
                <a class="rounded-md border border-slate-400 px-3 py-2 text-sm font-medium text-white" href="/my/orders">我的訂單</a>
                <form method="post" action="/logout">
                    <?= csrf_field() ?>
                    <button class="w-full rounded-md border border-slate-400 px-3 py-2 text-left text-sm font-medium text-white">登出</button>
                </form>
            <?php else: ?>
                <a class="rounded-md border border-slate-400 px-3 py-2 text-sm font-medium text-white" href="/login">登入</a>
                <a class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900" href="/register">註冊</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="mx-auto max-w-7xl px-4 py-6">
    <?php foreach (flash_all_pull() as $key => $message): ?>
        <?php $alertClass = $key === 'error' ? 'border-red-200 bg-red-50 text-red-800' : ($key === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-800'); ?>
        <div class="mb-4 rounded-lg border px-4 py-3 text-sm <?= e($alertClass) ?>" role="alert">
            <?= e($message) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-slate-500">
    動態網頁程式設計 期末專題 · <?= date('Y') ?>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('nav-toggle');
        var menu = document.getElementById('nav-menu');
        if (btn && menu) {
            btn.addEventListener('click', function () {
                var isHidden = menu.classList.toggle('hidden');
                btn.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
            });
        }
    });
</script>
</html>
