<?php
/**
 * DesbravaHub Test Suite
 * 
 * Script de testes básicos para verificar a integridade do sistema.
 * Execute: php tests/run.php
 */

require_once dirname(__DIR__) . '/bootstrap/bootstrap.php';

echo "🧪 DesbravaHub - Suite de Testes\n";
echo "=================================\n\n";

$passed = 0;
$failed = 0;
$tests = [];

/**
 * Função auxiliar para registrar teste
 */
function test(string $name, callable $fn): void
{
    global $passed, $failed;

    echo "  • $name... ";

    try {
        $result = $fn();
        if ($result === true) {
            echo "✅ PASSOU\n";
            $passed++;
        } else {
            echo "❌ FALHOU: $result\n";
            $failed++;
        }
    } catch (\Exception $e) {
        echo "❌ ERRO: " . $e->getMessage() . "\n";
        $failed++;
    }
}

// ======================
// TESTES DE CONFIGURAÇÃO
// ======================

echo "📦 Configuração\n";

test('Arquivo .env existe', function () {
    return file_exists(BASE_PATH . '/.env') ?: 'Arquivo .env não encontrado';
});

test('config() funciona', function () {
    $appName = config('app.name');
    return $appName !== null ?: 'config() retornou null';
});

test('env() funciona', function () {
    $env = env('APP_ENV');
    return $env !== null ?: 'env() retornou null';
});

test('base_url() funciona', function () {
    $url = base_url('test');
    return strpos($url, 'test') !== false ?: 'base_url() não funciona corretamente';
});

// ======================
// TESTES DE BANCO DE DADOS
// ======================

echo "\n💾 Banco de Dados\n";

test('Conexão com banco', function () {
    try {
        $pdo = db_connect();
        return $pdo instanceof PDO ?: 'Conexão não retornou PDO';
    } catch (Exception $e) {
        return 'Erro: ' . $e->getMessage();
    }
});

test('Tabela tenants existe', function () {
    try {
        $result = db_fetch_one("SHOW TABLES LIKE 'tenants'");
        return $result !== null ?: 'Tabela tenants não encontrada';
    } catch (Exception $e) {
        return 'Erro: ' . $e->getMessage();
    }
});

test('Tabela users existe', function () {
    try {
        $result = db_fetch_one("SHOW TABLES LIKE 'users'");
        return $result !== null ?: 'Tabela users não encontrada';
    } catch (Exception $e) {
        return 'Erro: ' . $e->getMessage();
    }
});

// ======================
// TESTES DE HELPERS
// ======================

echo "\n🔧 Helpers\n";

test('Função __() existe', function () {
    return function_exists('__') ?: 'Função __() não existe';
});

test('Tradução funciona', function () {
    $trans = __('auth.login');
    return $trans !== 'auth.login' ?: 'Tradução não encontrada';
});

test('time_ago() funciona', function () {
    $result = time_ago(date('Y-m-d H:i:s'));
    return !empty($result) ?: 'time_ago() retornou vazio';
});

test('csrf_field() funciona', function () {
    $field = csrf_field();
    return strpos($field, 'csrf_token') !== false ?: 'csrf_field() não funciona';
});

// ======================
// TESTES DE ARQUIVOS
// ======================

echo "\n📁 Estrutura de Arquivos\n";

$requiredFiles = [
    'public/index.php',
    'bootstrap/bootstrap.php',
    'config/app.php',
    'config/database.php',
    'helpers/config.php',
    'helpers/env.php',
    'helpers/auth.php',
    'helpers/lang.php',
    'lang/pt-BR.php',
    'routes/web.php',
];

foreach ($requiredFiles as $file) {
    test("Arquivo $file existe", function () use ($file) {
        return file_exists(BASE_PATH . '/' . $file) ?: "Arquivo não encontrado";
    });
}

// ======================
// TESTES DE DIRETÓRIOS
// ======================

echo "\n📂 Diretórios\n";

$requiredDirs = [
    'storage',
    'storage/logs',
    'storage/proofs',
    'public/assets',
    'views',
    'app/Controllers',
    'app/Services',
    'app/Middleware',
];

foreach ($requiredDirs as $dir) {
    test("Diretório $dir existe", function () use ($dir) {
        return is_dir(BASE_PATH . '/' . $dir) ?: "Diretório não encontrado";
    });
}

// ======================
// RESULTADOS
// ======================

echo "\n=================================\n";
echo "📊 Resultados:\n";
echo "   ✅ Passou: $passed\n";
echo "   ❌ Falhou: $failed\n";
echo "   📈 Taxa de sucesso: " . round(($passed / ($passed + $failed)) * 100, 1) . "%\n\n";

if ($failed > 0) {
    echo "⚠️  Alguns testes falharam. Verifique os erros acima.\n\n";
    exit(1);
} else {
    echo "🎉 Todos os testes passaram!\n\n";
    exit(0);
}
