-- New tables only (do not modify existing tables)
-- Run after your main schema is in place

CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `plan_id` bigint UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `payment_status` enum('pending','paid','failed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_user_id_payment_status_end_date_index` (`user_id`,`payment_status`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('plan','store') NOT NULL,
  `status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `paypal_order_id` varchar(255) DEFAULT NULL,
  `subscription_id` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  UNIQUE KEY `payments_paypal_order_id_unique` (`paypal_order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: align plans table to exactly 3 fixed plans (ids 1–3)
-- DELETE FROM plans WHERE id > 3;
-- INSERT INTO plans (id, name, price, duration, created_at, updated_at) VALUES
-- (1, 'S-TIER PULSE', 49.00, 30, NOW(), NOW()),
-- (2, 'INTERSTELLAR', 399.00, 365, NOW(), NOW()),
-- (3, 'ALPHA ORBIT', 129.00, 30, NOW(), NOW())
-- ON DUPLICATE KEY UPDATE name=VALUES(name), price=VALUES(price), duration=VALUES(duration);
