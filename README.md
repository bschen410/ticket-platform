# 演唱會票務平台

「動態網頁程式設計」期末專題 — 原生 PHP 8 + MySQL 8。

## 快速啟動

```bash
# 1. 建 DB 並匯入 schema / seed
mysql -u root -p -e "CREATE DATABASE ticket_platform CHARACTER SET utf8mb4;"
mysql -u root -p ticket_platform < sql/schema.sql
mysql -u root -p ticket_platform < sql/seed.sql

# 2. 設定環境變數
cp .env.example .env
# 編輯 .env 填入 DB 密碼

# 3. 建 admin 帳號（一次性）
php sql/init_admin.php

# 4. 起服務
php -S localhost:8000 -t public
```

瀏覽 `http://localhost:8000/`。

## Demo 帳號

| 角色 | Email | 密碼 |
|---|---|---|
| 管理員 | admin@example.com | admin1234 |
| 一般使用者 | 自行註冊 | — |

## 文件

- [docs/design.md](docs/design.md) — 整體設計與分工
- [docs/0-step.md](docs/0-step.md) — 共用骨架說明（本次第零步成果）

## 技術棧

- PHP 8.x（原生，無框架）
- MySQL 8.x（InnoDB，使用 transaction + row lock 處理搶票）
- Bootstrap 5（CDN）
