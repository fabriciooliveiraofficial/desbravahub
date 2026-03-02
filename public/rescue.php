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

    // 2. Promote or Create User
    $adminEmail = 'fabriciooliveiraofficial@gmail.com';
    $password = 'Fdm060881@'; // Senha informada pelo usuário

    $user = db_fetch_one("SELECT id FROM users WHERE email = ?", [$adminEmail]);
    
    if (!$user) {
        echo "Usuário não encontrado localmente. Criando...<br>";
        
        // Obter um tenant ID válido (geralmente 1 no local/docker)
        $tenantId = db_fetch_column("SELECT id FROM tenants LIMIT 1") ?: 1;
        $roleId = db_fetch_column("SELECT id FROM roles WHERE name = 'admin' LIMIT 1") ?: 1;

        $authService = new \App\Services\AuthService();
        $passwordHash = $authService->hashPassword($password);

        $userId = db_insert('users', [
            'tenant_id' => $tenantId,
            'role_id' => $roleId,
            'name' => 'Fabricio Oliveira',
            'email' => $adminEmail,
            'password_hash' => $passwordHash,
            'status' => 'active',
            'is_superadmin' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($userId) {
            echo "<span style='color:green'>✅ Usuário '$adminEmail' CRIADO e promovido a Super Admin!</span><br>";
        } else {
            echo "<span style='color:red'>❌ Falha ao criar usuário.</span><br>";
        }
    } else {
        $updated = db_update('users', ['is_superadmin' => 1], "email = ?", [$adminEmail]);
        if ($updated) {
            echo "<span style='color:green'>✅ Usuário '$adminEmail' já existia e foi promovido a Super Admin!</span><br>";
        } else {
            echo "<span style='color:orange'>ℹ️ Usuário '$adminEmail' já era Super Admin.</span><br>";
        }
    }

    echo "<br><b>Finalizado!</b> Agora você pode tentar logar em: <a href='/super-admin/login'>/super-admin/login</a><br>";
    echo "<p style='color:red'>⚠️ IMPORTANTE: Delete este arquivo do servidor após o uso por segurança!</p>";

} catch (Exception $e) {
    echo "<span style='color:red'>🔥 ERRO FATAL: " . htmlspecialchars($e->getMessage()) . "</span><br>";
}
