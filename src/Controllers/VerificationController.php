<?php

declare(strict_types=1);

class VerificationController
{
    public function show(): void
    {
        if (empty($_SESSION['pending_verification_user_id'])) {
            flash('error', '請先註冊帳號');
            header('Location: /register');
            exit;
        }

        render('auth/verify-email');
    }

    public function verify(): void
    {
        csrf_check();

        $userId = $_SESSION['pending_verification_user_id'] ?? null;
        $code = trim($_POST['code'] ?? '');

        if ($userId === null) {
            flash('error', '驗證流程已失效，請重新註冊或登入');
            header('Location: /register');
            exit;
        }

        if ($code === '') {
            $_SESSION['verify_errors'] = [
                'code' => '請輸入驗證碼',
            ];
            header('Location: /verify-email');
            exit;
        }

        $user = find_user_by_id((int)$userId);

        if (!$user) {
            flash('error', '找不到使用者');
            header('Location: /register');
            exit;
        }

        if ($user['verification_expires_at'] !== null && strtotime($user['verification_expires_at']) < time()) {
            delete_user_by_id((int)$userId);

            unset($_SESSION['pending_verification_user_id'], $_SESSION['verify_errors']);

            flash('error', '驗證碼已過期，請重新註冊。');

            header('Location: /register');
            exit;
        }

        if (!password_verify($code, $user['verification_code'])) {
            delete_user_by_id((int)$userId);

            unset($_SESSION['pending_verification_user_id'], $_SESSION['verify_errors']);

            flash('error', '驗證碼錯誤，請重新註冊。');

            header('Location: /register');
            exit;
        }

        mark_email_as_verified((int)$userId);

        unset($_SESSION['pending_verification_user_id'], $_SESSION['verify_errors']);

        $_SESSION['user_id'] = $userId;

        flash('success', 'Email 驗證成功，已完成登入！');

        header('Location: /');
        exit;
    }
}