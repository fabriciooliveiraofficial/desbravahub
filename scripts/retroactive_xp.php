<?php
// scripts/retroactive_xp.php

// Define BASE_PATH if not defined
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// 1. Load Helpers first
require_once BASE_PATH . '/helpers/env.php';
require_once BASE_PATH . '/helpers/database.php';

// 2. Load Core & Services
require_once BASE_PATH . '/app/Core/App.php';
require_once BASE_PATH . '/app/Services/ProgressionService.php';

use App\Core\App;
use App\Services\ProgressionService;

echo "Iniciando correção de XP...\n";

// 3. Bootstrap Database Connection
// We manually connect because we're outside the full App framework bootstrap
try {
    $config = require BASE_PATH . '/config/database.php';
    $connConfig = $config['connections'][$config['default']];
    
    $dsn = "mysql:host={$connConfig['host']};port={$connConfig['port']};dbname={$connConfig['database']};charset={$connConfig['charset']}";
    
    $pdo = new PDO($dsn, $connConfig['username'], $connConfig['password'], $config['options']);
    
    // Bind to App container so db() helper works
    App::bind('database', $pdo);
    
    echo "Conectado ao banco de dados.\n";
    
} catch (PDOException $e) {
    die("Erro fatal de conexão: " . $e->getMessage() . "\n");
}

// 4. Run Logic
$progressionService = new ProgressionService();

// Get all completed programs that have XP reward
$completed = db_fetch_all("
    SELECT upp.id, upp.user_id, upp.program_id, lp.xp_reward, lp.name, u.name as user_name, u.xp_points
    FROM user_program_progress upp
    JOIN learning_programs lp ON upp.program_id = lp.id
    JOIN users u ON upp.user_id = u.id
    WHERE upp.status = 'completed' AND lp.xp_reward > 0
");

echo "Total de programas concluídos encontrados: " . count($completed) . "\n";

$count = 0;
foreach ($completed as $c) {
    // Audit: We assume XP wasn't added because the code didn't exist.
    // We add it now.
    
    echo " > Adicionando {$c['xp_reward']} XP para {$c['user_name']} (Programa: {$c['name']})...\n";
    
    $result = $progressionService->addXp($c['user_id'], $c['xp_reward'], 'retroactive_fix', $c['program_id']);
    
    if ($result['success']) {
        $count++;
    } else {
        echo "   X Erro ao adicionar: " . ($result['error'] ?? 'Unknown') . "\n";
    }
}

echo "----------------------------------------\n";
echo "Processo finalizado! {$count} correções aplicadas.\n";
