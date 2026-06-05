<?php
$errors = $_SESSION['login_errors'] ?? [];
$form = $_SESSION['login_form'] ?? [];
?>

<div class="mx-auto flex min-h-[70vh] max-w-md items-center">
    <div class="w-full rounded-2xl bg-white p-8 shadow-lg ring-1 ring-slate-200">
        <h1 class="text-center text-2xl font-bold text-slate-900">登入</h1>

        <?php if (!empty($errors)): ?>
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>登入失敗，請檢查以下問題：</strong>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="mt-6 space-y-4">
            <?= csrf_field(); ?>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="你的 Email"
                    value="<?= e($form['email'] ?? '') ?>"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['email']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="mt-1 block text-sm text-red-600"><?= e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">密碼</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="請輸入密碼"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['password']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="mt-1 block text-sm text-red-600"><?= e($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">登入</button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            還沒有帳號？<a href="/register" class="font-semibold text-slate-900 hover:underline">立即註冊</a>
        </div>
    </div>
</div>
