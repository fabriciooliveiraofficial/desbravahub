<?php

namespace App\Controllers;

use App\Core\App;
use App\Services\ProgressionService;

class FixController
{
    public function run(): void
    {
        $tenant = App::tenant();
        $user = App::user(); // Ensure logged in, or remove if global
        
        if (!$user) {
            echo "Faça login primeiro.";
            return;
        }

        echo "<h2>Iniciando correção de XP...</h2>";
        $progressionService = new ProgressionService();

        // Get all completed programs
        $completed = db_fetch_all("
            SELECT upp.id, upp.user_id, upp.program_id, lp.xp_reward, lp.name, u.name as user_name, u.xp_points
            FROM user_program_progress upp
            JOIN learning_programs lp ON upp.program_id = lp.id
            JOIN users u ON upp.user_id = u.id
            WHERE upp.status = 'completed' AND lp.xp_reward > 0 AND upp.tenant_id = ?
        ", [$tenant['id']]);

        $count = 0;
        echo "<ul>";
        foreach ($completed as $c) {
            echo "<li>Adicionando {$c['xp_reward']} XP para <strong>{$c['user_name']}</strong> (Programa: {$c['name']})... ";
            
            $result = $progressionService->addXp($c['user_id'], $c['xp_reward'], 'retroactive_fix', $c['program_id']);
            
            if ($result['success']) {
                $count++;
                echo "<span style='color:green'>Sucesso!</span> (Total agora: {$result['total_xp']})</li>";
            } else {
                echo "<span style='color:red'>Erro: " . ($result['error'] ?? 'Unknown') . "</span></li>";
            }
        }
        echo "</ul>";
        echo "<h3>Finalizado! {$count} correções aplicadas.</h3>";
        exit;
    }
}
