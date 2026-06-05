<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_verification_email(string $email, string $name, string $code): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['MAIL_HOST'] ?? 'localhost';
        $mail->Port = (int)($_ENV['MAIL_PORT'] ?? 587);
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password = $_ENV['MAIL_PASSWORD'] ?? '';

        $encryption = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        $mail->CharSet = 'UTF-8';

        $mail->setFrom(
            $_ENV['MAIL_FROM'] ?? 'noreply@ticket-platform.local',
            $_ENV['MAIL_FROM_NAME'] ?? 'Ticket Platform'
        );

        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = '您的註冊驗證碼';
        $mail->Body = get_verification_email_html($name, $code);
        $mail->AltBody = "親愛的 {$name}，\n\n您的註冊驗證碼是：{$code}\n\n此驗證碼將在 10 分鐘後過期。\n\n謝謝！";

        return $mail->send();
    } catch (Exception $e) {
        error_log("郵件發送失敗: " . $mail->ErrorInfo);
        return false;
    }
}

function get_verification_email_html(string $name, string $code): string
{
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h1>註冊驗證碼</h1>
    <p>親愛的 {$name}，</p>
    <p>感謝您註冊演唱會票務平台！</p>
    <p>您的驗證碼是：</p>
    <h2 style="letter-spacing: 4px;">{$code}</h2>
    <p>此驗證碼將在 10 分鐘後過期。</p>
</body>
</html>
HTML;
}