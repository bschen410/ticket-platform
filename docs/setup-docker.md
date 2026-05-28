# Docker 啟動指南（推薦：跨平台一鍵跑）

Fedora、Windows、macOS 用 Docker Compose 跑法完全一樣，schema 跟 seed 也會自動匯入，**比 XAMPP 更不容易踩雷**。

> Podman 使用者直接把所有 `docker` 換成 `podman` 即可（細節見 [§ 用 Podman 取代 Docker](#用-podman-取代-docker)）。

---

## 1. 裝 Docker

### Fedora

```bash
sudo dnf install -y moby-engine docker-compose
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
# 登出再登入，讓 docker group 生效
```

驗證：
```bash
docker run --rm hello-world
```

### Windows

裝 [Docker Desktop](https://www.docker.com/products/docker-desktop/)。安裝完開起來、確認左下角顯示 `Engine running`。

> 註：Docker Desktop 需要 Windows 10/11 + WSL2，現代電腦都符合。第一次裝完可能要重開機。

---

## 2. 起服務

在專案根目錄：

```bash
docker compose up -d
```

第一次會下載 `mysql:8.0`、`php:8.2-cli` 並 build 自家 image，大約 1–3 分鐘。

確認狀態：
```bash
docker compose ps
```

兩個服務都應該是 `running`、`db` 是 `healthy`。

---

## 3. 建 admin 帳號（一次性）

```bash
docker compose exec app php sql/init_admin.php
```

成功會印：
```
已建立 admin 帳號：
  Email: admin@example.com
  Password: admin1234
```

---

## 4. 開瀏覽器

`http://localhost:8000/`

應看到「Hello, ticket platform 🎟️」+ DB connected + 3 場演唱會。

---

## 日常指令

| 指令 | 用途 |
|---|---|
| `docker compose up -d` | 啟動（背景） |
| `docker compose logs -f app` | 看 PHP 的 stdout / error |
| `docker compose exec app sh` | 進 app 容器內操作 |
| `docker compose exec db mysql -uroot -psecret ticket_platform` | 進 MySQL CLI |
| `docker compose restart app` | 改了 PHP 設定後重啟 app |
| `docker compose down` | 停止並移除容器（保留資料） |
| `docker compose down -v` | 連同資料庫 volume 一起清掉（重置） |

---

## 想重新匯入 schema / seed 怎麼辦？

MySQL container 只有**第一次啟動**時才會跑 `/docker-entrypoint-initdb.d/*.sql`。之後要重匯：

**選項 1：整個重置**（資料全清）
```bash
docker compose down -v
docker compose up -d
docker compose exec app php sql/init_admin.php
```

**選項 2：只重匯 SQL**
```bash
docker compose exec -T db mysql -uroot -psecret ticket_platform < sql/schema.sql
docker compose exec -T db mysql -uroot -psecret ticket_platform < sql/seed.sql
docker compose exec app php sql/init_admin.php
```

---

## 我想用 phpMyAdmin 看 DB 怎麼辦？

兩個選項：

**選項 1**：在 `docker-compose.yml` 加 phpMyAdmin 服務：
```yaml
  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: ticket-pma
    environment:
      PMA_HOST: db
      PMA_USER: root
      PMA_PASSWORD: secret
    ports:
      - "8081:80"
    depends_on:
      - db
```
之後 `docker compose up -d`，開 `http://localhost:8081/`。

**選項 2**：用本機的 MySQL Workbench / DBeaver / TablePlus 連 `127.0.0.1:3307`、root / secret。

---

## 用 Podman 取代 Docker

Podman 跟 Docker CLI 幾乎完全相容，**`docker-compose.yml` 不用改任何一行**（我們已預先在 volume 掛載加上 `:Z` 後綴，docker 會忽略它、podman 在 SELinux 系統上需要它）。

### Fedora 安裝

```bash
sudo dnf install -y podman podman-compose
```

### 指令對照

| Docker | Podman |
|---|---|
| `docker compose up -d` | `podman compose up -d`（或 `podman-compose up -d`） |
| `docker compose exec app php sql/init_admin.php` | `podman compose exec app php sql/init_admin.php` |
| `docker compose logs -f app` | `podman compose logs -f app` |
| `docker compose down` | `podman compose down` |
| `docker compose down -v` | `podman compose down -v` |

### 為什麼 volume 要加 `:Z`

Fedora 預設啟用 SELinux，bind mount 進容器的目錄如果沒重貼標籤，容器內 process 會 `Permission denied`。`:Z` 表示「重貼成私有標籤給這個容器用」。Docker（在非 SELinux 系統上）會忽略這個 flag，所以加了不會壞事，跨平台都能跑。

---

## 常見問題

| 症狀 | 原因 / 解法 |
|---|---|
| `docker: command not found`（Fedora） | 沒裝或沒加 PATH，回步驟 1 |
| `permission denied`（Fedora） | 還沒加入 `docker` group 或沒重新登入 |
| port 8000 已被佔 | 改 `docker-compose.yml` 的 `8000:8000` 成 `8001:8000` |
| port 3307 已被佔 | 改 `docker-compose.yml` 的 `3307:3306` 成別的 port |
| `connection refused` 連 DB | `db` 還沒 ready，等 5 秒再 reload，或看 `docker compose logs db` |
| 改了 `sql/schema.sql` 沒生效 | MySQL 只在第一次匯入；用上面「重新匯入」步驟 |
| Windows 上跑超慢 | Docker Desktop 預設 WSL2，把專案放在 WSL 的檔案系統（`\\wsl$\...`）會比放在 `C:\` 快很多 |
| Podman: `Permission denied` 讀檔 | `:Z` 沒生效或 SELinux 太嚴；確認 `docker-compose.yml` 的 volume 都帶 `:Z`，或暫時 `sudo setenforce 0` 排除 SELinux 嫌疑 |
