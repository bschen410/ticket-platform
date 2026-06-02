<?php

declare(strict_types=1);

class AuthController
{
    public function showRegister(): void
    {
        render('auth/register');
    }

    public function showLogin(): void
    {
        render('auth/login');
    }

    public function login(): void
    {
        csrf_check();

        $errors = validate_login($_POST);

        if (!empty($errors)) {
            $_SESSION['login_errors'] = $errors;
            $_SESSION['login_form'] = [
                'email' => $_POST['email'] ?? '',
            ];
            header('Location: /login');
            exit;
        }

        // 嘗試登入
        $user = attempt_login(
            trim($_POST['email']),
            $_POST['password']
        );

        if ($user === null) {
            $_SESSION['login_errors'] = [
                'email' => '郵箱或密碼錯誤',
            ];
            $_SESSION['login_form'] = [
                'email' => $_POST['email'] ?? '',
            ];
            header('Location: /login');
            exit;
        }

        // 登入成功
        $_SESSION['user_id'] = $user['id'];

        // 清除登入表單資料
        unset($_SESSION['login_errors'], $_SESSION['login_form']);

        // 設定成功訊息
        flash('success', '登入成功！歡迎 ' . $user['name']);

        // 重導到首頁（或返回之前的頁面）
        $return_to = $_SESSION['return_to'] ?? '/';
        unset($_SESSION['return_to']);
        header('Location: ' . $return_to);
        exit;
    }

    public function logout(): void
    {
        // 清除用戶 session
        unset($_SESSION['user_id']);

        // 設定登出訊息
        flash('success', '已登出，再見！');

        // 重導到首頁
        header('Location: /');
        exit;
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

        // 設定成功訊息
        flash('success', '註冊成功！歡迎 ' . trim($_POST['name']));

        // 重導到首頁
        header('Location: /');
        exit;
    }
}
