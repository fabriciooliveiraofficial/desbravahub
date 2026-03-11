-- Migration: ensure learning_categories has type and sort_order columns
-- Safe to run multiple times (uses IF NOT EXISTS / IGNORE logic via PHP wrapper)

ALTER TABLE `learning_categories`
    ADD COLUMN IF NOT EXISTS `type` ENUM('specialty', 'class', 'both') NOT NULL DEFAULT 'specialty' AFTER `icon`,
    ADD COLUMN IF NOT EXISTS `sort_order` INT NOT NULL DEFAULT 0 AFTER `status`;
