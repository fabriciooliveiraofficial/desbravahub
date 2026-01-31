<?php
/**
 * API Controller
 * 
 * Endpoints públicos da API
 */

namespace App\Controllers;

use App\Core\App;

class ApiController
{
    /**
     * Informações da API
     */
    public function info(): void
    {
        $this->json([
            'name' => 'DesbravaHub API',
            'version' => config('app.version', '1.0.0'),
            'documentation' => base_url('api/docs'),
            'endpoints' => [
                'health' => '/health',
                'version' => '/{tenant}/api/version',
                'activities' => '/{tenant}/api/activities',
                'notifications' => '/{tenant}/api/notifications',
            ],
        ]);
    }

    /**
     * Documentação da API
     */
    public function docs(): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>DesbravaHub API - Documentação</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    background: #1a1a2e;
                    color: #e0e0e0;
                    padding: 40px 20px;
                    line-height: 1.6;
                }

                .container {
                    max-width: 900px;
                    margin: 0 auto;
                }

                h1 {
                    font-size: 2rem;
                    margin-bottom: 10px;
                    background: linear-gradient(90deg, #00d9ff, #00ff88);
                    -webkit-background-clip: text;
                    -webkit-text-fill-color: transparent;
                }

                h2 {
                    font-size: 1.3rem;
                    margin: 30px 0 15px;
                    color: #00d9ff;
                }

                h3 {
                    font-size: 1rem;
                    margin: 20px 0 10px;
                    color: #00ff88;
                }

                p {
                    color: #888;
                    margin-bottom: 15px;
                }

                .endpoint {
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 8px;
                    padding: 16px;
                    margin-bottom: 16px;
                }

                .method {
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 4px;
                    font-size: 0.8rem;
                    font-weight: 600;
                    margin-right: 10px;
                }

                .get {
                    background: #00d9ff;
                    color: #1a1a2e;
                }

                .post {
                    background: #00ff88;
                    color: #1a1a2e;
                }

                .path {
                    font-family: monospace;
                    color: #fff;
                }

                .desc {
                    color: #888;
                    margin-top: 8px;
                }

                code {
                    background: rgba(0, 0, 0, 0.3);
                    padding: 2px 6px;
                    border-radius: 4px;
                    font-family: monospace;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <h1>⚡ DesbravaHub API</h1>
                <p>API REST para integração com a plataforma DesbravaHub.</p>

                <h2>🔐 Autenticação</h2>
                <p>A autenticação é feita via sessão (cookies). Para APIs externas, utilize token de API.</p>

                <h2>📍 Endpoints Públicos</h2>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/health</span>
                    <p class="desc">Verificação de saúde do sistema</p>
                </div>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/api</span>
                    <p class="desc">Informações da API</p>
                </div>

                <h2>🎯 Atividades</h2>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/activities</span>
                    <p class="desc">Lista todas as atividades disponíveis</p>
                </div>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/activities/{id}</span>
                    <p class="desc">Detalhe de uma atividade</p>
                </div>

                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/{tenant}/api/activities/{id}/start</span>
                    <p class="desc">Inicia uma atividade</p>
                </div>

                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/{tenant}/api/proofs/submit</span>
                    <p class="desc">Envia comprovante de atividade</p>
                </div>

                <h2>📊 Progresso</h2>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/progress</span>
                    <p class="desc">Progresso do usuário atual</p>
                </div>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/leaderboard</span>
                    <p class="desc">Ranking dos membros</p>
                </div>

                <h2>🔔 Notificações</h2>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/notifications</span>
                    <p class="desc">Lista notificações do usuário</p>
                </div>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/notifications/unread</span>
                    <p class="desc">Notificações não lidas</p>
                </div>

                <div class="endpoint">
                    <span class="method post">POST</span>
                    <span class="path">/{tenant}/api/notifications/{id}/read</span>
                    <p class="desc">Marca notificação como lida</p>
                </div>

                <h2>🔄 Versões</h2>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/version</span>
                    <p class="desc">Versão atual do app para o tenant</p>
                </div>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/version/check</span>
                    <p class="desc">Verifica se há atualizações</p>
                </div>

                <h2>🚩 Feature Flags</h2>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/features</span>
                    <p class="desc">Lista feature flags ativas</p>
                </div>

                <div class="endpoint">
                    <span class="method get">GET</span>
                    <span class="path">/{tenant}/api/features/{key}</span>
                    <p class="desc">Verifica se uma feature está ativa</p>
                </div>
            </div>
        </body>

        </html>
        <?php
    }

    /**
     * List clubs for login selector
     */
    public function clubs(): void
    {
        $clubs = db_fetch_all(
            "SELECT id, name, slug FROM tenants WHERE status = 'active' ORDER BY name ASC"
        );

        $this->json(['clubs' => $clubs]);
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
    }
}
