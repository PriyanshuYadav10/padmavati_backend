-- Padmavati Bangles — MySQL schema (port of the Mongoose models).
-- Import this once via phpMyAdmin (Database → Import) after creating the DB.

CREATE TABLE IF NOT EXISTS `contacts` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(120)  NOT NULL,
  `phone`         VARCHAR(20)   NOT NULL,
  `location`      VARCHAR(200)  NOT NULL DEFAULT '',
  `city`          VARCHAR(100)  NOT NULL DEFAULT '',
  `business_type` VARCHAR(100)  NOT NULL DEFAULT '',
  `notes`         VARCHAR(1000) NOT NULL DEFAULT '',
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  -- powers the free-text search endpoint
  FULLTEXT KEY `ft_contact` (`name`, `phone`, `location`, `city`, `business_type`, `notes`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`              VARCHAR(50)  NOT NULL DEFAULT 'app',
  `passcode_enabled` TINYINT(1)   NOT NULL DEFAULT 0,
  `passcode_hash`    VARCHAR(255)          DEFAULT NULL,
  `preferences`      JSON                  DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_settings_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The single global settings row is created automatically by the app on the
-- first /api/settings call (under a UTC connection, so its timestamps are
-- consistent). No seed INSERT here on purpose.
