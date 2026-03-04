-- ============================================================================
-- REFERRAL INVITES SYSTEM
-- Tracks invitations sent by Pathfinders and their conversion journey
-- ============================================================================

CREATE TABLE IF NOT EXISTS `referral_invites` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `referrer_id` INT UNSIGNED NOT NULL COMMENT 'User who sent the invite',
    `email` VARCHAR(255) NOT NULL COMMENT 'Email of the invited person',
    `token` VARCHAR(64) NOT NULL COMMENT 'Unique tracking token',
    `status` ENUM('pending', 'clicked', 'registered', 'active') NOT NULL DEFAULT 'pending',
    `converted_user_id` INT UNSIGNED NULL COMMENT 'User ID after registration',
    `xp_rewarded` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'XP given to referrer',
    `rewarded_at` TIMESTAMP NULL COMMENT 'When XP was awarded',
    `clicked_at` TIMESTAMP NULL,
    `registered_at` TIMESTAMP NULL,
    `activated_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_referral_token` (`token`),
    KEY `idx_referral_tenant` (`tenant_id`),
    KEY `idx_referral_referrer` (`referrer_id`),
    KEY `idx_referral_email` (`email`),
    KEY `idx_referral_status` (`tenant_id`, `status`),
    CONSTRAINT `fk_referral_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_referral_referrer` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_referral_converted` FOREIGN KEY (`converted_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add referred_by column to users table (safe additive change)
ALTER TABLE `users` ADD COLUMN `referred_by_id` INT UNSIGNED NULL AFTER `notification_preferences`;
ALTER TABLE `users` ADD KEY `idx_users_referred_by` (`referred_by_id`);
