-- 演唱會票務平台 schema
-- 使用前先：CREATE DATABASE ticket_platform CHARACTER SET utf8mb4;
-- 然後：mysql -u root -p ticket_platform < sql/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS zones;
DROP TABLE IF EXISTS concerts;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id                      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email                   VARCHAR(190) NOT NULL UNIQUE,
    password_hash           VARCHAR(255) NOT NULL,
    name                    VARCHAR(80)  NOT NULL,
    role                    ENUM('customer','admin') NOT NULL DEFAULT 'customer',
    email_verified_at       DATETIME NULL,
    verification_code       VARCHAR(255) NULL,
    verification_expires_at DATETIME NULL,
    created_at              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE concerts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(120) NOT NULL,
    venue           VARCHAR(120) NOT NULL,
    performed_at    DATETIME NOT NULL,
    poster_url      VARCHAR(255) NULL,
    venue_map_url   VARCHAR(255) NULL,
    program_intro   TEXT NULL,
    price_info      TEXT NULL,
    notices         TEXT NULL,
    sales_start_at  DATETIME NOT NULL,
    sales_end_at    DATETIME NOT NULL,
    status          ENUM('draft','on_sale','closed') NOT NULL DEFAULT 'draft',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status_perform (status, performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE zones (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    concert_id      INT UNSIGNED NOT NULL,
    name            VARCHAR(40)  NOT NULL,
    price           DECIMAL(10,2) NOT NULL,
    total_seats     INT UNSIGNED NOT NULL,
    sold_seats      INT UNSIGNED NOT NULL DEFAULT 0,
    INDEX idx_concert (concert_id),
    CONSTRAINT fk_zones_concert FOREIGN KEY (concert_id) REFERENCES concerts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id         INT UNSIGNED NOT NULL,
    concert_id      INT UNSIGNED NOT NULL,
    status          ENUM('pending','paid','expired','cancelled') NOT NULL DEFAULT 'pending',
    total_amount    DECIMAL(10,2) NOT NULL,
    expires_at      DATETIME NOT NULL,
    paid_at         DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_status_expires (status, expires_at),
    CONSTRAINT fk_orders_user    FOREIGN KEY (user_id)    REFERENCES users(id),
    CONSTRAINT fk_orders_concert FOREIGN KEY (concert_id) REFERENCES concerts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    zone_id         INT UNSIGNED NOT NULL,
    quantity        INT UNSIGNED NOT NULL,
    unit_price      DECIMAL(10,2) NOT NULL,
    seat_labels     JSON NULL,
    INDEX idx_order (order_id),
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_items_zone  FOREIGN KEY (zone_id)  REFERENCES zones(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
