-- ============================================================
-- Strip Map Ruas Jalan - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS `stripmap_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `stripmap_db`;

-- ------------------------------------------------------------
-- Tabel: ruas_jalan
-- Menyimpan data ruas jalan beserta STA awal/akhir
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ruas_jalan` (
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `kode_ruas`  VARCHAR(50)     NOT NULL,
    `nama_ruas`  VARCHAR(255)    NOT NULL,
    `sta_awal`   DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'STA Awal dalam meter',
    `sta_akhir`  DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'STA Akhir dalam meter',
    `panjang`    DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'Panjang total dalam meter',
    `koridor`    VARCHAR(100)    NULL COMMENT 'Koridor jalan',
    `kabupaten_kota` VARCHAR(100) NULL COMMENT 'Kabupaten / Kota lokasi ruas',
    `created_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_kode_ruas` (`kode_ruas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tabel: stripmap
-- Menyimpan data strip map per segmen untuk setiap ruas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `stripmap` (
    `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `ruas_id`       INT UNSIGNED    NOT NULL,
    `sta_awal`      DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'STA Awal segmen dalam meter',
    `sta_akhir`     DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'STA Akhir segmen dalam meter',
    `panjang`       DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'Panjang segmen dalam meter',
    `baik`          DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'Panjang kondisi Baik (meter)',
    `sedang`        DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'Panjang kondisi Sedang (meter)',
    `rusak_ringan`  DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'Panjang kondisi Rusak Ringan (meter)',
    `rusak_berat`   DECIMAL(10,2)   NOT NULL DEFAULT 0.00 COMMENT 'Panjang kondisi Rusak Berat (meter)',
    `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),
    KEY `idx_ruas_id` (`ruas_id`),

    CONSTRAINT `fk_stripmap_ruas`
        FOREIGN KEY (`ruas_id`)
        REFERENCES `ruas_jalan` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==================
--  DATABASE 
-- ==================
-- Jalankan query SQL di bawah ini di tab SQL database `stripmap_db` Anda
-- untuk memperbarui tabel ruas_jalan dengan kolom baru:

ALTER TABLE `ruas_jalan` ADD COLUMN `koridor` VARCHAR(100) NULL AFTER `panjang`;
ALTER TABLE `ruas_jalan` ADD COLUMN `kabupaten_kota` VARCHAR(100) NULL AFTER `koridor`;

