<?php

declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

// render('home/index', ['x' => 1])
// 預設用 layouts/main 包外框；要不包就傳 layout=null
function render(string $template, array $data = [], ?string $layout = 'layouts/app'): void
{
    $viewsRoot = dirname(__DIR__) . '/Views/';
    $templatePath = $viewsRoot . $template . '.php';

    if (!is_file($templatePath)) {
        http_response_code(500);
        echo "View 不存在：{$template}";
        return;
    }

    extract($data, EXTR_SKIP);

    ob_start();
    require $templatePath;
    $content = ob_get_clean();

    if ($layout === null) {
        echo $content;
        return;
    }

    $layoutPath = $viewsRoot . $layout . '.php';
    if (!is_file($layoutPath)) {
        echo $content;
        return;
    }
    require $layoutPath;
}

// 找不到資源：回 404 並結束請求
function abort_404(): void
{
    http_response_code(404);
    render('errors/404', ['uri' => $_SERVER['REQUEST_URI'] ?? '']);
    exit;
}
