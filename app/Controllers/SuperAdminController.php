<?php
/**
 * Super Admin Controller
 * 
 * Manage Clunes (Tenants), Users globally, and System Health.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Services\AuthService;
use App\Services\DatabaseMigrationService;

class SuperAdminController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    // =========================================================================
    // AUTHENTICATION
    // =========================================================================

    /**
     * Show standalone login page
     */
    public function showLogin(): void
    {
        // If already logged in as super admin, redirect to dashboard
        $user = App::user();
        if ($user && isset($user['is_superadmin']) && $user['is_superadmin'] == 1) {
            header('Location: /super-admin/dashboard');
            exit;
        }

        View::render('superadmin/login', [
            'pageTitle' => 'Super Admin Access',
        ], 'blank'); // No sidebar layout!
    }

    /**
     * Handle login attempt
     */
    public function login(): void
    {
        // Ensure we ALWAYS return JSON, even on unexpected errors
        header('Content-Type: application/json');

        try {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $this->jsonError('Email e senha são obrigatórios', 400);
                return;
            }

            $user = $this->authService->attemptSuperAdmin($email, $password);

            if (!$user) {
                $this->jsonError('Credenciais inválidas ou acesso não autorizado.', 401);
                return;
            }

            // Create session independently
            $token = $this->authService->createSession($user['id']);
            $this->authService->setAuthCookie($token);

            $this->json(['success' => true, 'redirect' => '/super-admin/dashboard']);
        } catch (\Throwable $e) {
            // Catch ANY error (PDO, missing columns, etc.) and return clean JSON
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erro interno do servidor: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        $token = $this->authService->getTokenFromRequest();

        if ($token) {
            $this->authService->destroySession($token);
            $this->authService->clearAuthCookie();
        }

        // Redirect to Super Admin login
        header('Location: /super-admin/login');
        exit;
    }

    /**
     * Dashboard Home
     */
    public function dashboard(): void
    {
        // Require Super Admin
        if (!App::isAuthenticated() || !isset(App::user()['is_superadmin']) || App::user()['is_superadmin'] != 1) {
            header('Location: /');
            exit;
        }

        $user = App::user();

        // Fetch KPIs
        $totalClubs = db_fetch_column("SELECT COUNT(*) FROM tenants");
        $activeClubs = db_fetch_column("SELECT COUNT(*) FROM tenants WHERE status = 'active'");
        $totalUsers = db_fetch_column("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL");

        View::render('superadmin/dashboard', [
            'user' => $user,
            'kpis' => [
                'total_clubs' => $totalClubs,
                'active_clubs' => $activeClubs,
                'total_users' => $totalUsers
            ],
            'pageTitle' => 'Painel Super Admin',
            'pageIcon' => 'shield_person'
        ], 'superadmin');
    }

    /**
     * View all Clubs (Tenants)
     */
    public function clubs(): void
    {
        $user = App::user();

        $clubs = db_fetch_all("
            SELECT t.*, (SELECT COUNT(*) FROM users u WHERE u.tenant_id = t.id AND u.deleted_at IS NULL) as member_count 
            FROM tenants t 
            ORDER BY t.created_at DESC
        ");

        View::render('superadmin/clubs', [
            'user' => $user,
            'clubs' => $clubs,
            'pageTitle' => 'Gerenciar Clubes',
            'pageIcon' => 'storefront'
        ], 'superadmin');
    }

    /**
     * View all Users globally
     */
    public function users(): void
    {
        $user = App::user();

        // Exclude soft-deleted but fetch all global
        $users = db_fetch_all("
            SELECT u.*, t.name as tenant_name, r.display_name as role_name 
            FROM users u
            JOIN tenants t ON u.tenant_id = t.id
            JOIN roles r ON u.role_id = r.id
            WHERE u.deleted_at IS NULL
            ORDER BY u.created_at DESC
            LIMIT 500
        ");

        View::render('superadmin/users', [
            'user' => $user,
            'users' => $users,
            'pageTitle' => 'Gerenciar Usuários Globais',
            'pageIcon' => 'group'
        ], 'superadmin');
    }
    /**
     * View the Super Scraper UI
     */
    public function scraper(): void
    {
        $user = App::user();
        
        View::render('superadmin/scraper', [
            'user' => $user,
            'pageTitle' => 'Super Scraper IA',
            'pageIcon' => 'smart_toy'
        ], 'superadmin');
    }

    /**
     * Save API Key to Session safely
     */
    public function saveApiKey(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['key'])) {
            $_SESSION['super_scraper_key'] = trim($data['key']);
            $this->json(['success' => true]);
        } else {
            $this->jsonError('Chave inválida');
        }
    }

    /**
     * Process Scrape via AI
     */
    public function processScrape(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $type = $data['type'] ?? 'text';
        $content = $data['content'] ?? '';
        
        if (empty($content)) {
            $this->jsonError('Conteúdo vazio.');
            return;
        }

        $apiKey = $_SESSION['super_scraper_key'] ?? '';
        
        // 1. Extraction Phase
        $rawText = '';
        if ($type === 'url') {
            $rawText = $this->extractTextFromUrl($content);
            if (!$rawText) {
                $this->jsonError('Não foi possível extrair o texto da URL. Bloqueio de CORS/Bot ou site inválido.');
                return;
            }
        } else {
            // Basic HTML strip for raw text input
            $rawText = strip_tags($content);
        }

        // Limit text size to avoid token explosion (approx 15000 chars roughly 3-4k tokens)
        $rawText = substr($rawText, 0, 15000);

        // 2. Normalization Phase (AI Hook)
        if (empty($apiKey)) {
            $this->jsonError('CHAVE DE API NECESSÁRIA! A normalização universal exige IA. Por favor, adicione sua chave OpenAI no painel acima.');
            return;
        }

        $result = $this->askAI($apiKey, $rawText);
        
        if ($result && isset($result['error'])) {
             $this->jsonError('Erro na API de IA: ' . $result['error']);
             return;
        }

        if (!$result) {
            $this->jsonError('A IA não conseguiu formatar os dados corretamente.');
            return;
        }

        $this->json(['success' => true, 'result' => $result]);
    }

    /**
     * Helper to fetch simplified text from a web page
     */
    private function extractTextFromUrl(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $html = curl_exec($ch);
        curl_close($ch);

        if (!$html) return '';

        // Primitive cleanup using DOMDocument
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        
        // Remove noise
        $tagsToRemove = ['script', 'style', 'nav', 'header', 'footer', 'svg', 'iframe'];
        foreach ($tagsToRemove as $tag) {
            $elements = $dom->getElementsByTagName($tag);
            for ($i = $elements->length; --$i >= 0;) {
                $node = $elements->item($i);
                $node->parentNode->removeChild($node);
            }
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        return $body ? preg_replace('/\s+/', ' ', trim($body->textContent)) : '';
    }

    /**
     * Call OpenAI API to transform the unstructured text into strict JSON
     */
    private function askAI(string $apiKey, string $text): ?array
    {
        $prompt = "Você é um especialista em estruturação de dados. Extraia TODOS os dados de Requisitos de Classes, Classes Avançadas e Especialidades de Desbravadores do texto abaixo. 
        Converta ESTRITAMENTE para o seguinte formato JSON, não responda com mais nada além do JSON (sem markdown de blocos de código, apenas texto bruto JSON):
        {
          \"classes\": [ { \"name\": \"Nome\", \"requirements\": [\"req 1\", \"req 2\"] } ],
          \"advancedClasses\": [ { \"name\": \"Nome\", \"requirements\": [\"req 1\", \"req 2\"] } ],
          \"leadershipClasses\": [ { \"name\": \"Nome\", \"requirements\": [\"req 1\"] } ],
          \"specialities\": {
            \"Nome Da Especialidade\": { \"level\": \"basic\", \"requirements\": [\"req 1\", \"req 2\"] }
          }
        }
        
        TEXTO FONTE:
        " . $text;

        $payload = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'Sua única função é extrair texto rústico e emitir um JSON perfeitamente formatado segundo as regras solicitadas. Retorne APENAS JSON válido, sem formato markdown ou texto extra.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.1
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $err = json_decode($response, true);
            return ['error' => $err['error']['message'] ?? 'Erro desconhecido da OpenAI'];
        }

        $data = json_decode($response, true);
        $content = $data['choices'][0]['message']['content'] ?? '';
        
        // Strip markdown code blocks just in case
        $content = preg_replace('/^```json\s*/m', '', $content);
        $content = preg_replace('/```\s*$/m', '', $content);
        $content = trim($content);

        return json_decode($content, true);
    }

    // =========================================================================
    // SUPPORT TICKET MANAGEMENT
    // =========================================================================

    /**
     * Support dashboard - list all tickets
     */
    public function supportDashboard(): void
    {
        $user = App::user();

        $status = $_GET['status'] ?? '';
        $category = $_GET['category'] ?? '';
        $priority = $_GET['priority'] ?? '';

        $sql = "SELECT t.*, tn.name as tenant_name, u.name as user_name
                FROM support_tickets t
                LEFT JOIN tenants tn ON t.tenant_id = tn.id
                LEFT JOIN users u ON t.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
        }
        if ($category) {
            $sql .= " AND t.category = ?";
            $params[] = $category;
        }
        if ($priority) {
            $sql .= " AND t.priority = ?";
            $params[] = $priority;
        }

        $sql .= " ORDER BY 
            CASE t.priority WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END,
            t.updated_at DESC";

        $tickets = db_fetch_all($sql, $params);

        // Stats
        $stats = [
            'open' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'open'"),
            'in_progress' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'in_progress'"),
            'waiting' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'waiting'"),
            'resolved' => db_fetch_column("SELECT COUNT(*) FROM support_tickets WHERE status = 'resolved'"),
        ];

        View::render('superadmin/support_dashboard', [
            'user' => $user,
            'tickets' => $tickets,
            'stats' => $stats,
            'pageTitle' => 'Central de Suporte',
            'pageIcon' => 'support_agent'
        ], 'superadmin');
    }

    /**
     * View single ticket
     */
    public function supportShow(array $params): void
    {
        $user = App::user();
        $ticketId = (int) $params['id'];

        $ticket = db_fetch_one(
            "SELECT t.*, tn.name as tenant_name, tn.slug as tenant_slug, u.name as user_name, u.email as user_email
             FROM support_tickets t
             LEFT JOIN tenants tn ON t.tenant_id = tn.id
             LEFT JOIN users u ON t.user_id = u.id
             WHERE t.id = ?",
            [$ticketId]
        );

        if (!$ticket) {
            http_response_code(404);
            echo "Ticket não encontrado";
            return;
        }

        $messages = db_fetch_all(
            "SELECT * FROM support_messages WHERE ticket_id = ? ORDER BY created_at ASC",
            [$ticketId]
        );

        $attachments = db_fetch_all(
            "SELECT * FROM support_attachments WHERE ticket_id = ?",
            [$ticketId]
        );

        View::render('superadmin/support_ticket', [
            'user' => $user,
            'ticket' => $ticket,
            'messages' => $messages,
            'attachments' => $attachments ?? [],
            'pageTitle' => 'Ticket #' . $ticket['id'],
            'pageIcon' => 'confirmation_number'
        ], 'superadmin');
    }

    /**
     * Reply to ticket
     */
    public function supportReply(array $params): void
    {
        $user = App::user();
        $ticketId = (int) $params['id'];

        $message = trim($_POST['message'] ?? '');
        $isInternal = isset($_POST['is_internal']) ? 1 : 0;
        $newStatus = $_POST['status'] ?? null;

        if (empty($message)) {
            $this->jsonError('Mensagem é obrigatória');
            return;
        }

        db_insert('support_messages', [
            'ticket_id' => $ticketId,
            'sender_type' => 'developer',
            'sender_id' => $user['id'],
            'sender_name' => $user['name'],
            'message' => $message,
            'is_internal' => $isInternal,
        ]);

        // Update ticket status
        $updates = ['updated_at' => date('Y-m-d H:i:s')];
        if ($newStatus) {
            $updates['status'] = $newStatus;
            if ($newStatus === 'resolved') {
                $updates['resolved_at'] = date('Y-m-d H:i:s');
            }
        } else {
            $updates['status'] = 'in_progress';
        }

        db_update('support_tickets', $updates, 'id = ?', [$ticketId]);

        $this->json(['success' => true, 'message' => 'Resposta enviada!']);
    }

    /**
     * Update ticket status
     */
    public function supportUpdateStatus(array $params): void
    {
        $ticketId = (int) $params['id'];
        $status = $_POST['status'] ?? '';

        $valid = ['open', 'in_progress', 'waiting', 'resolved', 'closed'];
        if (!in_array($status, $valid)) {
            $this->jsonError('Status inválido');
            return;
        }

        $updates = ['status' => $status];
        if ($status === 'resolved') {
            $updates['resolved_at'] = date('Y-m-d H:i:s');
        }

        db_update('support_tickets', $updates, 'id = ?', [$ticketId]);

        $this->json(['success' => true]);
    }

    // =========================================================================
    // DATABASE MIGRATION
    // =========================================================================

    /**
     * Show Migration Dashboard
     */
    public function migrationDashboard(): void
    {
        // Require Super Admin
        if (!App::isAuthenticated() || !isset(App::user()['is_superadmin']) || App::user()['is_superadmin'] != 1) {
            header('Location: /');
            exit;
        }

        View::render('superadmin/migration', [
            'pageTitle' => 'Migração de Banco de Dados',
            'user' => App::user(),
        ]);
    }

    /**
     * Export Database
     */
    public function exportDatabase(): void
    {
        // Require Super Admin
        if (!App::isAuthenticated() || !isset(App::user()['is_superadmin']) || App::user()['is_superadmin'] != 1) {
            $this->jsonError('Não autorizado', 403);
            return;
        }

        try {
            $migrationService = new DatabaseMigrationService();
            $sql = $migrationService->exportDatabase();

            $date = date('Y-m-d_H-i-s');
            $filename = "desbravahub_dump_{$date}.sql";

            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary"); 
            header("Content-disposition: attachment; filename=\"{$filename}\""); 
            echo $sql;
            exit;
        } catch (\Exception $e) {
            die("Erro na exportação: " . $e->getMessage());
        }
    }

    /**
     * Import Database
     */
    public function importDatabase(): void
    {
        // Require Super Admin
        if (!App::isAuthenticated() || !isset(App::user()['is_superadmin']) || App::user()['is_superadmin'] != 1) {
            $this->jsonError('Não autorizado', 403);
            return;
        }

        if (!isset($_FILES['dump_file']) || $_FILES['dump_file']['error'] !== UPLOAD_ERR_OK) {
            $this->jsonError('Nenhum arquivo de importação enviado ou ocorreu um erro no upload.');
            return;
        }

        $file = $_FILES['dump_file'];

        // Extra validation (check extension)
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if ($ext !== 'sql') {
            $this->jsonError('O arquivo deve ter a extensão .sql');
            return;
        }

        try {
            $migrationService = new DatabaseMigrationService();
            $success = $migrationService->importDatabase($file['tmp_name']);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Banco de Dados importado com sucesso!']);
            } else {
                $this->jsonError('Falha desconhecida ao importar.');
            }
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Helper to return standard JSON
     */
    private function json(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Helper to return JSON Error
     */
    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['success' => false, 'error' => $message]);
    }
}
