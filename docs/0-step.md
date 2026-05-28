# 第零步：共用骨架

> 目的：把「三人都會依賴」的共用基礎一次建好。
> 建完之後 A / B / C 三人可各自認領 controller 平行開工，不會在同一檔案打架。

## 已建立的檔案

```
ticket-platform/
├── .env.example                       ← DB 連線範本（複製成 .env 後填密碼）
├── .gitignore
├── README.md                          ← 安裝步驟、demo 帳號、執行方式
├── sql/
│   ├── schema.sql                     ← 5 張表建表
│   ├── seed.sql                       ← 3 場 demo 演唱會 + 各 3 區
│   └── init_admin.php                 ← 一次性：建立 admin 帳號（bcrypt 雜湊密碼）
├── public/
│   ├── .htaccess                      ← 全部請求 rewrite 到 index.php
│   ├── index.php                      ← 路由（switch 解析 URI）
│   └── assets/css/app.css
└── src/
    ├── helpers/
    │   ├── db.php                     ← PDO 單例 + query() helper
    │   ├── auth.php                   ← current_user / require_login / require_admin
    │   ├── csrf.php                   ← csrf_token / csrf_check
    │   ├── view.php                   ← render($tpl, $data) + e()
    │   └── flash.php                  ← session-based 一次性訊息
    ├── Controllers/
    │   └── HomeController.php         ← 範例 controller（顯示「Hello」+ 連線測試）
    └── Views/
        ├── layouts/main.php           ← Bootstrap 5 CDN + nav + flash 顯示
        └── home/index.php             ← 範例頁
```

## 環境啟動步驟

1. 安裝 XAMPP / Laragon（內含 PHP 8 + MySQL 8）
2. 建立 DB 並匯入 schema 與 seed
   ```bash
   mysql -u root -p -e "CREATE DATABASE ticket_platform CHARACTER SET utf8mb4;"
   mysql -u root -p ticket_platform < sql/schema.sql
   mysql -u root -p ticket_platform < sql/seed.sql
   ```
3. 複製環境變數範本並填入 DB 密碼
   ```bash
   cp .env.example .env
   ```
4. 建立 admin 帳號（一次性）
   ```bash
   php sql/init_admin.php
   ```
5. 起服務
   ```bash
   php -S localhost:8000 -t public
   ```
6. 瀏覽 `http://localhost:8000/`，應顯示「Hello, ticket platform」與 DB 連線狀態。

## 分工建議（第零步完成後）

| 人 | 認領範圍 | 主要新增檔案 |
|---|---|---|
| A | 帳號（註冊 / 登入 / 登出） | `src/Controllers/AuthController.php`、`src/Models/User.php`、`src/Views/auth/*` |
| B | 演唱會瀏覽 + 管理員後台 | `src/Controllers/ConcertController.php`、`src/Controllers/Admin/ConcertController.php`、`src/Models/Concert.php`、`src/Models/Zone.php`、`src/Views/concerts/*`、`src/Views/admin/*` |
| C | 訂票 / 付款 / 我的訂單（**專案核心**） | `src/Controllers/OrderController.php`、`src/Models/Order.php`、`src/Models/OrderItem.php`、`src/Views/orders/*` |

三人各自在新檔案上開發，路由要新增時各自到 `public/index.php` 加一行 `case` 即可。

## 共用約定（請大家遵守）

- **DB 查詢一律用 `db()->prepare()` + bind**，禁止字串拼 SQL（防 SQL injection）
- **view 內輸出變數一律用 `e($var)`**（防 XSS）
- **所有 POST 表單**要放 `<?= csrf_field() ?>`，controller 進入點要先 `csrf_check()`（防 CSRF）
- 需要登入的頁面：controller 第一行寫 `require_login();`
- 管理員頁面：controller 第一行寫 `require_admin();`
- 顯示訊息給使用者：用 `flash('success', '訊息')` 然後 `header('Location: /xxx')` 跳轉
