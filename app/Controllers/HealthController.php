<?php
/**
 * Health Check Controller
 * 
 * Endpoints para verificação de saúde do sistema.
 */

namespace App\Controllers;

class HealthController
{
    /**
     * Health check básico
     */
    public function index(): void
    {
        $checks = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'version' => config('app.version', '1.0.0'),
            'environment' => env('APP_ENV', 'production'),
            'php_version' => PHP_VERSION,
        ];

        // Verificar banco de dados
        try {
            $pdo = db_connect();
            $pdo->query('SELECT 1');
            $checks['database'] = 'connected';
        } catch (\Exception $e) {
            $checks['database'] = 'error';
            $checks['status'] = 'degraded';
        }

        // Verificar diretórios de escrita
        $writableDirs = [
            'storage' => BASE_PATH . '/storage',
            'logs' => BASE_PATH . '/storage/logs',
            'proofs' => BASE_PATH . '/storage/proofs',
        ];

        $checks['writable'] = [];
        foreach ($writableDirs as $name => $path) {
            $checks['writable'][$name] = is_writable($path);
            if (!is_writable($path)) {
                $checks['status'] = 'degraded';
            }
        }

        $this->json($checks);
    }

    /**
     * Health check detalhado (para admin)
     */
    public function detailed(): void
    {
        if (!is_admin()) {
            $this->jsonError('Acesso negado', 403);
            return;
        }

        $checks = [
            'status' => 'ok',
            'timestamp' => date('c'),
            'server' => [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
            ],
            'extensions' => [
                'pdo_mysql' => extension_loaded('pdo_mysql'),
                'mbstring' => extension_loaded('mbstring'),
                'json' => extension_loaded('json'),
                'openssl' => extension_loaded('openssl'),
            ],
        ];

        // Database stats
        try {
            $pdo = db_connect();

            $checks['database'] = [
                'status' => 'connected',
                'tables' => $this->getTableCount($pdo),
            ];

            // Estatísticas básicas
            $checks['stats'] = [
                'tenants' => db_fetch_column("SELECT COUNT(*) FROM tenants"),
                'users' => db_fetch_column("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL"),
                'activities' => db_fetch_column("SELECT COUNT(*) FROM activities"),
            ];
        } catch (\Exception $e) {
            $checks['database'] = ['status' => 'error', 'message' => $e->getMessage()];
            $checks['status'] = 'error';
        }

        $this->json($checks);
    }

    /**
     * Ping simples
     */
    public function ping(): void
    {
        echo 'pong';
    }

    private function getTableCount(\PDO $pdo): int
    {
        $stmt = $pdo->query("SHOW TABLES");
        return $stmt->rowCount();
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }
}
