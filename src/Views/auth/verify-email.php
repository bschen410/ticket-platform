<h1>Email 驗證</h1>

<p>我們已經寄出 6 位數驗證碼到你的 Email，請輸入驗證碼完成註冊。</p>

<?php
$errors = $_SESSION['verify_errors'] ?? [];
unset($_SESSION['verify_errors']);
?>

<form method="POST" action="/verify-email">
    <?= csrf_field() ?>

    <div>
        <label for="code">驗證碼</label>
        <input
            type="text"
            id="code"
            name="code"
            maxlength="6"
            required
        >

        <?php if (!empty($errors['code'])): ?>
            <p style="color: red;"><?= htmlspecialchars($errors['code']) ?></p>
        <?php endif; ?>
    </div>

    <button type="submit">完成驗證</button>
</form>