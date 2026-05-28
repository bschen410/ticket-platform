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
