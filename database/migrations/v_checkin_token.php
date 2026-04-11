<?php
/**
 * Migration: v_checkin_token
 * 
 * Adds unique check-in tokens for individual event enrollments.
 */

require_once dirname(dirname(__DIR__)) . '/bootstrap/bootstrap.php';

echo "🚀 Adicionando suporte a Check-in via QR Code...\n";

try {
    // Add checkin_token to event_enrollments
    db_query("ALTER TABLE `event_enrollments`
        ADD COLUMN `checkin_token` VARCHAR(64) UNIQUE NULL AFTER `status`
    ");
    echo "✅ Tabela 'event_enrollments' atualizada com tokens de check-in.\n";

    echo "\n🎉 Migração concluída com sucesso!\n";
} catch (\Exception $e) {
    echo "\n❌ ERRO na migração: " . $e->getMessage() . "\n";
    exit(1);
}
