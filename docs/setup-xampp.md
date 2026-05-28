# XAMPP 啟動指南

XAMPP 有兩種跑法，推薦先用「方法 A」（最快、不用改 Apache 設定）。

---

## 方法 A：用 XAMPP 的 PHP + MySQL，但用 PHP 內建 server 跑網站（推薦）

XAMPP 只開 MySQL，網站用 `php -S` 跑。不用碰 Apache 設定，**5 分鐘就能動**。

### 1. 開 XAMPP，啟動 MySQL

XAMPP Control Panel → 點 **MySQL** 旁的 **Start**（不用啟動 Apache）。

### 2. 建立資料庫並匯入 schema 跟 seed

用 **phpMyAdmin**（瀏覽器開 `http://localhost/phpmyadmin/`，**這個會需要 Apache**；如不想啟 Apache 改走命令列）：

1. 點左上「新增」→ 資料庫名稱填 `ticket_platform`、編碼選 `utf8mb4_unicode_ci` → 建立
2. 點剛建好的 `ticket_platform` → 上方「匯入」分頁 → 選檔案 `sql/schema.sql` → 執行
3. 同樣方式再匯入 `sql/seed.sql`

或用命令列（Windows PowerShell 在專案根目錄）：

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE ticket_platform CHARACTER SET utf8mb4"
& "C:\xampp\mysql\bin\mysql.exe" -u root ticket_platform < sql\schema.sql
& "C:\xampp\mysql\bin\mysql.exe" -u root ticket_platform < sql\seed.sql
```

XAMPP 預設 root 沒密碼，按 Enter 跳過。

### 3. 設定 `.env`

在專案根目錄：

```powershell
copy .env.example .env
```

編輯 `.env`，XAMPP 預設值：

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=ticket_platform
DB_USER=root
DB_PASS=
```

（XAMPP root 預設沒密碼，`DB_PASS=` 留空就好。）

### 4. 建 admin 帳號

```powershell
& "C:\xampp\php\php.exe" sql\init_admin.php
```

成功會印：

```
已建立 admin 帳號：
  Email: admin@example.com
  Password: admin1234
```

### 5. 起網站

```powershell
& "C:\xampp\php\php.exe" -S localhost:8000 -t public
```

瀏覽 `http://localhost:8000/`，應看到「Hello, ticket platform 🎟️」+ DB connected + 3 場演唱會。

---

## 方法 B：完整用 XAMPP 的 Apache（要設 VirtualHost）

只有想模擬正式部署環境才需要。

### 1. 把專案放到 htdocs

把整個 `ticket-platform` 資料夾複製或連結到 `C:\xampp\htdocs\ticket-platform`。

### 2. 編輯 Apache VirtualHost 設定

開 `C:\xampp\apache\conf\extra\httpd-vhosts.conf`，在最下面加：

```apache
<VirtualHost *:80>
    ServerName ticket.localhost
    DocumentRoot "C:/xampp/htdocs/ticket-platform/public"
    <Directory "C:/xampp/htdocs/ticket-platform/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**重點**：`DocumentRoot` 一定要指到 `public/`，不能指到專案根目錄（不然會把 `src/`、`.env` 等敏感檔案曝光出去）。

### 3. 編輯 hosts 檔（讓瀏覽器認得 `ticket.localhost`）

用系統管理員開 `C:\Windows\System32\drivers\etc\hosts`，加一行：

```
127.0.0.1   ticket.localhost
```

### 4. 重啟 Apache + MySQL

XAMPP Control Panel → 兩個都 Start。

### 5. 同方法 A 的步驟 2~4

建 DB、匯 schema/seed、設 .env、建 admin。

### 6. 瀏覽

`http://ticket.localhost/`

---

## 常見問題

| 症狀 | 原因 / 解法 |
|---|---|
| `php` 不是內部或外部命令 | 用完整路徑 `C:\xampp\php\php.exe`，或把 `C:\xampp\php` 加進 PATH |
| 連 DB 失敗 `SQLSTATE[HY000] [2002]` | MySQL 沒啟動，回 XAMPP Control Panel 點 Start |
| 連 DB 失敗 `Access denied` | `.env` 的 `DB_USER` / `DB_PASS` 跟 XAMPP 設的不一致 |
| 方法 B 開頁面顯示原始 PHP 碼 | Apache 沒啟動 PHP 模組，或 DocumentRoot 沒指對 |
| 方法 B 404 但首頁通 | `.htaccess` 沒生效，檢查 VirtualHost 裡的 `AllowOverride All` |

---

**最簡單就跟著方法 A 走**，組員每個人都這樣跑就行。
