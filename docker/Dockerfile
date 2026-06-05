FROM php:8.2-cli

# 安裝系統套件 + PHP 擴充
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    git \
    && docker-php-ext-install pdo_mysql zip opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 確保 $_ENV 能讀到容器注入的環境變數
RUN echo 'variables_order = "EGPCS"' > /usr/local/etc/php/conf.d/env.ini

# 開啟 OPcache(避免每個 request 重新編譯 PHP)
RUN echo 'opcache.enable=1' > /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'opcache.enable_cli=0' >> /usr/local/etc/php/conf.d/opcache.ini

# 從官方 image 取得 Composer(免下載 installer、可重現)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# 複製 composer.json 並安裝依賴
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]