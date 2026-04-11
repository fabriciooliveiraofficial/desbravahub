<?php
/**
 * Migration: v_event_modernization
 * 
 * Adds support for:
 * - Cover images
 * - Exclusive badges (achievements)
 * - Event types (presencial/online/hibrido)
 * - QR check-in
 * - Waitlist and recurring logic
 */

require_once dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';

echo "🚀 Iniciando migração de modernização de eventos...\n";

try {
    // 1. Alter events table
    db_query("ALTER TABLE `events` 
        ADD COLUMN `cover_image_url` VARCHAR(500) NULL AFTER `description`,
        ADD COLUMN `achievement_id` INT UNSIGNED NULL AFTER `xp_reward`,
        ADD COLUMN `type` ENUM('presencial', 'online', 'hibrido') NOT NULL DEFAULT 'presencial' AFTER `location`,
        ADD COLUMN `qr_code_token` VARCHAR(100) NULL AFTER `slug`,
        ADD COLUMN `recurrence_type` ENUM('none', 'weekly', 'biweekly', 'monthly') NOT NULL DEFAULT 'none' AFTER `status`
    ");
    echo "✅ Tabela 'events' atualizada.\n";

    // 2. Alter event_enrollments table
    db_query("ALTER TABLE `event_enrollments`
        ADD COLUMN `admin_notes` TEXT NULL AFTER `xp_earned`,
        ADD COLUMN `certificate_issued_at` TIMESTAMP NULL AFTER `enrolled_at`
    ");
    echo "✅ Tabela 'event_enrollments' atualizada.\n";

    // 3. Add foreign key index
    db_query("ALTER TABLE `events` ADD CONSTRAINT `fk_events_achievement` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE SET NULL");
    echo "✅ Chave estrangeira para conquistas adicionada.\n";

    echo "\n🎉 Migração concluída com sucesso!\n";
} catch (\Exception $e) {
    echo "\n❌ ERRO na migração: " . $e->getMessage() . "\n";
    exit(1);
}
