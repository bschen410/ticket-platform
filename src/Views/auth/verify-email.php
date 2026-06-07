<?php
$errors = $_SESSION['verify_errors'] ?? [];
unset($_SESSION['verify_errors']);
$pendingUser = find_user_by_id((int)($_SESSION['pending_verification_user_id'] ?? 0));
$pendingEmail = $pendingUser['email'] ?? '';
?>

<div class="mx-auto flex min-h-[70vh] max-w-md items-center">
    <div class="w-full rounded-2xl bg-white p-8 shadow-lg ring-1 ring-slate-200">
        <h1 class="text-center text-2xl font-bold text-slate-900">Email 驗證</h1>

        <p class="mt-3 text-center text-sm text-slate-600">我們已經寄出 6 位數驗證碼到 <span class="font-medium text-slate-800"><?= e($pendingEmail) ?></span>，請輸入驗證碼完成註冊。</p>

        <?php if (!empty($errors)): ?>
            <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <strong>驗證失敗，請檢查以下問題：</strong>
            </div>
        <?php endif; ?>

        <form method="POST" action="/verify-email" class="mt-6 space-y-4">
            <?= csrf_field() ?>

            <div>
                <label for="code" class="mb-1 block text-sm font-medium text-slate-700">驗證碼</label>
                <input
                    type="text"
                    id="code"
                    name="code"
                    placeholder="請輸入 6 位數驗證碼"
                    maxlength="6"
                    class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition focus:ring-2 <?= isset($errors['code']) ? 'border-red-300 focus:ring-red-200' : 'border-slate-300 focus:border-slate-500 focus:ring-slate-200' ?>"
                    required
                >
                <?php if (isset($errors['code'])): ?>
                    <span class="mt-1 block text-sm text-red-600"><?= e($errors['code']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="w-full rounded-lg bg-[#EA6D4A] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">完成驗證</button>
        </form>
    </div>
</div>
