# Step 3 — 訂票 / 付款 / 我的訂單（核心流程）

## Context

依 `docs/roadmap.md`，Step 0~2 已完成並併入 `main`：Auth（`feat/login`）、演唱會瀏覽 + 後台 CRUD（`feat/concert-crud`，PR #2 已合併）。`main` 現含 `Concert`/`Zone` model 與 `concerts/show.php`（已有帶 CSRF 的訂票表單 POST `/orders`）。

接下來是路線圖 **Step 3（核心）**：完整跑通搶票流程，含 transaction + `FOR UPDATE` 防超賣 + lazy expiration 回庫 + 付款 + 我的訂單。

現況落差：
- ❌ `Order`/`OrderItem` model 不存在（Step 1 原規劃的 C 骨架未交）
- ❌ `Zone` 缺 `findForUpdate` / `incrementSold` / `decrementSold`
- ❌ `OrderController`、`src/Views/orders/`、`countdown.js` 都不存在
- ✅ 路由已在 `public/index.php:46-49` 註解預留；helper（`auth`/`csrf`/`flash`/`view`/`db`）齊全；`show.php` 表單已就位

決策：從乾淨的 `main` 開 `feat/order-flow`，**完整 Step 3 一次做完**。座位分配採設計文件第一版的流水號（`A區 第N張`）。

## 開發流程（沿用團隊約定）

```bash
git checkout main && git pull
git checkout -b feat/order-flow
```
- DB 一律 `db()->prepare()` / `query()` 參數綁定；View 輸出 `e()`；POST 表單 `csrf_field()` + controller 開頭 `csrf_check()`；需登入 `require_login()`；訊息 `flash()` + `header('Location: ...')`。
- commit message：`feat:` / `fix:`，不要 Co-Authored-By。完工開 PR → 合併 `main`。

## 實作項目

### 1. `src/Models/Zone.php`（新增三方法，沿用現有靜態風格）
- `findForUpdate(int $zoneId, int $concertId): ?array` — `SELECT id, total_seats, sold_seats FROM zones WHERE id=? AND concert_id=? FOR UPDATE`（**僅能在 transaction 內呼叫**）。
- `incrementSold(int $zoneId, int $qty): bool` — `UPDATE zones SET sold_seats = sold_seats + ? WHERE id=?`。
- `decrementSold(int $zoneId, int $qty): bool` — `UPDATE ... sold_seats - ? ...`（回庫用，加 `GREATEST(...,0)` 保險）。

### 2. `src/Models/Order.php`（新建）
- `create(int $userId, int $concertId, float $total): int` — `INSERT INTO orders (user_id, concert_id, status, total_amount, expires_at, created_at) VALUES (?,?, 'pending', ?, NOW() + INTERVAL 10 MINUTE, NOW())`，回傳 `lastInsertId()`。
- `find(int $id): ?array`、`findWithDetails(int $id): ?array`（join concert 標題、附 `items`）。
- `findMineByStatus(int $userId, string $status): array`（`mine` 三 tab 用）。
- `markPaid(int $id): void` — `UPDATE orders SET status='paid', paid_at=NOW() WHERE id=? AND status='pending'`。
- `expirePending(int $concertId): array` — 先 `SELECT id FROM orders WHERE concert_id=? AND status='pending' AND expires_at < NOW()`，再 `UPDATE ... SET status='expired' WHERE ...`，回傳被 expired 的 order id 陣列（回庫由 controller 用 `OrderItem::findByOrder` + `Zone::decrementSold` 執行，符合路線圖介面）。

### 3. `src/Models/OrderItem.php`（新建）
- `create(int $orderId, int $zoneId, int $qty, float $unitPrice, array $seatLabels): int` — `seat_labels` 存 `json_encode($seatLabels, JSON_UNESCAPED_UNICODE)`。
- `findByOrder(int $orderId): array` — 取出後 `json_decode` `seat_labels`，並 join `zones.name`。

### 4. `src/Controllers/OrderController.php`（新建，4 method）
- **`create()`** — 核心 transaction（依 `docs/design.md §關鍵流程`）：
  1. `require_login()`；`csrf_check()`；讀 `concert_id`/`zone_id`/`qty`，驗 `qty >= 1`。
  2. `$pdo = db(); $pdo->beginTransaction();` 包 try/catch。
  3. `Zone::findForUpdate($zoneId, $concertId)`（鎖列）；找不到 → rollback + flash「資料錯誤」+ 導回。
  4. lazy expiration：`Order::expirePending($concertId)`，對每筆回傳 order，`OrderItem::findByOrder` → 逐筆 `Zone::decrementSold`。
  5. 重新讀該 zone（`findForUpdate` 再查一次或重查 sold_seats），算 `remaining = total_seats - sold_seats`；不足 → rollback + flash「售完」+ 導回詳細頁。
  6. 產生座位：`$base = sold_seats`（回庫後、增量前）；`seatLabels = ["{name} 第".($base+$i)."張"]`，i=1..qty。
  7. `Zone::incrementSold` → `Order::create($userId, $concertId, $price*$qty)` → `OrderItem::create(...)`。
  8. `commit()`；`header('Location: /orders/'.$orderId)`。catch → `rollBack()` + 重拋/錯誤頁。
- **`show(int $id)`** — `require_login()`；載入訂單；**越權檢查** `order['user_id'] === current_user()['id']`，否則 `abort_404()`；傳 `expires_at`、items、status 給 view（pending 顯示倒數 + 付款鈕；paid 顯示電子票）。
- **`pay(int $id)`** — `require_login()` + `csrf_check()`；越權檢查；驗 `status==='pending'` 且 `expires_at > NOW()`，否則 flash「訂單已過期」導回；`Order::markPaid($id)`；導回 `show`。
- **`mine()`** — `require_login()`；用 `Order::findMineByStatus(uid, ...)` 取 pending/paid/expired 三組傳給 view。

### 5. Views
- `src/Views/orders/show.php` — 訂單摘要、座位編號（`e()`）、金額；pending 時顯示 `#countdown` 區塊 + 「確認付款」表單（`csrf_field()`，POST `/orders/{id}/pay`）；paid 顯示電子票；引入 `countdown.js` 並用 `data-expires` 傳 `expires_at`。
- `src/Views/orders/mine.php` — Bootstrap nav-tabs：待付款 / 已付款 / 已過期，各列訂單卡片連到 `show`。

### 6. `public/assets/js/countdown.js`（新建）
- 純前端 `setInterval`，讀 `#countdown[data-expires]`（後端傳 ISO/timestamp），每秒更新 `MM:SS`；歸零時停掉計時並停用付款鈕、提示「已過期，請重新訂票」。

### 7. `public/index.php`
- 取消註解第 46-49 行四條 order 路由（`create`/`show`/`pay`/`mine`）。autoload 已支援 `Controllers/` 與 `Models/`，無需改。

## 已知簡化（MVP，可在報告「未來工作」註明）
- lazy expiration 回庫會 `decrementSold` 同場其他 zone（未在本次 `FOR UPDATE` 鎖定），school 專案可接受；高併發下應改為鎖該場全部 zone 或用樂觀鎖。
- 座位用流水號非實際排號；設計文件已列為 fallback。

## 驗證（對應 roadmap Step 3 整合驗收）

```bash
docker compose up -d
docker compose exec app php sql/init_admin.php
# 開 http://localhost:8000/
```
手動清單：
- [ ] 登入後從詳細頁訂 2 張 → 跳 `/orders/{id}` 顯示倒數 10:00 + 兩個座位編號
- [ ] 按「確認付款」→ 訂單變 paid + 顯示電子票
- [ ] **超賣測試**：某 zone `total_seats` 改成 2，兩瀏覽器各訂 2 張 → 一成功一「售完」
- [ ] 建單不付款，手動把 `expires_at` UPDATE 成過去 → 再有人訂同場 → 過期單變 expired、`sold_seats` 回扣
- [ ] `/my/orders` 顯示登入者所有訂單，分 pending/paid/expired 三 tab
- [ ] 非本人直接打 `/orders/{別人的id}` → 404（越權防護）
