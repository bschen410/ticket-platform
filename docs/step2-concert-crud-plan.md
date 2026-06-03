# 演唱會瀏覽 + 後台 CRUD 實作計畫（Step 2 — B）

## Context

期末專題票務平台目前已完成 Step 0（共用骨架）與 Step 1（Auth 登入/註冊/登出）。
本計畫實作 **Step 2 中 B 負責的部分**：讓使用者能在首頁瀏覽販售中演唱會、點進詳細頁看各區票價與剩餘張數；讓管理員能在後台對演唱會與區域做 CRUD。

設計與資料模型已凍結於 `docs/design.md`，schema（`concerts`、`zones`…）與 seed（3 場 × 3 區）皆已就緒。本計畫只新增 Model / Controller / View 與路由，**不改 schema**。

### 已確認決策
- **資料層採 Model class**：新建 `src/Models/Concert.php`、`src/Models/Zone.php`，以靜態查詢方法封裝（符合 `docs/design.md` 原始規劃）。Controller 保持精簡。
- **首頁 `/` 改為演唱會列表**：指向 `ConcertController::index`，移除舊的 `HomeController` 系統檢查頁。
- **訂票表單延後**：詳細頁本次只「顯示」各區票價/剩餘並預留訂票表單位置；真正的訂票 POST 需要 `OrderController`（C 的 Step 3，尚不存在），故不在本次範圍。

### 沿用慣例（Step 0）
- 查詢一律用 `query($sql, $params)`（`src/helpers/db.php`），禁止字串拼 SQL。
- View 輸出一律 `e($var)`；版面用 `render($template, $data)` 包 `layouts/main`。
- POST 表單夾 `csrf_field()`，controller 開頭 `csrf_check()`。
- 管理員頁 controller 第一行 `require_admin()`。
- 訊息用 `flash('success'|'error', '訊息')` + `header('Location: ...')` 重導。

---

## 範圍

| 項目 | 產出 |
|---|---|
| 演唱會瀏覽 | `ConcertController`、`concerts/index.php`、`concerts/show.php` |
| 後台演唱會 CRUD | `Admin\ConcertController`、`admin/concerts/*` |
| 後台區域 CRUD | `Admin\ZoneController`（區域在演唱會編輯頁以 sub-form 管理） |
| Model 層 | `Concert`、`Zone` |
| 路由 + 小幅調整 | `public/index.php`、`require_admin()` |

---

## 1. Models 層（新建 `src/Models/`）

Model 為純靜態方法類別，內部呼叫全域 `query()` / `db()` helper。autoloader 已支援 `src/Models/`（見 `public/index.php` 的 `spl_autoload_register`）。

### `src/Models/Concert.php`
```php
Concert::onSaleList(): array            // 首頁：status='on_sale'，依 performed_at 排序
Concert::findWithZones(int $id): ?array // 詳細頁：concert + zones[]，每個 zone 附 remaining
Concert::all(): array                   // 後台列表：所有 status
Concert::find(int $id): ?array          // 編輯表單用
Concert::create(array $data): int
Concert::update(int $id, array $data): bool
Concert::delete(int $id): bool          // hard delete；FK CASCADE 連帶刪 zones
```
- `findWithZones`：先查 concert，再 `Zone::findByConcert($id)`，組成 `$concert['zones']`，每筆加 `remaining = total_seats - sold_seats`。
- `delete`：因 `orders.concert_id` FK 無 CASCADE，若該場已有訂單會丟 PDOException；由 controller 攔截並 flash 友善訊息（demo 場景可接受只刪無訂單的場次）。

### `src/Models/Zone.php`
```php
Zone::findByConcert(int $concertId): array  // 每筆附 remaining
Zone::find(int $id): ?array
Zone::create(int $concertId, array $data): int
Zone::update(int $id, array $data): bool
Zone::delete(int $id): bool                 // 已售出（被 order_items 參照）時會失敗 → controller 攔截 flash
```
> 註：Step 3 訂票交易要用的 `findForUpdate` / `incrementSold` / `decrementSold` **不在本次範圍**，留給 C 在 Step 3 補（避免本份 PR 摻入未驗證的交易邏輯）。

---

## 2. Controllers

### `src/Controllers/ConcertController.php`（公開）
- `index()`：`Concert::onSaleList()` → `render('concerts/index', [...])`。
- `show(int $id)`：`Concert::findWithZones($id)`，null → 404（`http_response_code(404); render('errors/404')`）；否則 `render('concerts/show', [...])`。

### `src/Controllers/Admin/ConcertController.php`（`namespace Admin;`）
每個 action 第一行 `require_admin()`；寫入型 action 先 `csrf_check()`。
- `index()` → `Concert::all()` → `admin/concerts/index`
- `create()` → 顯示新增表單 `admin/concerts/create`
- `store()` → 驗證 → `Concert::create` → flash + 重導 `/admin/concerts`
- `edit(int $id)` → concert + zones → `admin/concerts/edit`（含區域 sub-form）
- `update(int $id)` → 驗證 → `Concert::update` → flash + 重導回 edit
- `destroy(int $id)` → try `Concert::delete`；PDOException → flash error「此演唱會已有訂單，無法刪除」

驗證：仿 `auth.php` 的 `validate_*` 風格，於 controller 內 private method `validate(array $input): array`（title/venue/performed_at 必填、price/seats 為正、sales_start < sales_end、status 在 enum 內）。
`datetime-local` 輸入（`2026-07-20T19:30`）轉成 DB 格式（`2026-07-20 19:30:00`）後再存。

### `src/Controllers/Admin/ZoneController.php`（`namespace Admin;`）
區域不另開列表頁，操作後一律重導回 `/admin/concerts/{concertId}/edit`。
- `store(int $concertId)` → csrf + 驗證 → `Zone::create` → 重導
- `update(int $id)` → csrf + 驗證 → `Zone::update` → 重導
- `destroy(int $id)` → csrf → try `Zone::delete`；失敗 flash「此區域已有售票，無法刪除」

---

## 3. Views

沿用 `layouts/main.php`（Bootstrap 5 CDN）。

- `concerts/index.php`：卡片格狀列表（標題、場館、`performed_at`、「查看詳情」連結 `/concerts/{id}`）。seed 無 `poster_url`，缺圖時用色塊/預設佔位。
- `concerts/show.php`：演唱會資訊 + 區域表格（區名、票價、剩餘 = `total_seats - sold_seats`）。
  **預留訂票表單位置**：以註解標出 Step 3 將插入 `<form action="/orders">`；本次顯示「登入後即可訂票」或停用按鈕，不接 POST。
- `admin/concerts/index.php`：所有演唱會表格 + 編輯/刪除（刪除用 POST + `csrf_field()`）+「新增演唱會」按鈕。
- `admin/concerts/create.php` / `edit.php`：演唱會欄位表單。可抽共用欄位到 `admin/concerts/_form.php`，由 create/edit 內 `require` 共用（避免重複）。
- `edit.php` 底部：區域 sub-form 區塊 — 列出現有區域（各一列含 inline 編輯/刪除小表單）+ 一列「新增區域」。

---

## 4. 路由（`public/index.php`）

router 僅支援 GET/POST、`{id}` 只配數字。新增/調整 `$routes`：

```php
// 演唱會瀏覽（B）— 首頁改指向 ConcertController
['GET',  '/',                              [ConcertController::class, 'index']],
['GET',  '/concerts/{id}',                 [ConcertController::class, 'show']],

// 後台演唱會 CRUD（B）
['GET',  '/admin/concerts',                [Admin\ConcertController::class, 'index']],
['GET',  '/admin/concerts/new',            [Admin\ConcertController::class, 'create']],
['POST', '/admin/concerts',                [Admin\ConcertController::class, 'store']],
['GET',  '/admin/concerts/{id}/edit',      [Admin\ConcertController::class, 'edit']],
['POST', '/admin/concerts/{id}',           [Admin\ConcertController::class, 'update']],
['POST', '/admin/concerts/{id}/delete',    [Admin\ConcertController::class, 'destroy']],

// 後台區域 CRUD（B）
['POST', '/admin/concerts/{id}/zones',     [Admin\ZoneController::class, 'store']],
['POST', '/admin/zones/{id}',              [Admin\ZoneController::class, 'update']],
['POST', '/admin/zones/{id}/delete',       [Admin\ZoneController::class, 'destroy']],
```
- 順序：`/admin/concerts/new`（GET）與 `/admin/concerts/{id}/edit`（GET）不衝突（`new` 非數字）。
- `Admin\ConcertController::class` 在全域命名空間解析為字串 `"Admin\ConcertController"`，autoloader 映射到 `src/Controllers/Admin/ConcertController.php`。

---

## 5. 小幅調整 `require_admin()`（`src/helpers/auth.php`）

目前 `require_admin()` 對非管理員是 `http_response_code(403); echo` 硬擋；但 roadmap 驗收要求「302 跳轉到首頁 + flash『無權限』」。改為：
```php
flash('error', '無權限存取');
header('Location: /');
exit;
```
（仍保留 `require_login()` 先擋未登入者導向 `/login`。）

---

## 6. 清理

- 移除 `src/Controllers/HomeController.php` 與 `src/Views/home/index.php`（首頁已由 ConcertController 接手）。
- `public/index.php` 移除 HomeController 的 `use`/route 與相關註解。

---

## 驗證（對應 roadmap Step 2 整合驗收）

啟動：
```bash
docker compose up -d
docker compose exec app php sql/init_admin.php   # 建 admin 帳號
# 瀏覽 http://localhost:8000/
```

手動清單：
- [ ] 首頁 `/` 看到 3 場 seed 演唱會卡片。
- [ ] 點進詳細頁看到各區票價與剩餘張數（= `total_seats - sold_seats`）。
- [ ] 用 `admin@example.com` 登入 → 右上「後台」→ `/admin/concerts`。
- [ ] 後台新增一場「測試演唱會」+ 一個區域 → 出現在首頁。
- [ ] 編輯該場 → 改標題/票價 → 詳細頁更新。
- [ ] 刪除測試演唱會 → 不再出現在首頁與後台列表。
- [ ] 嘗試刪除已有訂單的場次 → 看到友善 flash（非白頁錯誤）。
- [ ] 非 admin 登入後直接打 `/admin/concerts` → 重導首頁 + flash「無權限存取」。
- [ ] 未登入打 `/admin/concerts` → 導向 `/login`。
- [ ] 頁面無 PHP warning/notice。
