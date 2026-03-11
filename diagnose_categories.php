<?php
// Script de Diagnóstico: Categorias de Especialidades e Classes
// Acesse via CLI (php diagnose_categories.php) ou via Navegador

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/database.php';

try {
    // Buscar todas as categorias
    $categories = db_fetch_all("SELECT id, tenant_id, name, type, status, created_at FROM learning_categories ORDER BY tenant_id, created_at DESC");

    echo "<h1>Diagnóstico de Categorias Criadas</h1>\n";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%; font-family: sans-serif;'>\n";
    echo "<tr><th>ID</th><th>Tenant ID</th><th>Nome da Categoria</th><th>Tipo (Type)</th><th>Status</th><th>Data de Criação</th></tr>\n";
    
    foreach ($categories as $cat) {
        $typeColor = match($cat['type']) {
            'specialty' => '#e0f2fe', // light blue
            'class' => '#fef3c7',     // light amber
            'both' => '#dcfce7',      // light green
            default => '#fee2e2'      // light red
        };

        echo "<tr style='background-color: {$typeColor}'>\n";
        echo "<td>{$cat['id']}</td>\n";
        echo "<td>{$cat['tenant_id']}</td>\n";
        echo "<td><strong>" . htmlspecialchars($cat['name']) . "</strong></td>\n";
        echo "<td>{$cat['type']}</td>\n";
        echo "<td>{$cat['status']}</td>\n";
        echo "<td>{$cat['created_at']}</td>\n";
        echo "</tr>\n";
    }
    echo "</table>\n";

    echo "<h3>Análise do Problema:</h3>\n";
    echo "<p>No código fonte, verificamos que:</p>\n";
    echo "<ul>\n";
    echo "<li><strong>Especialidades (SpecialtyController.php):</strong> Tem o filtro <code>AND type IN ('specialty', 'both')</code>.</li>\n";
    echo "<li><strong>Classes (ProgramController.php):</strong> Não possui filtro de tipo (está buscando todas as categorias, sem <code>AND type IN ('class', 'both')</code> na função <code>getCategories()</code>).</li>\n";
    echo "</ul>\n";
    echo "<p>Além disso, o formulário de <em>Nova Categoria</em> no Mission Control (em <code>create_category_modal.php</code>) envia os tipos: <code>specialty</code>, <code>class</code> e <code>both</code>, mas algo pode estar impedindo a listagem na view Frontend.</p>";
    
    echo "<h3>O que verificar a seguir:</h3>";
    echo "<ol>";
    echo "<li>Se os tipos na tabela acima correspondem ao esperado pelas views.</li>";
    echo "<li>Corrigir o <code>ProgramController.php</code> para filtrar por <code>class</code> e <code>both</code>.</li>";
    echo "</ol>";

} catch (Exception $e) {
    echo "Erro ao conectar com o banco de dados: " . $e->getMessage();
}
