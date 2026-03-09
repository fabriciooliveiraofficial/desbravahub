-- ============================================================================
-- Migration: Ensure push_subscriptions table exists with proper indexes
-- ============================================================================
-- Run this if push_subscriptions was created manually without indexes.
-- All statements use IF NOT EXISTS / IF EXISTS to be safe to re-run.

-- Create table if it doesn't exist yet
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id`   INT UNSIGNED NOT NULL,
    `user_id`     INT UNSIGNED NOT NULL,
    `endpoint`    TEXT NOT NULL,
    `p256dh`      TEXT NOT NULL,
    `auth`        VARCHAR(255) NOT NULL,
    `user_agent`  TEXT NULL,
    `device_type` VARCHAR(50) NULL DEFAULT 'desktop',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index: look up subscriptions by user (WebPushService::sendToUser)
-- Used on every push send: SELECT * FROM push_subscriptions WHERE user_id = ?
ALTER TABLE `push_subscriptions`
    ADD INDEX IF NOT EXISTS `idx_push_subs_user` (`user_id`);

-- Index: look up by endpoint (subscribe UPSERT check + delete expired)
-- Used in: subscribe(), resubscribe(), isSubscriptionExpired() cleanup
ALTER TABLE `push_subscriptions`
    ADD INDEX IF NOT EXISTS `idx_push_subs_endpoint` (`endpoint`(191));

-- Composite index: tenant + user (for tenant-scoped subscription queries)
ALTER TABLE `push_subscriptions`
    ADD INDEX IF NOT EXISTS `idx_push_subs_tenant_user` (`tenant_id`, `user_id`);

-- ============================================================================
-- Additional: ensure notifications table has optimal composite index
-- for getUnread() query: WHERE user_id = ? AND read_at IS NULL ORDER BY created_at DESC
-- ============================================================================
ALTER TABLE `notifications`
    ADD INDEX IF NOT EXISTS `idx_notifications_user_unread_created`
        (`user_id`, `read_at`, `created_at`);
