# 演唱會票務平台

「動態網頁程式設計」期末專題 — 原生 PHP 8 + MySQL 8。

## 快速啟動

選一種：

- **Docker（推薦，跨平台一鍵）** → [docs/setup-docker.md](docs/setup-docker.md)
  ```bash
  docker compose up -d
  docker compose exec app php sql/init_admin.php
  # 開 http://localhost:8000/
  ```
- **XAMPP（Windows 同學熟悉的話）** → [docs/setup-xampp.md](docs/setup-xampp.md)
- **手動裝 PHP + MySQL**：依下方指令
  ```bash
  mysql -u root -p -e "CREATE DATABASE ticket_platform CHARACTER SET utf8mb4;"
  mysql -u root -p ticket_platform < sql/schema.sql
  mysql -u root -p ticket_platform < sql/seed.sql
  cp .env.example .env             # 編輯 .env 填 DB 密碼
  php sql/init_admin.php
  php -S localhost:8000 -t public
  # 開 http://localhost:8000/
  ```

## Demo 帳號

| 角色 | Email | 密碼 |
|---|---|---|
| 管理員 | admin@example.com | admin1234 |
| 一般使用者 | 自行註冊 | — |

## 文件

- [docs/design.md](docs/design.md) — 整體設計與分工
- [docs/0-step.md](docs/0-step.md) — 共用骨架說明（本次第零步成果）
- [docs/0-step-explained.md](docs/0-step-explained.md) — 第零步每個檔案的詳細說明
- [docs/setup-docker.md](docs/setup-docker.md) — 用 Docker 啟動的步驟
- [docs/setup-xampp.md](docs/setup-xampp.md) — 用 XAMPP 啟動的步驟

## 技術棧

- PHP 8.x（原生，無框架）
- MySQL 8.x（InnoDB，使用 transaction + row lock 處理搶票）
- Bootstrap 5（CDN）

---

## 開發流程

### 分支策略

`main` 為穩定主線，**不直接 push**，所有變更都經由 Pull Request 合入。

#### 分支命名

```
<type>/<short-description>
```

| type | 用途 |
|---|---|
| `feat` | 新功能 |
| `fix` | 修 bug |
| `refactor` | 重構（不改行為） |
| `docs` | 文件 / 註解 |
| `style` | 排版、命名等不影響邏輯的調整 |
| `chore` | 設定檔、依賴、CI 等雜務 |

範例：
```
feat/order-transaction
fix/csrf-token-mismatch
docs/setup-xampp
refactor/auth-helper
```

> `short-description` 全小寫、單字以 `-` 分隔、簡短說明做什麼事。

---

### Conventional Commits

每一個 commit message 格式：

```
<type>(<scope>): <subject>

[optional body]
```

- **type**：同上方分支類型表
- **scope**（選填）：影響範圍，如 `auth`、`order`、`admin`、`db`
- **subject**：用現在式動詞開頭（英文）或直述（中文），50 字以內

**範例：**

```
feat(order): add FOR UPDATE lock to prevent overselling
fix(auth): redirect to login when session expires
docs: add Docker setup guide
refactor(concert): extract Zone query to dedicated method
chore: update .env.example with DB_PORT
```

**Breaking change** 在 body 加 `BREAKING CHANGE:` 說明。

---

### 開發工作流程

```
1. 從 main 建立 feature branch
   git switch -c feat/my-feature main

2. 開發、測試，隨時 commit（小步提交）

3. 推到遠端
   git push -u origin feat/my-feature

4. 在 GitHub 開 Pull Request → 指定至少一人 review

5. 通過 review 後 Squash and Merge 到 main

6. 刪除已合入的分支
```

#### PR 規範

- 標題與 commit message 同格式：`feat(order): add payment flow`
- Description 說明「做了什麼」、「為什麼這樣做」、以及「如何測試」
- 每個 PR 只做一件事，避免把不相關的改動混在一起
- 合 PR 前確認本機可正常啟動（`docker compose up -d` 不報錯）

#### Code Review 重點

- SQL 查詢有使用 `prepare()` + 參數綁定（禁止字串拼接）
- View 輸出有 `e()` 跳脫
- POST 表單有 `csrf_field()` + controller 有 `csrf_check()`
- 需要登入的頁面第一行有 `require_login()`
- 後台頁面第一行有 `require_admin()`

---

### 編碼共同約定

| 事項 | 規定 |
|---|---|
| DB 查詢 | 一律 `db()->prepare()`，禁止字串拼 SQL |
| View 輸出 | 一律 `e($var)` |
| CSRF | 表單 `<?= csrf_field() ?>`，controller 開頭 `csrf_check()` |
| 登入守衛 | controller 第一行 `require_login()` |
| 管理員守衛 | controller 第一行 `require_admin()` |
| Flash 訊息 | `flash('success'/'error'/'warning', '訊息')` + `header('Location: ...')` |
| `.env` | 機密資訊放 `.env`（不進版控），參考 `.env.example` |
