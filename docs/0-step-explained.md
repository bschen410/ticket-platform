# 第零步詳細說明

> 本文件逐一說明第零步建立的每個檔案：它的職責、為什麼這樣寫、以及之後 A/B/C 三人在開發時要怎麼用它。
> 對應的清單版速查請看 [`0-step.md`](0-step.md)；整體設計理念看 [`design.md`](design.md)。

---

## 整體脈絡

第零步的目標：把**三人都會依賴**的共用基礎一次建好，避免後續開發時三個人改同一個檔案打架。

我們採用「**輕量 MVC**」架構（不引入框架）：

```
請求 → public/.htaccess (URL rewrite)
     → public/index.php (路由)
     → src/Controllers/XxxController.php (處理邏輯)
     → src/helpers/db.php (查 DB)
     → src/Views/xxx/yyy.php (套版輸出 HTML)
```

每個層的職責切得很清楚，讓組員開發新功能時都有「應該寫在哪」的明確答案。

---

## 檔案分類總覽

| 分類 | 檔案 | 角色 |
|---|---|---|
| **環境設定** | `.env.example`、`.gitignore` | 隔離設定與機密 |
| **資料庫** | `sql/schema.sql`、`sql/seed.sql`、`sql/init_admin.php` | 一鍵建立可用 DB |
| **入口路由** | `public/.htaccess`、`public/index.php` | 接住所有請求並派發 |
| **共用工具** | `src/helpers/*.php` | DB、登入、CSRF、view、flash 五個 helper |
| **範例 controller / view** | `HomeController.php`、`layouts/main.php`、`home/index.php`、`errors/404.php` | 給組員照抄的範本 |
| **說明文件** | `README.md`、`docs/0-step.md` | 組員上手指引 |

---

## 一、環境設定

> 註：原本還有 `config/database.php` 跟 `bin/init_admin.php`，後來基於 YAGNI 簡化掉了——`config/database.php` 合併進 `src/helpers/db.php`、`bin/init_admin.php` 搬到 `sql/` 跟其他 DB 初始化檔放一起。

### `.env.example`

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ticket_platform
DB_USER=root
DB_PASS=

APP_ENV=development
```

**為什麼這樣寫**：
- 把「會因人而異」的設定（DB 密碼、host）抽出來，避免每個組員 clone 後都要去改 `config/database.php`。
- `.env.example` 進 git 當範本；組員執行 `cp .env.example .env` 後在 `.env` 填自己的密碼。**`.env` 本身被 `.gitignore` 排除**，密碼不會上傳。
- 沒用 `vlucas/phpdotenv` 套件，自己寫 10 行讀檔，**減少組員要學的東西**。

### `.gitignore`

排除 `.env`、`vendor/`、`composer.lock`、IDE 設定、`*.log`。**重點是 `.env` 一定要排除**，密碼絕對不能上 git。

DB 連線設定的讀取邏輯**直接寫在 `src/helpers/db.php` 裡**（見下一節），不再獨立成 `config/` 目錄。

---

## 二、資料庫

### `sql/schema.sql` — 5 張表的 DDL

對應 `design.md` 的資料模型：

| 表 | 角色 |
|---|---|
| `users` | 帳號（含 `role` 區分 customer / admin） |
| `concerts` | 演唱會本體 |
| `zones` | 演唱會的票區（VIP / A 區 / B 區），含**庫存欄位** `total_seats`, `sold_seats` |
| `orders` | 訂單本體（含過期時間 `expires_at`） |
| `order_items` | 訂單細目（哪個 zone、幾張、座位 `seat_labels` 用 JSON 存） |

**幾個關鍵設計**：

1. **`ENGINE=InnoDB`**：必須，因為我們要用 `transaction` + `SELECT ... FOR UPDATE` 做搶票競態處理（這是專題技術亮點）。MyISAM 不支援 transaction 會破功。

2. **`utf8mb4`**：支援 emoji 與所有中文字（包括罕用字）。`utf8` 在 MySQL 是殘缺版只到 3 byte，會有坑。

3. **`zones.sold_seats`**：**「已售」是真實狀態，剩餘 = `total_seats - sold_seats`**。pending 訂單也會佔用 `sold_seats`，過期才回扣。這是為了讓「庫存」單一來源，避免重複計算。

4. **外鍵 `ON DELETE CASCADE`** 只加在 `zones`（演唱會刪了就刪區）跟 `order_items`（訂單刪了就刪細目）。`orders` 對 `users`、`concerts` 不 CASCADE，避免誤刪歷史單。

5. **索引**：
   - `orders` 的 `(status, expires_at)` 是給「找出過期 pending 單」用的（lazy expiration 流程）
   - `orders` 的 `user_id` 是給「我的訂單」頁面用

6. **預設先 `DROP TABLE IF EXISTS`**：開發初期允許重跑 schema 砍掉重建。正式上線時這段要拿掉。

### `sql/seed.sql` — Demo 資料

3 場演唱會、各 3 個區。**故意把區位數設得不大**（100~900 席），這樣 demo 時容易示範「售完」場景。

### `sql/init_admin.php` — 建立 admin 帳號

```php
$hash = password_hash('admin1234', PASSWORD_DEFAULT);
// INSERT INTO users (...) VALUES (?, ?, ?, "admin");
```

**為什麼獨立成 PHP 腳本而不寫在 seed.sql 裡**：
- bcrypt 雜湊**不能寫死在 SQL** 裡（每次 hash 結果不同，且需要 PHP 算）。
- 用 PHP 腳本可以**重複執行不會出錯**（先查有沒有同 email，有就略過）。
- 組員看一眼這個檔就知道：「喔，admin 帳號是 `admin@example.com` / `admin1234`」。

**為什麼放在 `sql/` 而不是 `bin/`**：DB 初始化的三個檔（schema、seed、init_admin）放在一起，語意統一。原本想用 `bin/` 但目錄裡只會有這一個檔案，沒必要為它開目錄。

---

## 三、入口路由

### `public/.htaccess` — URL rewrite

```apache
RewriteEngine On

# 已存在的檔案（CSS / JS / img）直接走
RewriteCond %{REQUEST_FILENAME} -f [OR]
RewriteCond %{REQUEST_FILENAME} -d
RewriteRule ^ - [L]

# 其餘全部交給 index.php
RewriteRule ^ index.php [QSA,L]
```

**作用**：
- 使用者訪問 `/login`、`/concerts/3`、`/orders` 等漂亮網址時，**全部都導到 `index.php`**，由 PHP 自己解析 URL。
- 但是 `/assets/css/app.css` 這種**實體存在的檔案**就直接給瀏覽器，不繞 PHP。

**注意**：用 `php -S` 內建 server 開發時其實不需要 `.htaccess`（PHP 內建會直接把 request 送到 `public/index.php`）；這個檔案是給正式部署到 Apache / XAMPP 時用的。

### `public/index.php` — 路由核心

這個檔案做四件事：

#### (1) 啟動 session + 載入 helper

```php
session_start();
require_once __DIR__ . '/../src/helpers/db.php';
require_once __DIR__ . '/../src/helpers/auth.php';
// ... csrf, view, flash
```

所有頁面都吃到這些 helper。

#### (2) 自動載入 Controller class

```php
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/src/';
    $path = $base . str_replace('\\', '/', $class) . '.php';
    if (is_file($path)) require_once $path;
});
```

之後寫 `new AuthController()` 時自動找 `src/AuthController.php`，寫 `new Admin\ConcertController()` 自動找 `src/Admin/ConcertController.php`。**組員不用每次都 require**。

> 注意：實際的 controller 檔放在 `src/Controllers/HomeController.php`，但 autoload 是用 class 全名直接對應路徑。後續若採用 namespace（如 `App\Controllers\HomeController`）會更乾淨，但為了簡單起見目前不用 namespace。

#### (3) 路由表（重點！）

```php
$routes = [
    ['GET',  '/',                          [HomeController::class,    'index']],

    // 帳號（A 負責 — 之後實作）
    // ['GET',  '/login',                  [AuthController::class,    'showLogin']],
    // ['POST', '/login',                  [AuthController::class,    'login']],
    // ...
];
```

- **每一個 (HTTP method, URL pattern) 對應一個 controller method**。
- A/B/C 三人**只需要在這個 `$routes` 陣列裡加自己的路由**，不會改到別人的代碼。
- `{id}` 寫法支援動態段（會被 regex 替換成 `\d+`）。
- 我已經把所有預計會用到的路由都**寫成註解占位**，組員開發時把對應行的 `//` 拿掉即可。

#### (4) 路由匹配 + 派發

```php
$matched = match_route($method, $uri, $routes);

if ($matched === null) {
    http_response_code(404);
    render('errors/404', ['uri' => $uri]);
    return;
}

[$handler, $params] = $matched;
[$class, $action] = $handler;
(new $class())->{$action}(...array_values($params));
```

簡單明瞭：找到就呼叫 controller，找不到就顯示 404。

---

## 四、共用工具（`src/helpers/`）

這五個檔案是**整個系統的共用層**，三人都會用到，所以一定要在第零步建好、講清楚規則。

### `db.php` — PDO 連線 + query helper

```php
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    // 讀 .env 填到 $_ENV
    // 用 $_ENV 組 DSN
    // 建立 PDO（singleton）
    return $pdo;
}

function query(string $sql, array $params = []): PDOStatement {
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
```

這個檔案同時負責：**讀 `.env`、組 DSN、建 PDO 連線、提供 `query()` 捷徑**。原本 `.env` 讀取在 `config/database.php`，已合併到此。

**關鍵安全設定**：
- `ATTR_ERRMODE => ERRMODE_EXCEPTION`：DB 出錯丟例外（不會被吞掉）
- `ATTR_EMULATE_PREPARES => false`：**用真的 prepared statement**，這是防 SQL injection 的關鍵。emulated 版本在某些舊版 MySQL 會有風險。
- `ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC`：`$row['name']` 而不是 `$row[0]`，可讀性更好。

**組員必須遵守的規則**：
- **任何 SQL 都要用 `query($sql, $params)` 或 `db()->prepare()`**
- **絕對禁止**字串拼 SQL：`"SELECT * FROM users WHERE email = '$email'"` ← 這樣寫直接 SQL injection
- 正確：`query('SELECT * FROM users WHERE email = ?', [$email])`

### `auth.php` — 登入狀態與權限

```php
function current_user(): ?array { /* 從 session 撈 user_id，查 DB 回 user row */ }
function require_login(): void { /* 沒登入就 redirect 到 /login */ }
function require_admin(): void { /* 沒登入或不是 admin 就擋 */ }
```

**組員用法**：
- 任何「需要登入才能看」的頁面，controller 第一行寫 `require_login();`
- 任何 admin 頁面，controller 第一行寫 `require_admin();`
- view 裡判斷登入狀態：`$u = current_user(); if ($u) { ... }`

**內部細節**：
- `current_user()` 有 static 快取，**同一個 request 內只會查一次 DB**，view 跟 controller 多次呼叫不會打 DB 多次。
- `require_login()` 會把當前 URL 存到 `$_SESSION['return_to']`，登入後 A 寫的 controller 應該檢查這個變數並 redirect 回原頁面。

### `csrf.php` — CSRF token 防護

```php
function csrf_token(): string { /* 產生並回傳 session 內的 token */ }
function csrf_field(): string { /* 回傳 <input type="hidden" name="_csrf" ...> */ }
function csrf_check(): void { /* 比對 POST 內的 _csrf 與 session 內的 token，不對就 419 */ }
```

**為什麼需要**：
- 防止其他網站用 `<form action="https://你的站/orders" method="POST">` 偷送請求。
- 因為攻擊網站**拿不到** session 裡的 token，所以擋得住。

**組員用法**：
- 任何 `<form method="post">` 裡面**一定要放** `<?= csrf_field() ?>`
- 任何 controller 處理 POST 的方法**第一行寫** `csrf_check();`
- GET 不需要 CSRF token（GET 本來就不該改資料）

### `view.php` — 模板輸出

```php
function e(?string $s): string { return htmlspecialchars(...); }

function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void {
    // 1. 把 $data 解到 view scope 裡
    // 2. ob_start 把 view 的輸出抓進 $content
    // 3. 把 $content 放進 layout 印出
}
```

**設計**：
- `render('home/index', ['x' => $x])` 預設會用 `layouts/main.php` 包外框。
- 不要外框：`render('xxx', $data, null)`
- `e()` 是**全站防 XSS 的關鍵**，任何 view 內輸出變數一律 `<?= e($var) ?>`。

**組員必須遵守的規則**：
- 在 view 裡輸出任何變數：**一律 `e()`**
  - `<?= e($name) ?>` ✅
  - `<?= $name ?>` ❌（會被 XSS）
- 只有確定要輸出 HTML 標籤的地方才能不 `e()`（例如 `<?= $content ?>` 在 layout 裡）

### `flash.php` — 一次性訊息

```php
function flash(string $key, string $message): void { /* 存到 $_SESSION['_flash'][$key] */ }
function flash_pull(string $key): ?string { /* 讀出來並清掉 */ }
function flash_all_pull(): array { /* 讀全部並清掉，供 layout 渲染用 */ }
```

**使用情境**：
```php
// Controller 裡
flash('success', '訂單已成立');
header('Location: /my/orders');
exit;

// Layout 裡（已經寫好了）
foreach (flash_all_pull() as $key => $message) {
    echo "<div class='alert alert-$key'>$message</div>";
}
```

支援的 key：`success`、`info`、`warning`、`error`（會被 layout 渲染成 `alert-success` 等 Bootstrap class）。

---

## 五、範例 Controller / View

### `src/Controllers/HomeController.php`

```php
class HomeController {
    public function index(): void {
        // 試著查 concerts 表，確認 DB 通了
        $row = query('SELECT COUNT(*) AS c FROM concerts')->fetch();
        render('home/index', [
            'dbStatus'     => 'connected',
            'concertCount' => (int)$row['c'],
        ]);
    }
}
```

**這是給組員照抄的範本**。一個典型 controller 方法的流程：
1. （可選）`require_login()` 或 `require_admin()`
2. （POST 才需要）`csrf_check()`
3. 從 `$_POST` / `$_GET` 收輸入
4. 用 `query()` 操作 DB
5. `render('xxx/yyy', $data)` 渲染畫面
6. 或 `header('Location: /xxx'); exit;` 跳轉

### `src/Views/layouts/main.php` — 共用外框

包含：
- Bootstrap 5 CSS / JS（CDN 引入，**不用裝 npm 也不用下載**）
- 導覽列：根據 `current_user()` 顯示「登入/註冊」或「Hi, XXX / 我的訂單 / 後台 / 登出」
- Flash 訊息區（自動印出所有 flash）
- `<?= $content ?>` 是 view 的本體會被插入的位置
- footer

**已經處理好**：登入狀態、權限按鈕（admin 才看得到「後台」）、CSRF 在登出表單裡（範例）。

### `src/Views/home/index.php`

範例頁面：顯示「Hello」+ DB 連線狀態 + 已 seed 演唱會數。**主要功能是驗證骨架通了**。

### `src/Views/errors/404.php`

簡單的 404 頁面。

---

## 六、為什麼這樣切？

幾個設計決策的理由：

### 1. 為什麼不用框架（Laravel / Slim）？
- **老師要看到「學生自己寫的邏輯」**，框架會包掉路由、ORM、session、validation 等等，剩下能寫的太少。
- 原生 PHP 8 已經很夠用，路由 + autoload + helper 自己寫不超過 200 行。

### 2. 為什麼用 helper functions 而不是 class？
- 學生熟悉 function 多過 OOP，`query(...)` 比 `Db::getInstance()->query(...)` 直觀。
- 時程緊，**少打字、少出錯**。
- Controller / Model 還是用 class（因為要 autoload + 方法分組）。

### 3. 為什麼 helper 用 `require_once` 而不是 autoload？
- Helper 是 function 不是 class，PHP autoload 只認 class。
- 在 `index.php` 一次 require 五個檔，**之後就全站可用**，符合 helper 「隨時都在」的語意。

### 4. 為什麼路由放在 `index.php` 的陣列而不是分檔？
- 路由表只會被一個人改（每次新加 controller 才動），三人不會同時搶這個檔。
- **集中在一處看得到全站有哪些路由**，debug 容易。

### 5. 為什麼 view 用純 PHP 而不是 Twig / Blade？
- 樣板引擎要先學語法。原生 PHP `<?= e($x) ?>` / `<?php foreach (...): ?>` 雖然醜，**但組員馬上就會寫**。
- 1–2 週時程，省學習成本最重要。

---

## 七、組員開發新功能時的標準流程

以 A 同學要做「登入頁」為例：

### Step 1：到 `public/index.php` 解開兩行路由註解
```php
['GET',  '/login',  [AuthController::class, 'showLogin']],
['POST', '/login',  [AuthController::class, 'login']],
```

### Step 2：建 `src/Controllers/AuthController.php`
```php
class AuthController {
    public function showLogin(): void {
        render('auth/login');
    }

    public function login(): void {
        csrf_check();                                            // 防 CSRF

        $email = $_POST['email'] ?? '';
        $stmt  = query('SELECT * FROM users WHERE email = ?', [$email]);   // prepared statement 防 SQLi
        $user  = $stmt->fetch();

        if (!$user || !password_verify($_POST['password'] ?? '', $user['password_hash'])) {
            flash('error', '帳號或密碼錯誤');
            header('Location: /login');
            exit;
        }

        session_regenerate_id(true);                             // 防 session fixation
        $_SESSION['user_id'] = $user['id'];

        flash('success', '登入成功');
        header('Location: ' . ($_SESSION['return_to'] ?? '/'));
        exit;
    }
}
```

### Step 3：建 `src/Views/auth/login.php`
```php
<form method="post" action="/login" class="card p-4">
    <?= csrf_field() ?>
    <input name="email"    type="email"    class="form-control mb-2" placeholder="Email">
    <input name="password" type="password" class="form-control mb-2" placeholder="密碼">
    <button class="btn btn-primary">登入</button>
</form>
```

### Step 4：跑起來、測試、commit

完成。**全程沒動到 B / C 的檔案**。

---

## 八、驗證骨架通了的指令

```bash
mysql -u root -p -e "CREATE DATABASE ticket_platform CHARACTER SET utf8mb4;"
mysql -u root -p ticket_platform < sql/schema.sql
mysql -u root -p ticket_platform < sql/seed.sql
cp .env.example .env
# 編輯 .env 填好 DB_PASS
php sql/init_admin.php
php -S localhost:8000 -t public
```

開 `http://localhost:8000/`，應該看到：
- 標題「Hello, ticket platform 🎟️」
- DB 連線：`connected`（綠色 badge）
- 已種子演唱會：`3` 場

如果這三個都對，**第零步成功**，三人可以開始平行開工了。
