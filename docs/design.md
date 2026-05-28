# 演唱會票務平台 — 期末專題規劃書

## Context

「動態網頁程式設計」課程期末專題，3 人組，預計 1–2 週內完成可繳交版本（今日 2026-05-27）。主題為**演唱會票務平台**，目的：展示動態網頁開發能力（資料庫驅動、表單處理、session、交易處理），同時呈現一個學生能完整 demo 的票務系統。

關鍵考量：
- 時程緊（1–2 週、3 人），必須嚴格 YAGNI，砍掉所有非核心功能。
- 老師需要看得到「學生自己寫的邏輯」，故選**原生 PHP**而非框架。
- 票務系統的技術亮點在於**搶票競態處理**（避免超賣），這是給老師的主要技術賣點。

---

## Scope

### MVP 必做（一定要 demo 出來）
1. **帳號**：註冊、登入、登出。`users` 表用 `role` 欄位區分 `customer` / `admin`。
2. **演唱會瀏覽**：首頁列出販售中演唱會、詳細頁顯示各區票價與剩餘數。
3. **訂票流程**：
   - 選擇區域與張數 → 建立 `pending` 訂單（10 分鐘倒數）→ 模擬付款（按鈕「確認付款」）→ 訂單變 `paid`，顯示電子票（含系統隨機配座結果，例如 `A 區 3 排 15 號`）。
   - 倒數結束未付款 → 訂單變 `expired`、釋出佔位庫存。
4. **我的訂單**：列出登入者所有訂單，按狀態分頁籤。
5. **管理員後台**：演唱會 CRUD、區域 / 票價 CRUD、訂單檢視（不能改）。

### 刻意不做（寫進報告的「未來工作」）
- 真實金流串接（綠界等）
- Email / 簡訊通知
- 找回密碼、社群登入
- 評價、推薦、購物車跨場次
- 二手轉讓、退票
- 全文搜尋
- 自動化測試（時程不允許，改用手動測試清單）

---

## 技術棧

| 層 | 選擇 |
|---|---|
| 後端 | 原生 PHP 8.x，PDO 連線 MySQL |
| 資料庫 | MySQL 8.x（InnoDB engine，需要 transaction + row lock） |
| Session | PHP 內建 session，登入後 `session_regenerate_id()` |
| Composer 套件 | 必要時引入（候選：phpdotenv 讀環境變數、TCPDF 出電子票 PDF — 可選） |
| 前端 | **延後決定**（建議 Bootstrap 5 + 少量原生 JS，CDN 引入即可） |
| 本機環境 | XAMPP / Laragon / Docker 任一即可（組員自選） |

---

## 架構總覽

採輕量 MVC 雛形（不引入框架，但目錄分層清楚）：

```
ticket-platform/
├── public/                  ← web root（Apache DocumentRoot 指這裡）
│   ├── index.php            ← 單一入口，依 URL 派發到 controller
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── .htaccess            ← URL rewrite 把所有請求送到 index.php
├── src/
│   ├── Controllers/         ← AuthController, ConcertController, OrderController, Admin/*
│   ├── Models/              ← User, Concert, Zone, Order, OrderItem（包成 PDO query class）
│   ├── Views/               ← .php 模板（純 PHP，不另引模板引擎）
│   │   ├── layouts/
│   │   ├── concerts/
│   │   ├── orders/
│   │   └── admin/
│   └── helpers/             ← db.php, auth.php, csrf.php, view.php, flash.php
├── config/
│   └── database.php         ← 從 .env 讀
├── sql/
│   ├── schema.sql           ← 建表 + 初始 admin 帳號
│   └── seed.sql             ← demo 用：3–5 場演唱會、各 3–4 個區域
├── .env.example
├── composer.json            ← 之後若需要再 init
└── README.md                ← 安裝、執行、demo 帳號說明
```

**路由策略**：`public/index.php` 一個簡單 switch 解析 `$_SERVER['REQUEST_URI']`。例：
```
GET  /                       → ConcertController::index
GET  /concerts/{id}          → ConcertController::show
POST /orders                 → OrderController::create
POST /orders/{id}/pay        → OrderController::pay
GET  /my/orders              → OrderController::mine
GET  /admin/concerts         → Admin\ConcertController::index
...
```

不引入第三方 router；自己寫 ~30 行就夠。

---

## 資料模型（5 張必要 + 1 張可選）

```sql
users        (id, email UNIQUE, password_hash, name, role ENUM('customer','admin'),
              created_at)

concerts     (id, title, venue, performed_at DATETIME, poster_url, description TEXT,
              sales_start_at DATETIME, sales_end_at DATETIME,
              status ENUM('draft','on_sale','closed'),
              created_at, updated_at)

zones        (id, concert_id FK, name, price DECIMAL(10,2),
              total_seats INT, sold_seats INT DEFAULT 0,
              INDEX (concert_id))

orders       (id, user_id FK, concert_id FK,
              status ENUM('pending','paid','expired','cancelled'),
              total_amount DECIMAL(10,2),
              expires_at DATETIME, paid_at DATETIME NULL,
              created_at,
              INDEX (user_id), INDEX (status, expires_at))

order_items  (id, order_id FK, zone_id FK,
              quantity INT, unit_price DECIMAL(10,2),
              seat_labels JSON  -- 例 ["A區3排15號","A區3排16號"]
              )

-- 可選（時程允許再做）
audit_log    (id, user_id, action, target_type, target_id, payload JSON, created_at)
```

**設計重點**：
- 庫存實際保存在 `zones.sold_seats`（已售張數），剩餘 = `total_seats - sold_seats`。**`sold_seats` 包含 pending 訂單佔位**，付款失敗 / 過期時要回扣。
- `seat_labels` 用 JSON 存陣列；既然是區域劃位 + 隨機配座，產生策略：在區內按「排號從小到大、座號從小到大」遞增分配，每個 zone 維護一個簡單的「下一個可發座位」邏輯（可加 `next_row`, `next_seat` 欄位簡化，或從 `sold_seats` 反推）。
- `orders.expires_at` 是過期判斷依據；訂單建立時 = `NOW() + INTERVAL 10 MINUTE`。

---

## 關鍵流程：建立訂單（搶票競態）

這是本專題技術核心，必須用 transaction + row lock：

```
BEGIN;
  -- 1. 鎖住該場該區的庫存列
  SELECT total_seats, sold_seats
    FROM zones
    WHERE id = :zone_id AND concert_id = :concert_id
    FOR UPDATE;

  -- 2. 順手清理過期 pending：把該場過期未付的訂單回扣
  --    （以下兩段在同一個 transaction 內完成，確保一致）
  -- 2a. 找出此場所有 status=pending 且 expires_at < NOW() 的訂單
  -- 2b. UPDATE 該些訂單 status='expired'
  -- 2c. 對應 order_items 內每筆，把 zones.sold_seats 扣回

  -- 3. 重新檢查剩餘（因為步驟 2 可能已回庫）
  -- 4. 若 sold_seats + 申請張數 > total_seats → ROLLBACK，回「售完」
  -- 5. UPDATE zones SET sold_seats = sold_seats + :qty WHERE id = :zone_id
  -- 6. INSERT orders (status='pending', expires_at=NOW()+INTERVAL 10 MINUTE, ...)
  -- 7. INSERT order_items（含分配好的 seat_labels）
COMMIT;
```

**過期清理策略**：採 **lazy expiration**（每次有人下單時順手清同場過期單），不另開背景排程。若課程要求展示 cron，可加一個 `php bin/expire.php` 腳本當作 bonus。

**模擬付款**：訂單詳情頁顯示倒數計時（JS `setInterval` + 後端 `expires_at`）。「確認付款」按鈕送 POST，後端檢查 `status='pending' AND expires_at > NOW()`，是則改 `paid` + 填 `paid_at`，否則回錯誤。

---

## 資安要點（報告必寫）

| 風險 | 對策 | 實作位置 |
|---|---|---|
| SQL Injection | 全部用 PDO prepared statement，禁止字串拼接 | `src/helpers/db.php` 包一個 `query($sql, $params)` |
| XSS | 所有輸出走 `htmlspecialchars($s, ENT_QUOTES, 'UTF-8')` | `src/helpers/view.php` 提供 `e($s)` |
| CSRF | 所有 POST 表單夾帶 token，session 比對 | `src/helpers/csrf.php` |
| 密碼明文 | `password_hash(PASSWORD_DEFAULT)` + `password_verify` | `AuthController` |
| Session hijack | 登入後 `session_regenerate_id(true)` | `AuthController::login` |
| 權限繞過 | 每個 `Admin\*` controller 進入點檢查 `role==='admin'` | `src/helpers/auth.php::require_admin()` |
| 超賣 | 上面 transaction + `FOR UPDATE` | `OrderController::create` |

---

## 需要改 / 新建的關鍵檔案

| 路徑 | 用途 |
|---|---|
| `sql/schema.sql` | 建表 SQL，含 admin 預設帳號 |
| `sql/seed.sql` | 3–5 場 demo 演唱會 |
| `public/index.php` | 入口路由 |
| `public/.htaccess` | URL rewrite |
| `src/helpers/db.php` | PDO 連線單例 + query helper |
| `src/helpers/auth.php` | `current_user()`, `require_login()`, `require_admin()` |
| `src/helpers/csrf.php` | `csrf_token()`, `csrf_check()` |
| `src/helpers/view.php` | `render($template, $data)`, `e($s)` |
| `src/Controllers/AuthController.php` | 註冊、登入、登出 |
| `src/Controllers/ConcertController.php` | 首頁、詳細頁 |
| `src/Controllers/OrderController.php` | 建單、付款、我的訂單 — **本專案核心** |
| `src/Controllers/Admin/ConcertController.php` | 後台演唱會 CRUD |
| `src/Controllers/Admin/OrderController.php` | 後台訂單檢視 |
| `src/Models/*.php` | 每張表一個檔，包成簡單 query class |
| `src/Views/layouts/main.php` | 共用 header / footer |
| `src/Views/concerts/`、`orders/`、`admin/` | 對應頁面模板 |

---

## 驗證計畫（手動測試清單）

**環境啟動**：
1. `mysql -u root -p < sql/schema.sql && mysql -u root -p ticket_platform < sql/seed.sql`
2. 設定 web server DocumentRoot 指向 `public/`
3. 瀏覽 `http://localhost/`

**功能驗證清單**：
- [ ] 註冊新帳號、登入、登出、再登入
- [ ] 未登入瀏覽首頁可看，但點「訂票」要求登入
- [ ] 列表顯示 demo 演唱會、點進詳細頁看到各區票價與剩餘
- [ ] 訂票：選區、選張數、送出 → 訂單頁顯示倒數 10:00
- [ ] 倒數中按「確認付款」→ 訂單變 paid、顯示電子票（含座位編號）
- [ ] 訂票後不付款，等 10 分鐘 → 重新整理變 expired、庫存回扣
- [ ] **超賣測試**：把某區 `total_seats` 改成 2，兩個瀏覽器分頁同時各訂 2 張 → 一個成功、一個應顯示「售完」
- [ ] 「我的訂單」分頁籤切換正確
- [ ] 管理員後台登入、新增演唱會、編輯、刪除、檢視訂單
- [ ] 非管理員直接打 `/admin/*` 應被擋下
- [ ] 隨便試一個 SQL injection payload（如 `' OR 1=1 --`）到登入表單 → 應失敗
- [ ] 沒帶 CSRF token 直接 curl POST 訂票 → 應失敗

**報告 demo 重點**：超賣測試（用兩個瀏覽器同時搶最後一張）+ 倒數過期回庫，最能體現技術深度。

---

## 風險與後備方案

| 風險 | 後備 |
|---|---|
| 1–2 週做不完管理員後台 | 砍掉訂單檢視畫面，只留演唱會 CRUD |
| `FOR UPDATE` 在 demo 機器跑不順 | 退一步用「樂觀鎖」：`UPDATE zones SET sold_seats = sold_seats + :qty WHERE id = :zone_id AND sold_seats + :qty <= total_seats`，再看 affected_rows |
| 座位編號分配邏輯太複雜 | 直接用流水號（`A 區 第 12 張`），不分排座 |
| 前端做不漂亮 | Bootstrap 5 套版即可，不要花時間刻 CSS |
