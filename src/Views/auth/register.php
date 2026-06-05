<?php
$errors = $_SESSION['register_errors'] ?? [];
$form = $_SESSION['register_form'] ?? [];
?>

<div class="mx-auto flex min-h-[70vh] max-w-md items-center">
    <div class="w-full rounded-2xl bg-white p-8 shadow-lg ring-1 ring-slate-200">
        <h1 class="text-center text-2xl font-bold text-slate-900">建立帳號</h1>

        <?php if (!empty($errors)): ?>
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>註冊失敗，請檢查以下問題：</strong>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="mt-6 space-y-4">
            <?= csrf_field(); ?>

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">名字</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    placeholder="請輸入你的名字"
                    value="<?= e($form['name'] ?? '') ?>"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['name']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                    required
                >
                <?php if (isset($errors['name'])): ?>
                    <span class="mt-1 block text-sm text-red-600"><?= e($errors['name']) ?></span>
                <?php endif; ?>
            </div>

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
                    placeholder="至少 6 個字元"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['password']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="mt-1 block text-sm text-red-600"><?= e($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <div>
                <label for="password_confirm" class="mb-1 block text-sm font-medium text-slate-700">確認密碼</label>
                <input
                    type="password"
                    id="password_confirm"
                    name="password_confirm"
                    placeholder="再輸入一次密碼"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['password_confirm']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                    required
                >
                <?php if (isset($errors['password_confirm'])): ?>
                    <span class="mt-1 block text-sm text-red-600"><?= e($errors['password_confirm']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">建立帳號</button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            已有帳號？<a href="/login" class="font-semibold text-slate-900 hover:underline">立即登入</a>
        </div>
    </div>
</div>
