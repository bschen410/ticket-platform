# 開發路線圖（Step 1 ~ Step 5）

> 草稿。Ultraplan 雲端精修中斷，先暫存本地以免遺失；正式核可後可能再修。

## Context

第零步（共用骨架）已完成並 commit 至 `main`（`b196ad0`、`28066b0`、`a3816a7`）。三人分工已在 [`docs/0-step.md`](0-step.md) 列出。
今日 **2026-05-29**，期末繳交 **2026-06-12**，共 **14 天**，三人團隊。需要保留週末 1–2 天寫報告 / demo 腳本。

本路線圖把剩下的工作切成 5 個 Step，**每個 Step 結束都做一次整合**。每個 Step 同時列出三人各自的任務、產出檔案、驗收標準。

整體設計與資料模型已凍結，見 [`docs/design.md`](design.md)。本路線圖只規劃**順序與里程碑**，不重複設計細節。

---

## 時程總覽

| Step | 日期                                 | 主題                                | 主負責 | 整合點                         |
| ---- | ------------------------------------ | ----------------------------------- | ------ | ------------------------------ |
| 0    | ✅ done                              | 共用骨架                            | 全員   | —                              |
| 1    | 05-30 ~ 06-01（3 天）                | Models 層 + Auth 上線               | A 主導 | 06-01 晚：能登入登出           |
| 2    | 06-02 ~ 06-05（4 天）                | 演唱會瀏覽 + 後台 CRUD              | B 主導 | 06-05 晚：能 demo 場次列表     |
| 3    | 06-03 ~ 06-08（與 Step 2 重疊 6 天） | 訂票 / 付款 / 我的訂單 **（核心）** | C 主導 | 06-08 晚：能完整跑訂單流程     |
| 4    | 06-09 ~ 06-10（2 天）                | 整合測試 + bug fix + 樣式收尾       | 全員   | 06-10 晚：所有手動測試清單綠燈 |
| 5    | 06-11 ~ 06-12（2 天）                | 報告 / 簡報 / demo 腳本             | 全員   | 06-12：繳交                    |

> Step 2 與 Step 3 刻意重疊：B 做完 ConcertController/Zone 模型後 C 才能接 Order；但 C 可在 Step 2 早期就先寫 Order/OrderItem 模型與訂單頁面骨架，等 Zone 模型可用再串。

---

## Step 1 — Models 層 + Auth（05-30 ~ 06-01）

**目標**：所有後續 controller 共用的 Model class 全部就位；A 完成 Auth，其他人可登入測試。

### 任務分工

| 人    | 任務                                                     | 產出檔案                                                                                                                                                    |
| ----- | -------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **A** | `User` model + 註冊、登入、登出、`session_regenerate_id` | `src/Models/User.php`、`src/Controllers/AuthController.php`、`src/Views/auth/login.php`、`src/Views/auth/register.php`、`public/index.php`（加 5 條 route） |
| **B** | `Concert`、`Zone` model（先做純查詢方法，不做頁面）      | `src/Models/Concert.php`、`src/Models/Zone.php`                                                                                                             |
| **C** | `Order`、`OrderItem` model 骨架 + 訂單狀態 enum 常數     | `src/Models/Order.php`、`src/Models/OrderItem.php`                                                                                                          |

### Model 介面（共同約定，避免互相打架）

```php
// User
User::findByEmail(string $email): ?array
User::create(string $email, string $hash, string $name, string $role='customer'): int

// Concert
Concert::onSaleList(): array          // 首頁用
Concert::findWithZones(int $id): ?array  // 詳細頁用，含 zones[]
Concert::all(): array                 // 後台用

// Zone
Zone::findForUpdate(int $zoneId, int $concertId): ?array  // FOR UPDATE 鎖列
Zone::incrementSold(int $zoneId, int $qty): bool
Zone::decrementSold(int $zoneId, int $qty): bool

// Order
Order::create(int $userId, int $concertId, float $total): int
Order::findMineByStatus(int $userId, string $status): array
Order::expirePending(int $concertId): array  // 回傳被 expired 的 order id

// OrderItem
OrderItem::create(int $orderId, int $zoneId, int $qty, float $unitPrice, array $seatLabels): int
OrderItem::findByOrder(int $orderId): array
```

### 整合驗收（06-01 晚）

- [ ] `composer dump-autoload` 不需要（spl_autoload_register 已能找到 `src/Models/*`）
- [ ] 從 `/register` 註冊 → `/login` 登入 → 右上角顯示 user name → `/logout` 登出
- [ ] B/C 在自己的 controller 中 `var_dump(User::findByEmail($email))` 能拿到資料
- [ ] 用 admin@example.com / admin1234 登入 → 右上角有「後台」連結（即使後台還是 404）

---

## Step 2 — 演唱會瀏覽 + 管理員後台 CRUD（06-02 ~ 06-05）

**目標**：使用者能在首頁看演唱會列表、點進詳細頁看各區票價剩餘；管理員能在後台新增/編輯/刪除演唱會與區域。

### 任務分工

| 人    | 任務                                                                                            | 產出檔案                                                                                                                                                                                                                           |
| ----- | ----------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **B** | 首頁列表、詳細頁、後台演唱會 CRUD、後台區域 CRUD                                                | `src/Controllers/ConcertController.php`、`src/Controllers/Admin/ConcertController.php`、`src/Controllers/Admin/ZoneController.php`、`src/Views/concerts/{index,show}.php`、`src/Views/admin/concerts/*`、`src/Views/admin/zones/*` |
| **A** | Auth 收尾：忘記密碼提示頁（純文字「請聯絡管理員」）、登入失敗錯誤訊息整理；協助寫 layout 內 nav | `src/Views/auth/*` 收尾                                                                                                                                                                                                            |
| **C** | 訂單流程開工：訂單頁面骨架（不串庫存）、付款頁 UI、`OrderController::mine` 先做出來             | `src/Controllers/OrderController.php`、`src/Views/orders/*`                                                                                                                                                                        |

### 後台 CRUD 範圍（刻意精簡）

- 演唱會：列表、新增、編輯、刪除（軟刪除可省略，直接 hard delete + FK CASCADE）
- 區域：在演唱會編輯頁底下用 sub-form 管理；不另開列表頁
- 訂單檢視：只列表，不能改（**Step 4 再做，若時間不夠可砍**）

### 整合驗收（06-05 晚）

- [ ] 首頁 `/` 看到 3 場 seed 演唱會
- [ ] 點進去看到各區票價、剩餘張數（=`total_seats - sold_seats`）
- [ ] admin 登入後台 `/admin/concerts`，能新增一場「測試演唱會」+ 一個區域，刪除後不再出現在首頁
- [ ] 非 admin 直接打 `/admin/concerts` → 302 跳轉到首頁 + flash「無權限」

---

## Step 3 — 訂票 / 付款 / 我的訂單（06-03 ~ 06-08，核心）

**目標**：完整跑通搶票流程，含 transaction + `FOR UPDATE` 防超賣 + lazy expiration 回庫。

### 任務分工

| 人    | 任務                                                                                          | 產出檔案                                                                  |
| ----- | --------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------- |
| **C** | `OrderController::create`（核心 transaction）、`pay`、`show`、`mine`；座位分配邏輯            | `src/Controllers/OrderController.php`、`src/Views/orders/{show,mine}.php` |
| **C** | 訂單詳情頁倒數計時 JS（純前端 setInterval，後端傳 `expires_at`）                              | `public/assets/js/countdown.js`                                           |
| **B** | 詳細頁加「立即訂票」表單（選 zone + qty + CSRF token）                                        | `src/Views/concerts/show.php` 修改                                        |
| **A** | 把 `require_login()` 加在 OrderController 進入點 + 寫一個 `flash('warning', '請先登入')` 跳轉 | `src/helpers/auth.php` 微調                                               |

### 核心 transaction（C 寫，B/A code review）

照 `docs/design.md` § 關鍵流程：建立訂單 章節執行，重點：

1. `BEGIN`
2. `SELECT ... FOR UPDATE` 鎖 zone 列
3. `Order::expirePending($concertId)` → 對回傳的每筆 order，把 `order_items` 的 qty 加回 `zones.sold_seats`
4. 重新檢查剩餘 → 不夠則 `ROLLBACK` + flash「售完」
5. `Zone::incrementSold` → `Order::create` → `OrderItem::create`（含 seat_labels JSON）
6. `COMMIT`

座位分配：第一版用流水號（`A 區 第 N 張`），時間夠再改成 row/seat。設計文件已列為 fallback。

### 整合驗收（06-08 晚）

- [ ] 登入後從詳細頁訂 2 張 → 跳到訂單頁顯示倒數 10:00 + 兩個座位編號
- [ ] 按「確認付款」→ 訂單變 paid + 顯示電子票
- [ ] **超賣手動測試**：把某 zone `total_seats` 改成 2，兩個瀏覽器各訂 2 張 → 一個成功、一個顯示「售完」
- [ ] 訂單建立後不付款，把 `expires_at` 手動 UPDATE 成過去時間 → 下次有人訂同場 → 過期單變 expired、庫存回扣
- [ ] `/my/orders` 顯示登入者所有訂單，分 pending / paid / expired 三 tab

---

## Step 4 — 整合測試 + bug fix + 樣式收尾（06-09 ~ 06-10）

**目標**：跑完 `docs/design.md § 驗證計畫` 整份手動測試清單，全部綠燈。

### 任務分工（全員）

- 三人各跑一份手動測試清單，發現 bug 開 issue / 直接修
- 一人專心做後台訂單檢視（如果還沒做完且時間夠）
- 一人專心刻 Bootstrap 樣式：navbar、card、表單對齊（**不超過半天**，YAGNI）
- 一人專心寫資安測試（SQL injection payload、CSRF curl 攻擊、未授權 admin 存取）

### 整合驗收

- [ ] `docs/design.md` 全部 11 個手動測試項目綠燈
- [ ] README 的「快速啟動」三條路徑（Docker / XAMPP / 手動）至少由不同組員各驗證過一條
- [ ] 沒有 PHP warning / notice 噴在頁面上（`display_errors=Off` 不算數，要實際無 error）

---

## Step 5 — 報告 / 簡報 / demo 腳本（06-11 ~ 06-12）

**目標**：交得出去的書面報告 + 上台 demo 腳本。

### 任務分工（全員協作）

- **架構章節**：摘要 `docs/design.md`（MVC 雛形、單一入口路由、helpers 分層）
- **資料模型章節**：5 張表 schema 圖 + 關聯
- **資安章節**：直接用 `docs/design.md § 資安要點` 表格 + 每項貼一段對應 code
- **技術亮點章節**：搶票 transaction + `FOR UPDATE` 完整流程圖 + 超賣測試截圖
- **未來工作章節**：直接用 `docs/design.md § 刻意不做` 列表
- **demo 腳本**：開 3 個瀏覽器、admin 後台 + 兩個 customer 同時搶最後一張，5 分鐘 demo

### 繳交檢查（06-12 中午前）

- [ ] PDF 報告
- [ ] PPT 簡報
- [ ] GitHub repo public + README 完整
- [ ] demo 影片（備援，如果 demo 機壞掉）

---

## 共用約定（沿用 Step 0）

- DB 查詢一律 `db()->prepare()`，禁止字串拼 SQL
- View 輸出一律 `e($var)`
- POST 表單一律 `<?= csrf_field() ?>` + controller 開頭 `csrf_check()`
- 需要登入：controller 第一行 `require_login()`
- 管理員專用：controller 第一行 `require_admin()`
- 訊息給 user：`flash('success', '訊息')` + `header('Location: ...')`
- commit message：`feat:` / `fix:` / `docs:`，不要 Co-Authored-By
- 每完成一個 Step 開一個整合 commit 同步到 `main`，三人各自 feature branch → PR / merge

---

## 風險與後備（沿用 `docs/design.md`）

| 風險                        | 後備                                                                                |
| --------------------------- | ----------------------------------------------------------------------------------- |
| Step 3 transaction 寫不出來 | 退一步用樂觀鎖 `UPDATE ... WHERE sold_seats + :qty <= total_seats` 看 affected_rows |
| Step 2 後台 CRUD 來不及     | 砍訂單檢視，只留演唱會 CRUD（設計文件已預告）                                       |
| 整合時 schema 要改          | 改 `sql/schema.sql` + 跑 `docker compose down -v && up -d` 重灌                     |
| 整合時 route 衝突           | `public/index.php` 已有預留註解區塊，依 controller 分區追加                         |

---

## 驗證方式

每個 Step 結尾的「整合驗收」清單就是驗證；最終驗收以 `docs/design.md § 驗證計畫` 為準。

開發中本機驗證：

```bash
docker compose up -d
docker compose exec app php sql/init_admin.php
# 開 http://localhost:8000/
```

整合時三人輪流跑一遍 `docs/design.md § 驗證計畫` 的 11 個項目。
