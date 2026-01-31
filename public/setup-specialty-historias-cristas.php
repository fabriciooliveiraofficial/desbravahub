<?php
/**
 * Setup: Arte de Contar Histórias Cristãs - E-Learning Specialty
 * 
 * This script creates and populates the complete specialty with:
 * - Specialty metadata
 * - 7 Requirements/Steps
 * - Questions for interactive learning
 * 
 * IMPORTANT: Delete this file after running!
 * URL: /setup-specialty-historias-cristas.php
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
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
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("<h1>❌ Erro de conexão</h1><pre>{$e->getMessage()}</pre>");
}

// Specialty ID
$specialtyId = 'miss_001'; // Arte de Contar Histórias Cristãs

// Specialty metadata (will update existing or use for reference)
$specialtyMeta = [
    'id' => $specialtyId,
    'name' => 'Arte de Contar Histórias Cristãs',
    'category_id' => 'cat_missionary',
    'badge_icon' => '📖',
    'type' => 'indoor', // Interactive E-learning
    'difficulty' => 2,
    'duration_hours' => 8,
    'xp_reward' => 100,
    'description' => 'Aprenda a arte de contar histórias cristãs de forma envolvente e edificante, desenvolvendo habilidades para transmitir mensagens espirituais através de narrativas.'
];

// Requirements with questions
$requirements = [
    // Requirement 1: Sources and Stories
    [
        'order_num' => 1,
        'type' => 'mixed', // Has theory questions + practical
        'title' => 'Fontes de Histórias e Narrativas',
        'description' => 'Mencionar três fontes onde tenha encontrado material para história de cada uma das 5 categorias (Sagrada, Igreja, Natureza, Personagem, Parábola) e contar uma história de cada categoria.',
        'points' => 20,
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Qual livro é uma fonte recomendada para Histórias Sagradas?',
                'options' => ['Pérolas Esparsas', 'Trilhas da Natureza', 'Ben Carson', 'Nossa Herança'],
                'correct_answer' => 0
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'Qual fonte é adequada para histórias da Igreja Adventista?',
                'options' => ['Animais Heróis', 'Na Trilha dos Pioneiros', 'O Gato que Salvou o Trem', 'Lucy Miller'],
                'correct_answer' => 1
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'Qual livro é recomendado para histórias sobre a natureza?',
                'options' => ['A Mão de Deus ao Leme', 'De Vaqueiro a Advogado', 'Ensinado por um Tigre', 'Nossa Herança'],
                'correct_answer' => 2
            ],
            [
                'type' => 'text',
                'question' => 'Cite três fontes que você encontrou para histórias de personagens bíblicos ou cristãos:',
                'options' => null,
                'correct_answer' => null
            ],
            [
                'type' => 'file_upload',
                'question' => 'Grave ou envie um link de vídeo de você contando uma história de cada categoria (5 histórias no total):',
                'options' => null,
                'correct_answer' => null
            ]
        ]
    ],

    // Requirement 2: Stories for Different Ages
    [
        'order_num' => 2,
        'type' => 'practical',
        'title' => 'Contando Histórias para Diferentes Idades',
        'description' => 'Contar uma história para crianças de no máximo 5 anos (mínimo 3 min) e outra para crianças de 10-12 anos (mínimo 5 min).',
        'points' => 15,
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Para contar histórias para crianças de até 5 anos, onde é recomendado fazer essa atividade?',
                'options' => ['No culto de jovens', 'Na Escola Sabatina do Jardim ou Rol do Berço', 'Em um sermão principal', 'Na reunião administrativa'],
                'correct_answer' => 1
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'Qual é a duração mínima da história para crianças de 10-12 anos?',
                'options' => ['2 minutos', '3 minutos', '5 minutos', '10 minutos'],
                'correct_answer' => 2
            ],
            [
                'type' => 'file_upload',
                'question' => 'Envie um vídeo ou link de você contando a história para crianças pequenas (até 5 anos):',
                'options' => null,
                'correct_answer' => null
            ],
            [
                'type' => 'file_upload',
                'question' => 'Envie um vídeo ou link de você contando a história para crianças de 10-12 anos:',
                'options' => null,
                'correct_answer' => null
            ]
        ]
    ],

    // Requirement 3: Written Summary
    [
        'order_num' => 3,
        'type' => 'text',
        'title' => 'Resumo Escrito de História',
        'description' => 'Fazer um resumo por escrito de uma história que você vai contar, com tópicos das partes importantes.',
        'points' => 10,
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Por que é importante fazer um resumo por tópicos da história?',
                'options' => ['Para impressionar o público', 'Para não esquecer detalhes importantes e chegar ao objetivo', 'Para ler durante a apresentação', 'Para publicar na internet'],
                'correct_answer' => 1
            ],
            [
                'type' => 'text',
                'question' => 'Escreva o resumo por tópicos de uma história que você planeja contar. Inclua as partes importantes e o fundo moral:',
                'options' => null,
                'correct_answer' => null
            ]
        ]
    ],

    // Requirement 4: Modifying Stories
    [
        'order_num' => 4,
        'type' => 'multiple_choice',
        'title' => 'Modificando Histórias para Diferentes Situações',
        'description' => 'Explicar como modificar histórias para diferentes pessoas (1ª, 2ª, 3ª pessoa), faixas etárias, e como encurtar ou alongar histórias.',
        'points' => 15,
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Em qual pessoa você conta a história quando diz "Eu estava caminhando quando..."?',
                'options' => ['Primeira pessoa', 'Segunda pessoa', 'Terceira pessoa', 'Pessoa neutra'],
                'correct_answer' => 0
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'Como você deve encurtar uma história sem perder o sentido?',
                'options' => ['Remover a conclusão', 'Eliminar detalhes que não atrapalhem o objetivo', 'Falar mais rápido', 'Pular a introdução'],
                'correct_answer' => 1
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'O que você faz para alongar uma história de forma interessante?',
                'options' => ['Repetir várias vezes os mesmos pontos', 'Se apegar aos detalhes para causar mais impacto', 'Adicionar personagens inventados', 'Mudar o final'],
                'correct_answer' => 1
            ],
            [
                'type' => 'text',
                'question' => 'Explique como você adaptaria uma mesma história para crianças de 5 anos e para adolescentes de 15 anos:',
                'options' => null,
                'correct_answer' => null
            ]
        ]
    ],

    // Requirement 5: Objective and Climax
    [
        'order_num' => 5,
        'type' => 'multiple_choice',
        'title' => 'Objetivo e Clímax da História',
        'description' => 'Explicar por que é necessário um objetivo definido e como se obtém um bom clímax na história.',
        'points' => 15,
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Por que é necessário ter um objetivo definido ao contar uma história?',
                'options' => ['Para entreter o público', 'Sem objetivo a história não tem sentido e não toca os ouvintes', 'Para parecer mais profissional', 'Para cumprir tempo'],
                'correct_answer' => 1
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'O que uma boa história deve fazer com os ouvintes?',
                'options' => ['Fazer rir', 'Tocar e dar lições de moral para aplicar na vida', 'Fazê-los dormir', 'Causar medo'],
                'correct_answer' => 1
            ],
            [
                'type' => 'multiple_choice',
                'question' => 'Como se obtém um bom clímax na história?',
                'options' => ['Gritando no final', 'Construindo interesse gradual até o ponto alto', 'Contando o final primeiro', 'Usando muitos efeitos sonoros'],
                'correct_answer' => 1
            ],
            [
                'type' => 'text',
                'question' => 'Descreva qual é o objetivo da história que você resumiu e como você planeja construir o clímax:',
                'options' => null,
                'correct_answer' => null
            ]
        ]
    ],

    // Requirement 6: Missionary Story
    [
        'order_num' => 6,
        'type' => 'practical',
        'title' => 'História Missionária em Terra Estrangeira',
        'description' => 'Contar uma história sobre missionários em terra estrangeira, com duração mínima de 5 minutos.',
        'points' => 15,
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Qual é a duração mínima exigida para a história missionária?',
                'options' => ['2 minutos', '3 minutos', '5 minutos', '10 minutos'],
                'correct_answer' => 2
            ],
            [
                'type' => 'text',
                'question' => 'Qual missionário você escolheu para sua história e por quê?',
                'options' => null,
                'correct_answer' => null
            ],
            [
                'type' => 'file_upload',
                'question' => 'Envie um vídeo ou link de você contando a história missionária (mínimo 5 minutos):',
                'options' => null,
                'correct_answer' => null
            ]
        ]
    ],

    // Requirement 7: Health Story
    [
        'order_num' => 7,
        'type' => 'practical',
        'title' => 'História sobre Princípios de Saúde',
        'description' => 'Contar uma história que ensine princípios de saúde.',
        'points' => 10,
        'questions' => [
            [
                'type' => 'multiple_choice',
                'question' => 'Qual princípio de saúde você escolheu abordar em sua história?',
                'options' => ['Alimentação saudável', 'Exercício físico', 'Descanso adequado', 'Todos os anteriores são válidos'],
                'correct_answer' => 3
            ],
            [
                'type' => 'text',
                'question' => 'Descreva brevemente a história que você vai contar e qual princípio de saúde ela ensina:',
                'options' => null,
                'correct_answer' => null
            ],
            [
                'type' => 'file_upload',
                'question' => 'Envie um vídeo ou link de você contando a história sobre saúde:',
                'options' => null,
                'correct_answer' => null
            ]
        ]
    ]
];

// HTML Output
echo "<!DOCTYPE html><html lang='pt-BR'><head><meta charset='UTF-8'><title>Setup: Arte de Contar Histórias Cristãs</title>";
echo "<style>body{font-family:system-ui;max-width:900px;margin:40px auto;padding:20px;background:#1a1a2e;color:#fff}";
echo "h1{color:#00d9ff}h2{color:#00ff88;margin-top:30px}.success{color:#00ff88}.error{color:#ff6b6b}.warning{color:#ffc107}";
echo "pre{background:#0d0d1a;padding:15px;border-radius:8px;overflow-x:auto}button{padding:15px 30px;font-size:1.1rem;";
echo "background:linear-gradient(135deg,#00d9ff,#00ff88);color:#1a1a2e;border:none;border-radius:8px;cursor:pointer;font-weight:bold}";
echo ".card{background:#252542;border-radius:10px;padding:20px;margin:15px 0}.req{border-left:3px solid #00d9ff;padding-left:15px;margin:10px 0}";
echo ".q{background:#1a1a2e;padding:10px;border-radius:6px;margin:5px 0;font-size:0.9rem}</style></head><body>";

echo "<h1>📖 Setup: Arte de Contar Histórias Cristãs</h1>";
echo "<p>Especialidade E-Learning completa com 7 requisitos e perguntas interativas.</p>";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<div class='card'>";
    echo "<h2>📋 Requisitos a Popular</h2>";

    $totalQuestions = 0;
    foreach ($requirements as $req) {
        $qCount = count($req['questions']);
        $totalQuestions += $qCount;
        echo "<div class='req'>";
        echo "<strong>{$req['order_num']}. {$req['title']}</strong> ({$req['points']} pts)<br>";
        echo "<small>{$req['description']}</small><br>";
        echo "<small class='success'>{$qCount} perguntas/atividades</small>";
        echo "</div>";
    }

    echo "</div>";

    echo "<div class='card'>";
    echo "<p><strong>Especialidade:</strong> {$specialtyMeta['name']}</p>";
    echo "<p><strong>Categoria:</strong> Atividades Missionárias</p>";
    echo "<p><strong>Tipo:</strong> E-Learning Interativo</p>";
    echo "<p><strong>XP:</strong> {$specialtyMeta['xp_reward']}</p>";
    echo "<p><strong>Total de Requisitos:</strong> " . count($requirements) . "</p>";
    echo "<p><strong>Total de Perguntas:</strong> {$totalQuestions}</p>";
    echo "</div>";

    echo "<form method='POST'><button type='submit'>🚀 Executar Setup</button></form>";
    echo "</body></html>";
    exit;
}

// Execute setup
echo "<h2>🔄 Executando Setup...</h2>";

$inserted = 0;
$errors = [];

// Step 1: Insert/Update specialty requirements
echo "<div class='card'><h3>📘 Inserindo Requisitos...</h3>";

// Check if requirements already exist
$existing = $pdo->query("SELECT COUNT(*) as cnt FROM specialty_requirements WHERE specialty_id = '{$specialtyId}'")->fetch();
if ($existing['cnt'] > 0) {
    echo "<p class='warning'>⚠️ Já existem {$existing['cnt']} requisitos. Removendo antigos...</p>";
    $pdo->exec("DELETE FROM specialty_requirements WHERE specialty_id = '{$specialtyId}'");
}

// Insert requirements
$stmtReq = $pdo->prepare("INSERT INTO specialty_requirements (specialty_id, order_num, type, title, description, options, points, is_required) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");

foreach ($requirements as $req) {
    try {
        // Store questions in options field as JSON
        $questionsJson = json_encode($req['questions'], JSON_UNESCAPED_UNICODE);

        $stmtReq->execute([
            $specialtyId,
            $req['order_num'],
            $req['type'],
            $req['title'],
            $req['description'],
            $questionsJson,
            $req['points']
        ]);

        $inserted++;
        echo "<p class='success'>✅ Requisito {$req['order_num']}: {$req['title']}</p>";

    } catch (PDOException $e) {
        $errors[] = "Req {$req['order_num']}: " . $e->getMessage();
        echo "<p class='error'>❌ Erro no requisito {$req['order_num']}: {$e->getMessage()}</p>";
    }
}

echo "</div>";

// Step 2: Update specialty metadata in specialties table if exists
echo "<div class='card'><h3>📊 Atualizando Metadados...</h3>";

try {
    // Check if specialty exists in any specialties table
    $checkSpec = $pdo->query("SHOW TABLES LIKE 'specialties'")->fetch();
    if ($checkSpec) {
        $updateSql = "UPDATE specialties SET 
            description = :description,
            difficulty = :difficulty,
            duration_hours = :duration_hours,
            xp_reward = :xp_reward,
            type = :type
            WHERE id = :id";

        $stmt = $pdo->prepare($updateSql);
        $result = $stmt->execute([
            ':description' => $specialtyMeta['description'],
            ':difficulty' => $specialtyMeta['difficulty'],
            ':duration_hours' => $specialtyMeta['duration_hours'],
            ':xp_reward' => $specialtyMeta['xp_reward'],
            ':type' => $specialtyMeta['type'],
            ':id' => $specialtyId
        ]);

        if ($stmt->rowCount() > 0) {
            echo "<p class='success'>✅ Metadados atualizados na tabela specialties</p>";
        } else {
            echo "<p class='warning'>⚠️ Especialidade não encontrada na tabela specialties (usando JSON)</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p class='warning'>⚠️ Tabela specialties não existe ou erro: {$e->getMessage()}</p>";
}

echo "</div>";

// Summary
echo "<div class='card'>";
echo "<h2>📊 Resumo</h2>";
echo "<p class='success'>✅ Requisitos inseridos: <strong>{$inserted}</strong></p>";

if (!empty($errors)) {
    echo "<p class='error'>❌ Erros encontrados:</p><pre>" . implode("\n", $errors) . "</pre>";
}

echo "</div>";

// Instructions
echo "<div class='card' style='background:#1a3d1a;border:2px solid #00ff88'>";
echo "<h2>✅ Próximos Passos</h2>";
echo "<ol>";
echo "<li>Atribua a especialidade <strong>{$specialtyMeta['name']}</strong> a um desbravador</li>";
echo "<li>O desbravador acessa via Dashboard → Minhas Especialidades</li>";
echo "<li>O sistema carrega as perguntas progressivamente</li>";
echo "<li>Uploads de prova requerem aprovação do líder</li>";
echo "</ol>";
echo "</div>";

echo "<div class='card' style='background:#3d1a1a;border:2px solid #ff6b6b'>";
echo "<h2>⚠️ IMPORTANTE</h2>";
echo "<p><strong>Delete este arquivo após o uso!</strong></p>";
echo "<pre>rm public/setup-specialty-historias-cristas.php</pre>";
echo "</div>";

echo "</body></html>";
