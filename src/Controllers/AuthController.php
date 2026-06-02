<?php

declare(strict_types=1);

class AuthController
{
    public function showRegister(): void
    {
        render('auth/register');
    }

    public function register(): void
    {
        csrf_check();

        $errors = validate_register($_POST);

        if (!empty($errors)) {
            $_SESSION['register_errors'] = $errors;
            $_SESSION['register_form'] = [
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
            ];
            header('Location: /register');
            exit;
        }

        // 建立用戶
        $user_id = create_user(
            trim($_POST['name']),
            trim($_POST['email']),
            $_POST['password']
        );

        // 自動登入
        $_SESSION['user_id'] = $user_id;

        // 清除註冊表單資料
        unset($_SESSION['register_errors'], $_SESSION['register_form']);

        // 重導到首頁
        header('Location: /');
        exit;
    }
}
