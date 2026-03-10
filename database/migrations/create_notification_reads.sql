-- Migration: Create notification_reads table
-- Tracks per-user read status for broadcast notifications (user_id IS NULL).
-- User-specific notifications (user_id IS NOT NULL) continue using notifications.read_at.

CREATE TABLE IF NOT EXISTS `notification_reads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `notification_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `read_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_read` (`notification_id`, `user_id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_notification_id` (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
