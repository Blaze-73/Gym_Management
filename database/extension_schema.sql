-- Fix order status values (run if shipped/delivered updates fail)
-- ALTER TABLE `orders` MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'pending';
-- UPDATE `orders` SET `status` = 'shipped' WHERE `status` = 'processing';
-- UPDATE `orders` SET `status` = 'delivered' WHERE `status` = 'completed';

-- Extension schema (ADD ONLY) — run after main gym_management.sql
-- Checkout info + order tracking fields

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `customer_name` VARCHAR(191) NULL AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `customer_email` VARCHAR(191) NULL,
  ADD COLUMN IF NOT EXISTS `customer_phone` VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS `shipping_address` TEXT NULL,
  ADD COLUMN IF NOT EXISTS `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER `status`;

ALTER TABLE `subscriptions`
  ADD COLUMN IF NOT EXISTS `customer_name` VARCHAR(191) NULL AFTER `user_id`,
  ADD COLUMN IF NOT EXISTS `customer_email` VARCHAR(191) NULL,
  ADD COLUMN IF NOT EXISTS `customer_phone` VARCHAR(30) NULL,
  ADD COLUMN IF NOT EXISTS `billing_address` TEXT NULL;

-- Tables payments & subscriptions: see database/payment_tables.sql
