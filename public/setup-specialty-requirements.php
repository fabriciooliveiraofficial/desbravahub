<?php
/**
 * Setup Script: Populate Specialty Requirements
 * 
 * This script populates the specialty_requirements table with sample requirements
 * for testing the E-Learning flow.
 * 
 * IMPORTANT: Delete this file after running!
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Direct database connection with production credentials
$config = [
    'db_host' => 'localhost',
    'db_name' => 'u714643564_db_desbravahub',
    'db_user' => 'u714643564_user_desbravah',
    'db_pass' => 'Fdm399788896528168172@#$%',
];

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("<h1>❌ Erro de conexão</h1><pre>{$e->getMessage()}</pre>");
}

// Sample requirements for popular specialties (based on official Pathfinder content)
$specialtyRequirements = [
    // NÓS E AMARRAS (Knots and Lashings) - Outdoor
    'camp_001' => [
        'type' => 'outdoor',
        'xp_reward' => 150,
        'duration_hours' => 4,
        'description' => 'Aprenda a fazer nós e amarras essenciais para acampamento e sobrevivência.',
        'requirements' => [
            ['type' => 'text', 'title' => 'Defina os seguintes termos: laço, volta, corda, fio, bitola.', 'points' => 10],
            ['type' => 'practical', 'title' => 'Faça os seguintes nós e explique seu uso: nó direito, nó de correr, laço de fiel, volta do fiel, volta da ribeira, catau, lais de guia, pescador duplo, nó de escota, nó simples, volta da encapeladura.', 'points' => 30],
            ['type' => 'practical', 'title' => 'Faça as seguintes amarras e explique seu uso: amarra quadrada, amarra diagonal, amarra paralela (redonda).', 'points' => 20],
            ['type' => 'practical', 'title' => 'Faça uma armação de cabide ou porta panela usando os nós e amarras aprendidos.', 'points' => 20],
            ['type' => 'text', 'title' => 'Como você deve cuidar de uma corda? Descreva as melhores práticas.', 'points' => 10],
            ['type' => 'practical', 'title' => 'Enrole e desenrole apropriadamente uma corda de pelo menos 6 metros.', 'points' => 10]
        ]
    ],

    // PRIMEIROS SOCORROS BÁSICO - Indoor/Text
    'health_001' => [
        'type' => 'indoor',
        'xp_reward' => 200,
        'duration_hours' => 6,
        'description' => 'Conheça os procedimentos básicos de primeiros socorros para emergências.',
        'requirements' => [
            ['type' => 'multiple_choice', 'title' => 'O que é um primeiro socorro?', 'points' => 10, 'options' => ['Tratamento médico completo', 'Atendimento imediato e provisório dado a uma vítima de acidente ou mal súbito', 'Medicação para dor', 'Cirurgia de emergência']],
            ['type' => 'text', 'title' => 'Quais são os objetivos dos primeiros socorros?', 'points' => 15],
            ['type' => 'text', 'title' => 'Descreva o procedimento correto para verificar os sinais vitais de uma vítima.', 'points' => 20],
            ['type' => 'text', 'title' => 'Como você deve agir em caso de hemorragia externa?', 'points' => 20],
            ['type' => 'text', 'title' => 'Descreva os procedimentos para tratamento de queimaduras de 1º, 2º e 3º grau.', 'points' => 20],
            ['type' => 'text', 'title' => 'O que é a posição de recuperação e quando deve ser usada?', 'points' => 15],
            ['type' => 'practical', 'title' => 'Demonstre como fazer uma bandagem triangular para imobilização de braço.', 'points' => 20],
            ['type' => 'practical', 'title' => 'Demonstre o procedimento de RCP (Ressuscitação Cardiopulmonar).', 'points' => 30]
        ]
    ],

    // ACAMPAMENTO I - Outdoor
    'camp_002' => [
        'type' => 'outdoor',
        'xp_reward' => 180,
        'duration_hours' => 8,
        'description' => 'Aprenda técnicas fundamentais de acampamento ao ar livre.',
        'requirements' => [
            ['type' => 'practical', 'title' => 'Participar de pelo menos um acampamento.', 'points' => 30],
            ['type' => 'text', 'title' => 'Listar 10 itens essenciais para um acampamento.', 'points' => 15],
            ['type' => 'practical', 'title' => 'Saber montar e desmontar uma barraca corretamente.', 'points' => 25],
            ['type' => 'text', 'title' => 'Quais são os critérios para escolher um bom local para acampamento?', 'points' => 15],
            ['type' => 'practical', 'title' => 'Saber acender uma fogueira de forma segura e utilizando apenas 2 fósforos.', 'points' => 25],
            ['type' => 'practical', 'title' => 'Preparar uma refeição simples ao ar livre.', 'points' => 20],
            ['type' => 'text', 'title' => 'Quais são as regras de segurança em um acampamento?', 'points' => 20]
        ]
    ],

    // VIDA DE CRISTO - Indoor/Text
    'bible_001' => [
        'type' => 'indoor',
        'xp_reward' => 150,
        'duration_hours' => 5,
        'description' => 'Estude a vida e os ensinamentos de Jesus Cristo.',
        'requirements' => [
            ['type' => 'text', 'title' => 'Desenhe ou encontre um mapa da Palestina nos tempos de Jesus. Marque as principais cidades e regiões mencionadas nos Evangelhos.', 'points' => 20],
            ['type' => 'multiple_choice', 'title' => 'Em qual cidade Jesus nasceu?', 'points' => 10, 'options' => ['Nazaré', 'Belém', 'Jerusalém', 'Cafarnaum']],
            ['type' => 'text', 'title' => 'Liste os 12 discípulos de Jesus e uma característica de cada um.', 'points' => 25],
            ['type' => 'text', 'title' => 'Descreva 3 parábolas de Jesus e seus significados.', 'points' => 25],
            ['type' => 'text', 'title' => 'Quais foram os 7 milagres de Jesus registrados no Evangelho de João?', 'points' => 20],
            ['type' => 'text', 'title' => 'Descreva os eventos da Semana Santa (Domingo de Ramos até a Ressurreição).', 'points' => 25],
            ['type' => 'text', 'title' => 'O que significa para você seguir os passos de Jesus hoje?', 'points' => 20]
        ]
    ],

    // AMIGO DA NATUREZA - Outdoor
    'nature_001' => [
        'type' => 'outdoor',
        'xp_reward' => 120,
        'duration_hours' => 4,
        'description' => 'Desenvolva amor e respeito pela natureza criada por Deus.',
        'requirements' => [
            ['type' => 'practical', 'title' => 'Fazer uma caminhada de pelo menos 3 km em um ambiente natural (parque, trilha, mata).', 'points' => 20],
            ['type' => 'practical', 'title' => 'Identificar e coletar folhas de pelo menos 10 árvores diferentes.', 'points' => 20],
            ['type' => 'text', 'title' => 'O que significa ser um bom mordomo da natureza?', 'points' => 15],
            ['type' => 'practical', 'title' => 'Observar e identificar pelo menos 5 pássaros diferentes na natureza.', 'points' => 15],
            ['type' => 'text', 'title' => 'Cite 3 textos bíblicos que falam sobre a criação e nossa responsabilidade de cuidar do meio ambiente.', 'points' => 15],
            ['type' => 'practical', 'title' => 'Participar de uma ação de limpeza ou plantio em sua comunidade ou igreja.', 'points' => 25]
        ]
    ],

    // EVANGELISMO PESSOAL - Indoor
    'miss_009' => [
        'type' => 'indoor',
        'xp_reward' => 140,
        'duration_hours' => 5,
        'description' => 'Aprenda a compartilhar sua fé de forma eficaz e amorosa.',
        'requirements' => [
            ['type' => 'text', 'title' => 'O que é evangelismo pessoal e qual sua importância?', 'points' => 15],
            ['type' => 'text', 'title' => 'Memorize pelo menos 5 textos bíblicos que possam ser usados no evangelismo (ex: João 3:16, Romanos 6:23).', 'points' => 20],
            ['type' => 'text', 'title' => 'Descreva os passos para dar um estudo bíblico simples.', 'points' => 20],
            ['type' => 'text', 'title' => 'O que você diria a uma pessoa que perguntasse \"Por que devo aceitar Jesus?\"', 'points' => 20],
            ['type' => 'practical', 'title' => 'Convidar pelo menos 3 pessoas para ir à igreja ou a uma reunião de jovens.', 'points' => 20],
            ['type' => 'practical', 'title' => 'Participar de uma ação evangelística com seu clube (distribuição de literaturas, projeto comunitário, etc).', 'points' => 25],
            ['type' => 'text', 'title' => 'Escreva seu testemunho pessoal de como conheceu Jesus (mínimo 10 linhas).', 'points' => 20]
        ]
    ],

    // ORDEM UNIDA - Outdoor/Practical
    'master_001' => [
        'type' => 'outdoor',
        'xp_reward' => 130,
        'duration_hours' => 4,
        'description' => 'Aprenda os comandos e movimentos básicos de ordem unida.',
        'requirements' => [
            ['type' => 'text', 'title' => 'Qual é a importância da ordem unida no clube de Desbravadores?', 'points' => 10],
            ['type' => 'practical', 'title' => 'Executar corretamente os seguintes comandos: Sentido, Descansar, À vontade, Cobrir.', 'points' => 20],
            ['type' => 'practical', 'title' => 'Executar corretamente as variações de Marche: Em frente marche, Alto, Passo acelerado marche.', 'points' => 20],
            ['type' => 'practical', 'title' => 'Executar corretamente os movimentos de direção: Direita volver, Esquerda volver, Meia volta volver.', 'points' => 20],
            ['type' => 'practical', 'title' => 'Participar de uma apresentação de ordem unida com seu clube ou unidade.', 'points' => 30],
            ['type' => 'text', 'title' => 'Descreva a posição correta de continência e quando ela deve ser feita.', 'points' => 15],
            ['type' => 'text', 'title' => 'Qual é a diferença entre ordem unida com e sem armas (bandeiras)?', 'points' => 10]
        ]
    ],

    // ORIENTAÇÃO - Outdoor
    'camp_003' => [
        'type' => 'outdoor',
        'xp_reward' => 160,
        'duration_hours' => 5,
        'description' => 'Aprenda a se orientar usando bússola, mapas e métodos naturais.',
        'requirements' => [
            ['type' => 'text', 'title' => 'O que é orientação e por que é importante saber se orientar?', 'points' => 10],
            ['type' => 'text', 'title' => 'Quais são os pontos cardeais e colaterais? Desenhe uma rosa dos ventos.', 'points' => 15],
            ['type' => 'practical', 'title' => 'Demonstrar como usar uma bússola para determinar direções.', 'points' => 25],
            ['type' => 'text', 'title' => 'Descreva 3 métodos de orientação sem bússola (sol, estrelas, natureza).', 'points' => 20],
            ['type' => 'practical', 'title' => 'Ler as coordenadas de um mapa topográfico e identificar curvas de nível.', 'points' => 20],
            ['type' => 'practical', 'title' => 'Completar uma caminhada orientada de pelo menos 1 km usando bússola e mapa.', 'points' => 30],
            ['type' => 'text', 'title' => 'O que fazer se você se perder em uma trilha ou mata?', 'points' => 15]
        ]
    ]
];

// HTML output
echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><title>Setup Specialty Requirements</title>";
echo "<style>body{font-family:system-ui;max-width:800px;margin:40px auto;padding:20px;background:#1a1a2e;color:#fff}";
echo "h1{color:#00d9ff}h2{color:#00ff88}.success{color:#00ff88}.error{color:#ff6b6b}.warning{color:#ffc107}";
echo "pre{background:#0d0d1a;padding:15px;border-radius:8px;overflow-x:auto}button{padding:15px 30px;font-size:1.1rem;";
echo "background:linear-gradient(135deg,#00d9ff,#00ff88);color:#1a1a2e;border:none;border-radius:8px;cursor:pointer;font-weight:bold}";
echo ".card{background:#252542;border-radius:10px;padding:20px;margin:15px 0}</style></head><body>";

echo "<h1>🎓 Setup Specialty Requirements</h1>";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<div class='card'>";
    echo "<h2>📋 Especialidades a Popular</h2>";
    echo "<ul>";
    foreach ($specialtyRequirements as $id => $spec) {
        $type = $spec['type'] === 'outdoor' ? '🏕️ Outdoor' : '📚 Indoor';
        $count = count($spec['requirements']);
        echo "<li><strong>{$id}</strong>: {$count} requisitos - {$type} - {$spec['xp_reward']} XP</li>";
    }
    echo "</ul>";
    echo "<p>Total: <strong>" . count($specialtyRequirements) . "</strong> especialidades com <strong>" .
        array_sum(array_map(fn($s) => count($s['requirements']), $specialtyRequirements)) . "</strong> requisitos.</p>";
    echo "</div>";

    echo "<form method='POST'><button type='submit'>🚀 Executar Setup</button></form>";
    echo "</body></html>";
    exit;
}

// Run setup
echo "<h2>🔄 Executando Setup...</h2>";
$inserted = 0;
$errors = [];

foreach ($specialtyRequirements as $specialtyId => $spec) {
    echo "<div class='card'>";
    echo "<h3>📘 {$specialtyId}</h3>";

    // Check if already exists
    $existing = $pdo->query("SELECT COUNT(*) as cnt FROM specialty_requirements WHERE specialty_id = '{$specialtyId}'")->fetch();

    if ($existing['cnt'] > 0) {
        echo "<p class='warning'>⚠️ Já existem {$existing['cnt']} requisitos - pulando...</p>";
        echo "</div>";
        continue;
    }

    // Insert requirements
    $stmt = $pdo->prepare("INSERT INTO specialty_requirements (specialty_id, order_num, type, title, description, options, points, is_required) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");

    $orderNum = 1;
    foreach ($spec['requirements'] as $req) {
        try {
            $options = isset($req['options']) ? json_encode($req['options'], JSON_UNESCAPED_UNICODE) : null;
            $stmt->execute([
                $specialtyId,
                $orderNum,
                $req['type'],
                $req['title'],
                $spec['description'],
                $options,
                $req['points']
            ]);
            $inserted++;
            $orderNum++;
        } catch (PDOException $e) {
            $errors[] = "{$specialtyId} req {$orderNum}: " . $e->getMessage();
        }
    }

    echo "<p class='success'>✅ Inseridos " . count($spec['requirements']) . " requisitos</p>";
    echo "</div>";
}

// Summary
echo "<div class='card'>";
echo "<h2>📊 Resumo</h2>";
echo "<p class='success'>✅ Total de requisitos inseridos: <strong>{$inserted}</strong></p>";

if (!empty($errors)) {
    echo "<p class='error'>❌ Erros encontrados:</p><pre>" . implode("\n", $errors) . "</pre>";
}

echo "</div>";

echo "<div class='card' style='background:#3d1a1a;border:2px solid #ff6b6b'>";
echo "<h2>⚠️ IMPORTANTE</h2>";
echo "<p><strong>Delete este arquivo após o uso!</strong></p>";
echo "<pre>rm public/setup-specialty-requirements.php</pre>";
echo "</div>";

echo "</body></html>";
