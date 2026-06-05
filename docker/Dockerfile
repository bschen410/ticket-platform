FROM php:8.2-cli

# 安裝系統套件 + PHP 擴充
RUN apt-get update && apt-get install -y \
    unzip \
    libzip-dev \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# 確保 $_ENV 能讀到容器注入的環境變數
RUN echo 'variables_order = "EGPCS"' > /usr/local/etc/php/conf.d/env.ini

# 安裝 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# 複製 composer.json 並安裝依賴
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public", "public/index.php"]