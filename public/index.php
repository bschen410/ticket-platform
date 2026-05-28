<?php
// 單一入口路由

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../src/helpers/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/csrf.php';
require_once __DIR__ . '/../src/helpers/view.php';
require_once __DIR__ . '/../src/helpers/flash.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';

// 比對 (METHOD, PATH) -> [Controller, method, params]
// 動態段以 {id} 表示，僅支援數字
function match_route(string $method, string $uri, array $routes): ?array
{
    foreach ($routes as [$m, $pattern, $handler]) {
        if ($m !== $method) continue;
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', $pattern) . '$#';
        if (preg_match($regex, $uri, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return [$handler, $params];
        }
    }
    return null;
}

$routes = [
    // 首頁 + 範例
    ['GET',  '/',                          [HomeController::class,    'index']],

    // 帳號（A 負責 — 之後實作）
    // ['GET',  '/login',                  [AuthController::class,    'showLogin']],
    // ['POST', '/login',                  [AuthController::class,    'login']],
    // ['GET',  '/register',               [AuthController::class,    'showRegister']],
    // ['POST', '/register',               [AuthController::class,    'register']],
    // ['POST', '/logout',                 [AuthController::class,    'logout']],

    // 演唱會（B 負責）
    // ['GET',  '/concerts/{id}',          [ConcertController::class, 'show']],

    // 訂票（C 負責）
    // ['POST', '/orders',                 [OrderController::class,   'create']],
    // ['GET',  '/orders/{id}',            [OrderController::class,   'show']],
    // ['POST', '/orders/{id}/pay',        [OrderController::class,   'pay']],
    // ['GET',  '/my/orders',              [OrderController::class,   'mine']],

    // 管理員後台（B 負責）
    // ['GET',  '/admin/concerts',         [Admin\ConcertController::class, 'index']],
    // ...
];

// 簡易 autoload：依 class 名稱載入 src/Controllers / src/Models
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/src/';
    $path = $base . str_replace('\\', '/', $class) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

$matched = match_route($method, $uri, $routes);

if ($matched === null) {
    http_response_code(404);
    render('errors/404', ['uri' => $uri]);
    return;
}

[$handler, $params] = $matched;
[$class, $action] = $handler;

(new $class())->{$action}(...array_values($params));
