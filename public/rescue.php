<?php
/**
 * DB Rescue Script
 * Run this ONCE on production to fix the missing is_superadmin column.
 * Visit: https://cruzeirodosuljuveve.org/rescue.php
 */

require_once __DIR__ . '/../bootstrap/bootstrap.php';

echo "<h2>DesbravaHub Database Rescue</h2>";

try {
    // 1. Add Column
    echo "Limpando/Preparando estrutura...<br>";
    try {
        db_query("ALTER TABLE users ADD COLUMN is_superadmin TINYINT(1) DEFAULT 0 AFTER role_id");
        echo "<span style='color:green'>✅ Coluna 'is_superadmin' adicionada com sucesso.</span><br>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<span style='color:orange'>ℹ️ A coluna 'is_superadmin' já existia.</span><br>";
        } else {
            throw $e;
        }
    }

    // 2. Promote User
    $adminEmail = 'fabriciooliveiraofficial@gmail.com';
    $updated = db_update('users', ['is_superadmin' => 1], "email = ?", [$adminEmail]);
    
    if ($updated) {
        echo "<span style='color:green'>✅ Usuário '$adminEmail' promovido a Super Admin!</span><br>";
    } else {
        echo "<span style='color:red'>❌ Usuário '$adminEmail' não encontrado para promoção.</span><br>";
    }

    echo "<br><b>Finalizado!</b> Agora você pode tentar logar em: <a href='/super-admin/login'>/super-admin/login</a><br>";
    echo "<p style='color:red'>⚠️ IMPORTANTE: Delete este arquivo do servidor após o uso por segurança!</p>";

} catch (Exception $e) {
    echo "<span style='color:red'>🔥 ERRO FATAL: " . h($e->getMessage()) . "</span><br>";
}
