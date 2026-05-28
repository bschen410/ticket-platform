FROM php:8.2-cli

# 安裝 PDO MySQL 驅動
RUN docker-php-ext-install pdo_mysql

# 確保 $_ENV 能讀到容器注入的環境變數
RUN echo 'variables_order = "EGPCS"' > /usr/local/etc/php/conf.d/env.ini

WORKDIR /var/www/html

EXPOSE 8000

# 由 docker-compose 的 command 覆蓋；這裡留個合理預設
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]
