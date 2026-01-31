-- ============================================================================
-- DesbravaHub - Complete Database Schema
-- Multi-tenant path-based architecture with strict tenant isolation
-- ============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================================
-- TENANTS
-- ============================================================================

CREATE TABLE `tenants` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `slug` VARCHAR(100) NOT NULL COMMENT 'URL path identifier (e.g., club-alpha)',
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `logo_url` VARCHAR(500) NULL,
    `status` ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'pending',
    `settings` JSON NULL COMMENT 'Tenant-specific configuration',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tenants_slug` (`slug`),
    KEY `idx_tenants_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ROLES & PERMISSIONS
-- ============================================================================

CREATE TABLE `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL COMMENT 'admin, director, pathfinder',
    `display_name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'System roles cannot be deleted',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_tenant_name` (`tenant_id`, `name`),
    KEY `idx_roles_tenant` (`tenant_id`),
    CONSTRAINT `fk_roles_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL COMMENT 'Permission key (e.g., activities.create)',
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `group` VARCHAR(100) NULL COMMENT 'Permission group for UI organization',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permissions_key` (`key`),
    KEY `idx_permissions_group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
    `role_id` INT UNSIGNED NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_id`, `permission_id`),
    KEY `idx_role_permissions_permission` (`permission_id`),
    CONSTRAINT `fk_role_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_role_permissions_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- USERS
-- ============================================================================

CREATE TABLE `levels` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `level_number` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `min_xp` INT UNSIGNED NOT NULL COMMENT 'Minimum XP required for this level',
    `badge_url` VARCHAR(500) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_levels_number` (`level_number`),
    KEY `idx_levels_min_xp` (`min_xp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `role_id` INT UNSIGNED NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `avatar_url` VARCHAR(500) NULL,
    `phone` VARCHAR(50) NULL,
    `birth_date` DATE NULL,
    `xp_points` INT UNSIGNED NOT NULL DEFAULT 0,
    `level_id` INT UNSIGNED NULL,
    `email_verified_at` TIMESTAMP NULL,
    `last_login_at` TIMESTAMP NULL,
    `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
    `notification_preferences` JSON NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL COMMENT 'Soft delete',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_users_tenant_email` (`tenant_id`, `email`),
    KEY `idx_users_tenant` (`tenant_id`),
    KEY `idx_users_role` (`role_id`),
    KEY `idx_users_level` (`level_id`),
    KEY `idx_users_tenant_status` (`tenant_id`, `status`),
    KEY `idx_users_xp` (`xp_points`),
    CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
    CONSTRAINT `fk_users_level` FOREIGN KEY (`level_id`) REFERENCES `levels` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_sessions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `expires_at` TIMESTAMP NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_sessions_token` (`token_hash`),
    KEY `idx_sessions_user` (`user_id`),
    KEY `idx_sessions_expires` (`expires_at`),
    CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ACTIVITIES
-- ============================================================================

CREATE TABLE `activities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `instructions` TEXT NULL,
    `min_level` INT UNSIGNED NOT NULL DEFAULT 1,
    `xp_reward` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_outdoor` TINYINT(1) NOT NULL DEFAULT 0,
    `proof_types` JSON NOT NULL COMMENT '["url", "upload", "quiz"]',
    `max_attempts` INT UNSIGNED NULL COMMENT 'NULL = unlimited',
    `deadline_days` INT UNSIGNED NULL COMMENT 'Days to complete after starting',
    `status` ENUM('active', 'inactive', 'draft') NOT NULL DEFAULT 'draft',
    `order_position` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_activities_tenant` (`tenant_id`),
    KEY `idx_activities_tenant_status` (`tenant_id`, `status`),
    KEY `idx_activities_min_level` (`min_level`),
    KEY `idx_activities_created_by` (`created_by`),
    CONSTRAINT `fk_activities_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_activities_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_prerequisites` (
    `activity_id` INT UNSIGNED NOT NULL,
    `prerequisite_activity_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`activity_id`, `prerequisite_activity_id`),
    KEY `idx_prerequisites_prerequisite` (`prerequisite_activity_id`),
    CONSTRAINT `fk_prerequisites_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_prerequisites_prerequisite` FOREIGN KEY (`prerequisite_activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_activities` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `activity_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `status` ENUM('in_progress', 'pending_review', 'completed', 'failed') NOT NULL DEFAULT 'in_progress',
    `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
    `xp_earned` INT UNSIGNED NOT NULL DEFAULT 0,
    `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    `deadline_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_activities` (`user_id`, `activity_id`),
    KEY `idx_user_activities_tenant` (`tenant_id`),
    KEY `idx_user_activities_status` (`tenant_id`, `status`),
    KEY `idx_user_activities_activity` (`activity_id`),
    CONSTRAINT `fk_user_activities_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_activities_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_activities_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- PROOFS
-- ============================================================================

CREATE TABLE `activity_proofs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_activity_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `type` ENUM('url', 'upload', 'quiz') NOT NULL,
    `content` TEXT NULL COMMENT 'URL or file path',
    `quiz_attempt_id` INT UNSIGNED NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `submitted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at` TIMESTAMP NULL,
    `reviewed_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_proofs_user_activity` (`user_activity_id`),
    KEY `idx_proofs_tenant` (`tenant_id`),
    KEY `idx_proofs_tenant_status` (`tenant_id`, `status`),
    KEY `idx_proofs_reviewed_by` (`reviewed_by`),
    CONSTRAINT `fk_proofs_user_activity` FOREIGN KEY (`user_activity_id`) REFERENCES `user_activities` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_proofs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_proofs_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `proof_reviews` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `proof_id` INT UNSIGNED NOT NULL,
    `reviewer_id` INT UNSIGNED NOT NULL,
    `action` ENUM('approved', 'rejected', 'requested_changes') NOT NULL,
    `comment` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_proof_reviews_proof` (`proof_id`),
    KEY `idx_proof_reviews_reviewer` (`reviewer_id`),
    CONSTRAINT `fk_proof_reviews_proof` FOREIGN KEY (`proof_id`) REFERENCES `activity_proofs` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_proof_reviews_reviewer` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- QUIZZES
-- ============================================================================

CREATE TABLE `quizzes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `activity_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `passing_score` INT UNSIGNED NOT NULL DEFAULT 70 COMMENT 'Percentage required to pass',
    `time_limit_minutes` INT UNSIGNED NULL,
    `shuffle_questions` TINYINT(1) NOT NULL DEFAULT 0,
    `shuffle_options` TINYINT(1) NOT NULL DEFAULT 0,
    `show_correct_answers` TINYINT(1) NOT NULL DEFAULT 1,
    `max_attempts` INT UNSIGNED NULL COMMENT 'NULL = unlimited',
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_quizzes_activity` (`activity_id`),
    KEY `idx_quizzes_tenant` (`tenant_id`),
    CONSTRAINT `fk_quizzes_activity` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quizzes_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quizzes_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_questions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id` INT UNSIGNED NOT NULL,
    `question_text` TEXT NOT NULL,
    `question_type` ENUM('single_choice', 'multiple_choice', 'true_false', 'text') NOT NULL DEFAULT 'single_choice',
    `points` INT UNSIGNED NOT NULL DEFAULT 1,
    `order_position` INT UNSIGNED NOT NULL DEFAULT 0,
    `explanation` TEXT NULL COMMENT 'Shown after answering',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_quiz_questions_quiz` (`quiz_id`),
    CONSTRAINT `fk_quiz_questions_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `quiz_options` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question_id` INT UNSIGNED NOT NULL,
    `option_text` TEXT NOT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `order_position` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_quiz_options_question` (`question_id`),
    CONSTRAINT `fk_quiz_options_question` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_quiz_attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `quiz_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `score` DECIMAL(5,2) NULL COMMENT 'Percentage score',
    `passed` TINYINT(1) NULL,
    `answers` JSON NOT NULL COMMENT 'Stored answers for review',
    `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` TIMESTAMP NULL,
    `time_spent_seconds` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_quiz_attempts_quiz` (`quiz_id`),
    KEY `idx_quiz_attempts_user` (`user_id`),
    KEY `idx_quiz_attempts_tenant` (`tenant_id`),
    CONSTRAINT `fk_quiz_attempts_quiz` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quiz_attempts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_quiz_attempts_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- ACHIEVEMENTS
-- ============================================================================

CREATE TABLE `achievements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `badge_url` VARCHAR(500) NULL,
    `xp_reward` INT UNSIGNED NOT NULL DEFAULT 0,
    `criteria_type` ENUM('activities_completed', 'xp_earned', 'level_reached', 'custom') NOT NULL,
    `criteria_value` INT UNSIGNED NULL,
    `criteria_data` JSON NULL COMMENT 'Custom criteria definition',
    `is_hidden` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Secret achievements',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_achievements_tenant` (`tenant_id`),
    CONSTRAINT `fk_achievements_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_achievements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `achievement_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `earned_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `notified` TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_achievements` (`user_id`, `achievement_id`),
    KEY `idx_user_achievements_tenant` (`tenant_id`),
    KEY `idx_user_achievements_achievement` (`achievement_id`),
    CONSTRAINT `fk_user_achievements_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_achievements_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_achievements_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- EVENTS
-- ============================================================================

CREATE TABLE `events` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `location` VARCHAR(500) NULL,
    `start_datetime` DATETIME NOT NULL,
    `end_datetime` DATETIME NULL,
    `max_participants` INT UNSIGNED NULL,
    `registration_deadline` DATETIME NULL,
    `xp_reward` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` ENUM('upcoming', 'ongoing', 'completed', 'cancelled') NOT NULL DEFAULT 'upcoming',
    `created_by` INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_events_tenant` (`tenant_id`),
    KEY `idx_events_tenant_status` (`tenant_id`, `status`),
    KEY `idx_events_start` (`start_datetime`),
    CONSTRAINT `fk_events_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `event_enrollments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `event_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `status` ENUM('enrolled', 'attended', 'cancelled', 'no_show') NOT NULL DEFAULT 'enrolled',
    `enrolled_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `attended_at` TIMESTAMP NULL,
    `xp_earned` INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_event_enrollments` (`event_id`, `user_id`),
    KEY `idx_enrollments_user` (`user_id`),
    KEY `idx_enrollments_tenant` (`tenant_id`),
    CONSTRAINT `fk_enrollments_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enrollments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_enrollments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- NOTIFICATIONS
-- ============================================================================

CREATE TABLE `notifications` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL COMMENT 'NULL = broadcast to all tenant users',
    `type` VARCHAR(100) NOT NULL COMMENT 'achievement, activity, event, system, broadcast',
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `data` JSON NULL COMMENT 'Additional data (links, IDs, etc.)',
    `channels` JSON NOT NULL COMMENT '["toast", "push", "email"]',
    `priority` ENUM('low', 'normal', 'high', 'critical') NOT NULL DEFAULT 'normal',
    `read_at` TIMESTAMP NULL,
    `sent_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_notifications_tenant_user` (`tenant_id`, `user_id`),
    KEY `idx_notifications_user_read` (`user_id`, `read_at`),
    KEY `idx_notifications_created` (`created_at`),
    CONSTRAINT `fk_notifications_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_notification_preferences` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `tenant_id` INT UNSIGNED NOT NULL,
    `notification_type` VARCHAR(100) NOT NULL,
    `channel_toast` TINYINT(1) NOT NULL DEFAULT 1,
    `channel_push` TINYINT(1) NOT NULL DEFAULT 1,
    `channel_email` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_notification_prefs` (`user_id`, `notification_type`),
    KEY `idx_notification_prefs_tenant` (`tenant_id`),
    CONSTRAINT `fk_notification_prefs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_notification_prefs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- APP VERSIONS & FEATURE FLAGS
-- ============================================================================

CREATE TABLE `app_versions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `version_code` VARCHAR(50) NOT NULL COMMENT 'Semantic version (e.g., 1.2.0)',
    `version_number` INT UNSIGNED NOT NULL COMMENT 'Numeric for comparison',
    `release_notes` TEXT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Currently deployed version',
    `is_critical` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Forces immediate update',
    `min_supported_version` INT UNSIGNED NULL COMMENT 'Minimum client version allowed',
    `released_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_versions_code` (`version_code`),
    KEY `idx_versions_active` (`is_active`),
    KEY `idx_versions_number` (`version_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tenant_versions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `app_version_id` INT UNSIGNED NOT NULL,
    `rollout_status` ENUM('pending', 'active', 'paused', 'rolled_back') NOT NULL DEFAULT 'pending',
    `rollout_percentage` INT UNSIGNED NOT NULL DEFAULT 100 COMMENT 'For gradual rollout',
    `activated_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tenant_versions` (`tenant_id`, `app_version_id`),
    KEY `idx_tenant_versions_version` (`app_version_id`),
    CONSTRAINT `fk_tenant_versions_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tenant_versions_version` FOREIGN KEY (`app_version_id`) REFERENCES `app_versions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `feature_flags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `default_enabled` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_feature_flags_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tenant_feature_flags` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `feature_flag_id` INT UNSIGNED NOT NULL,
    `enabled` TINYINT(1) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tenant_feature_flags` (`tenant_id`, `feature_flag_id`),
    KEY `idx_tenant_feature_flags_flag` (`feature_flag_id`),
    CONSTRAINT `fk_tenant_feature_flags_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tenant_feature_flags_flag` FOREIGN KEY (`feature_flag_id`) REFERENCES `feature_flags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- EMAIL LOGS (for SMTP tracking)
-- ============================================================================

CREATE TABLE `email_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `to_email` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(500) NOT NULL,
    `template` VARCHAR(100) NULL,
    `status` ENUM('queued', 'sent', 'failed', 'bounced') NOT NULL DEFAULT 'queued',
    `error_message` TEXT NULL,
    `sent_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email_logs_tenant` (`tenant_id`),
    KEY `idx_email_logs_user` (`user_id`),
    KEY `idx_email_logs_status` (`status`),
    CONSTRAINT `fk_email_logs_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_email_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- SPECIALTY E-LEARNING SYSTEM
-- ============================================================================

-- Specialty assignments (admin assigns to pathfinders)
CREATE TABLE `specialty_assignments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tenant_id` INT UNSIGNED NOT NULL,
    `specialty_id` VARCHAR(50) NOT NULL COMMENT 'References specialties_repository.json',
    `user_id` INT UNSIGNED NOT NULL,
    `assigned_by` INT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    `due_date` DATE NULL,
    `instructions` TEXT NULL,
    `xp_earned` INT UNSIGNED NOT NULL DEFAULT 0,
    `started_at` TIMESTAMP NULL,
    `completed_at` TIMESTAMP NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_specialty_assignment` (`tenant_id`, `specialty_id`, `user_id`),
    KEY `idx_specialty_assignments_user` (`user_id`),
    KEY `idx_specialty_assignments_status` (`tenant_id`, `status`),
    CONSTRAINT `fk_specialty_assignments_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_specialty_assignments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_specialty_assignments_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Specialty requirements (questions/tasks for each specialty)
CREATE TABLE `specialty_requirements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `specialty_id` VARCHAR(50) NOT NULL COMMENT 'References specialties_repository.json',
    `order_num` INT UNSIGNED NOT NULL DEFAULT 1,
    `type` ENUM('text', 'multiple_choice', 'checkbox', 'file_upload', 'practical') NOT NULL DEFAULT 'text',
    `title` VARCHAR(500) NOT NULL,
    `description` TEXT NULL,
    `options` JSON NULL COMMENT 'For multiple_choice/checkbox types',
    `correct_answer` JSON NULL COMMENT 'Expected answer(s)',
    `points` INT UNSIGNED NOT NULL DEFAULT 10,
    `is_required` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_specialty_requirements_specialty` (`specialty_id`),
    KEY `idx_specialty_requirements_order` (`specialty_id`, `order_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User progress on each requirement
CREATE TABLE `user_requirement_progress` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignment_id` INT UNSIGNED NOT NULL,
    `requirement_id` INT UNSIGNED NOT NULL,
    `status` ENUM('pending', 'answered', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `answer` TEXT NULL COMMENT 'User response (text, JSON for choices)',
    `file_path` VARCHAR(500) NULL COMMENT 'For file_upload type',
    `answered_at` TIMESTAMP NULL,
    `reviewed_by` INT UNSIGNED NULL,
    `reviewed_at` TIMESTAMP NULL,
    `feedback` TEXT NULL COMMENT 'Instructor feedback',
    `points_earned` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_requirement_progress` (`assignment_id`, `requirement_id`),
    KEY `idx_user_requirement_progress_status` (`status`),
    CONSTRAINT `fk_user_requirement_progress_assignment` FOREIGN KEY (`assignment_id`) REFERENCES `specialty_assignments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_requirement_progress_requirement` FOREIGN KEY (`requirement_id`) REFERENCES `specialty_requirements` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_requirement_progress_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
