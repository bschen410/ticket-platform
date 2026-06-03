<?php
$errors = $_SESSION['login_errors'] ?? [];
$form = $_SESSION['login_form'] ?? [];
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登入</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.5);
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover {
            background: #5568d3;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: block;
        }
        .alert {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        input.error {
            border-color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>登入</h1>
        
        <?php if (!empty($errors)): ?>
        <div class="alert">
            <strong>登入失敗，請檢查以下問題：</strong>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <?php echo csrf_field(); ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="你的 Email"
                    value="<?php echo htmlspecialchars($form['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                    class="<?php echo isset($errors['email']) ? 'error' : ''; ?>"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                <span class="error-message"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">密碼</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="請輸入密碼"
                    class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                <span class="error-message"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <button type="submit">登入</button>
        </form>
        
        <div class="register-link">
            還沒有帳號？<a href="/register">立即註冊</a>
        </div>
    </div>
</body>
</html>
