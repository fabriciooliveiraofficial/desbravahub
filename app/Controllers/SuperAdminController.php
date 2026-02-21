<?php
/**
 * Super Admin Controller
 * 
 * Manage Clunes (Tenants), Users globally, and System Health.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\View;

class SuperAdminController
{
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
