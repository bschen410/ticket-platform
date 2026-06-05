<?php
// 單一入口路由

declare(strict_types=1);

// 容器預設 UTC；統一以台北時間顯示/計算（需與 db.php 的連線時區一致）。
date_default_timezone_set('Asia/Taipei');

// 內建伺服器（php -S）搭配 router script 時不會自動服務靜態檔；
// 對應到 public/ 下實體檔案的請求（/assets/*）直接交還給伺服器。
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . '/' . ltrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '', '/');
    if (is_file($file)) {
        return false;
    }
}

session_start();

// 加載 Composer 依賴
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../src/helpers/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
require_once __DIR__ . '/../src/helpers/csrf.php';
require_once __DIR__ . '/../src/helpers/view.php';
require_once __DIR__ . '/../src/helpers/flash.php';
require_once __DIR__ . '/../src/helpers/mail.php';

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
    // 演唱會瀏覽（B）
    ['GET',  '/',                           [ConcertController::class, 'index']],
    ['GET',  '/concerts/{id}',              [ConcertController::class, 'show']],

    // 帳號（A）
    ['GET',  '/login',                      [AuthController::class,    'showLogin']],
    ['POST', '/login',                      [AuthController::class,    'login']],
    ['GET',  '/register',                  [AuthController::class,    'showRegister']],
    ['POST', '/register',                  [AuthController::class,    'register']],
    ['GET',  '/verify-email', [VerificationController::class, 'show']],
    ['POST', '/verify-email', [VerificationController::class, 'verify']],
    ['POST', '/logout',                     [AuthController::class,    'logout']],

    // 訂票（C 負責）
    ['POST', '/orders',                     [OrderController::class,   'create']],
    ['GET',  '/orders/{id}',                [OrderController::class,   'show']],
    ['POST', '/orders/{id}/pay',            [OrderController::class,   'pay']],
    ['GET',  '/my/orders',                  [OrderController::class,   'mine']],

    // 管理員後台 — 演唱會 CRUD（B）
    ['GET',  '/admin/concerts',             [Admin\ConcertController::class, 'index']],
    ['GET',  '/admin/concerts/new',         [Admin\ConcertController::class, 'create']],
    ['POST', '/admin/concerts',             [Admin\ConcertController::class, 'store']],
    ['GET',  '/admin/concerts/{id}/edit',   [Admin\ConcertController::class, 'edit']],
    ['POST', '/admin/concerts/{id}',        [Admin\ConcertController::class, 'update']],
    ['POST', '/admin/concerts/{id}/delete', [Admin\ConcertController::class, 'destroy']],

    // 管理員後台 — 區域 CRUD（B）
    ['POST', '/admin/concerts/{id}/zones',  [Admin\ZoneController::class, 'store']],
    ['POST', '/admin/zones/{id}',           [Admin\ZoneController::class, 'update']],
    ['POST', '/admin/zones/{id}/delete',    [Admin\ZoneController::class, 'destroy']],
];

// 簡易 autoload：依 class 名稱找 src/Controllers/、src/Models/
// 例：HomeController         → src/Controllers/HomeController.php
//     User                   → src/Models/User.php
//     Admin\ConcertController → src/Controllers/Admin/ConcertController.php
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/src/';
    $relative = str_replace('\\', '/', $class) . '.php';
    foreach (['Controllers/', 'Models/', ''] as $subdir) {
        $path = $base . $subdir . $relative;
        if (is_file($path)) {
            require_once $path;
            return;
        }
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

// 動態段（{id}）正則只配數字，轉成 int 再傳入 controller（strict_types 需要）
$args = array_map(
    static fn($v) => ctype_digit((string) $v) ? (int) $v : $v,
    array_values($params)
);

(new $class())->{$action}(...$args);
