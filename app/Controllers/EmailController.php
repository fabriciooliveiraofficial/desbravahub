<?php
/**
 * Email Controller
 * 
 * Manages email composition, sending, and SMTP configuration.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\EmailService;

class EmailController
{
    /**
     * Email inbox/history
     */
    public function inbox(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        // Get email history (with error handling for missing table)
        try {
            $emails = db_fetch_all(
                "SELECT e.*, u.name as sender_name 
                 FROM composed_emails e 
                 LEFT JOIN users u ON e.sender_id = u.id 
                 WHERE e.tenant_id = ? 
                 ORDER BY e.created_at DESC
                 LIMIT 100",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            $emails = [];
        }

        // Get stats
        $stats = [
            'total' => count($emails),
            'sent' => count(array_filter($emails, fn($e) => $e['status'] === 'sent')),
            'draft' => count(array_filter($emails, fn($e) => $e['status'] === 'draft')),
            'failed' => count(array_filter($emails, fn($e) => $e['status'] === 'failed'))
        ];

        \App\Core\View::render('admin/email/inbox', [
            'tenant' => $tenant,
            'user' => $user,
            'emails' => $emails,
            'stats' => $stats,
            'pageTitle' => 'Caixa de Saída',
            'pageIcon' => 'inbox'
        ]);
    }

    /**
     * Compose new email
     */
    public function compose(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        // Get all users for recipient selection
        $users = db_fetch_all(
            "SELECT u.id, u.name, u.email, r.display_name as role 
             FROM users u 
             LEFT JOIN roles r ON u.role_id = r.id 
             WHERE u.tenant_id = ? AND u.status = 'active'
             ORDER BY u.name",
            [$tenant['id']]
        );

        // Get roles for bulk sending
        $roles = db_fetch_all(
            "SELECT DISTINCT r.name, r.display_name, COUNT(u.id) as user_count
             FROM roles r
             LEFT JOIN users u ON r.id = u.role_id AND u.status = 'active'
             WHERE r.tenant_id = ?
             GROUP BY r.id
             ORDER BY r.display_name",
            [$tenant['id']]
        );

        // Get units
        try {
            $units = db_fetch_all(
                "SELECT id, name FROM units WHERE tenant_id = ? ORDER BY name",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            $units = [];
        }

        // Check if SMTP is configured
        $smtpConfigured = $this->isSmtpConfigured($tenant['id']);

        \App\Core\View::render('admin/email/compose', [
            'tenant' => $tenant,
            'user' => $user,
            'users' => $users,
            'roles' => $roles,
            'units' => $units,
            'smtpConfigured' => $smtpConfigured,
            'pageTitle' => 'Compor Email',
            'pageIcon' => 'edit_note'
        ]);
    }

    /**
     * Send email
     */
    public function send(): void
    {
        $this->requireLeadership();

        $tenant = App::tenant();
        $user = App::user();

        $subject = trim($_POST['subject'] ?? '');
        $body = $_POST['body'] ?? '';
        $recipientType = $_POST['recipient_type'] ?? 'individual';
        $recipientType = $_POST['recipient_type'] ?? 'individual';
        
        // Map form inputs to generic recipient_ids based on type
        $recipientIds = [];
        switch ($recipientType) {
            case 'individual':
                $recipientIds = $_POST['recipients'] ?? [];
                break;
            case 'role':
                if (!empty($_POST['role_recipient'])) {
                    $recipientIds = [$_POST['role_recipient']];
                }
                break;
            case 'unit':
                if (!empty($_POST['unit_recipient'])) {
                    $recipientIds = [$_POST['unit_recipient']];
                }
                break;
            case 'all':
                // No IDs needed for 'all', but we can pass a dummy or handle it in resolveRecipients
                $recipientIds = ['all']; 
                break;
            default:
                $recipientIds = $_POST['recipient_ids'] ?? [];
        }
        $action = $_POST['action'] ?? 'send'; // send or draft

        // Validate
        if (empty($subject)) {
            $this->jsonError('Assunto é obrigatório');
            return;
        }

        if (empty($body)) {
            $this->jsonError('Corpo do email é obrigatório');
            return;
        }

        // Get recipient emails based on type
        $recipientEmails = $this->resolveRecipients($tenant['id'], $recipientType, $recipientIds);

        if (empty($recipientEmails) && $action === 'send') {
            $this->jsonError('Nenhum destinatário selecionado');
            return;
        }

        // Create email record
        $emailId = db_insert('composed_emails', [
            'tenant_id' => $tenant['id'],
            'sender_id' => $user['id'],
            'recipient_type' => $recipientType,
            'recipient_ids' => json_encode($recipientIds),
            'recipient_emails' => json_encode($recipientEmails),
            'subject' => $subject,
            'body' => $body,
            'status' => $action === 'draft' ? 'draft' : 'queued'
        ]);

        if ($action === 'draft') {
            $this->jsonSuccess('Rascunho salvo', ['email_id' => $emailId]);
            return;
        }

        // Send emails
        $sentCount = 0;
        $failedCount = 0;
        $emailService = EmailService::getInstance();

        // Update status to sending
        db_update('composed_emails', ['status' => 'sending'], 'id = ?', [$emailId]);

        $htmlBody = $this->wrapInTemplate($tenant, $body);

        foreach ($recipientEmails as $email) {
            try {
                $result = $emailService->send($email, $subject, $htmlBody);
                if ($result) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $failedCount++;
                error_log("Email send failed to {$email}: " . $e->getMessage());
            }
        }

        // Update final status
        $finalStatus = $failedCount === 0 ? 'sent' : ($sentCount > 0 ? 'sent' : 'failed');
        db_update('composed_emails', [
            'status' => $finalStatus,
            'sent_at' => date('Y-m-d H:i:s'),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount
        ], 'id = ?', [$emailId]);

        $this->jsonSuccess("Email enviado para {$sentCount} destinatários", [
            'sent' => $sentCount,
            'failed' => $failedCount
        ]);
    }

    /**
     * SMTP Settings page
     */
    public function settings(): void
    {
        $this->requireDirector();

        $tenant = App::tenant();
        $user = App::user();

        // Get current settings (with error handling for missing table)
        try {
            $settings = db_fetch_one(
                "SELECT * FROM tenant_smtp_settings WHERE tenant_id = ?",
                [$tenant['id']]
            );
        } catch (\Exception $e) {
            $settings = null;
        }

        // AUTO-FIX: Correct IMAP to SMTP and fix 'noreplay' typo
        if ($settings) {
            $updates = [];
            if (strpos($settings['smtp_host'], 'imap.') !== false) {
                 $updates['smtp_host'] = str_replace('imap.', 'smtp.', $settings['smtp_host']);
            }
            if (strpos($settings['smtp_user'], 'noreplay') !== false) {
                 $updates['smtp_user'] = str_replace('noreplay', 'noreply', $settings['smtp_user']);
            }
            
            if (!empty($updates)) {
                db_update('tenant_smtp_settings', $updates, 'id = ?', [$settings['id']]);
                // Refresh settings
                $settings = array_merge($settings, $updates);
            }
        }

        // Check if migration needed
        $migrationNeeded = false;
        try {
            db_fetch_one("SELECT 1 FROM tenant_smtp_settings LIMIT 1");
        } catch (\Exception $e) {
            $migrationNeeded = true;
        }

        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$migrationNeeded) {
            $this->saveSettings($tenant);
            return;
        }

        \App\Core\View::render('admin/email/settings', [
            'tenant' => $tenant,
            'user' => $user,
            'settings' => $settings,
            'migrationNeeded' => $migrationNeeded,
            'pageTitle' => 'Configuração SMTP',
            'pageIcon' => 'settings_ethernet'
        ]);
    }

    /**
     * Save SMTP settings
     */
    private function saveSettings(array $tenant): void
    {
        $smtpHost = trim($_POST['smtp_host'] ?? '');
        $smtpPort = (int) ($_POST['smtp_port'] ?? 587);
        $smtpUser = trim($_POST['smtp_user'] ?? '');
        $smtpPass = $_POST['smtp_pass'] ?? '';
        $fromEmail = trim($_POST['from_email'] ?? '');
        $fromName = trim($_POST['from_name'] ?? '');
        $encryption = $_POST['encryption'] ?? 'tls';

        // Validate
        if (empty($smtpHost) || empty($smtpUser) || empty($fromEmail)) {
            $_SESSION['flash_error'] = 'Preencha todos os campos obrigatórios';
            header('Location: ' . base_url($tenant['slug'] . '/admin/email/settings'));
            return;
        }

        // Encrypt password
        $encryptedPass = $this->encryptPassword($smtpPass);

        // Check if settings exist
        $existing = db_fetch_one(
            "SELECT id FROM tenant_smtp_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        $data = [
            'smtp_host' => $smtpHost,
            'smtp_port' => $smtpPort,
            'smtp_user' => $smtpUser,
            'smtp_pass_encrypted' => $encryptedPass,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'encryption' => $encryption,
            'is_verified' => 0
        ];

        if ($existing) {
            // Only update password if provided
            if (empty($smtpPass)) {
                unset($data['smtp_pass_encrypted']);
            }
            db_update('tenant_smtp_settings', $data, 'id = ?', [$existing['id']]);
        } else {
            $data['tenant_id'] = $tenant['id'];
            db_insert('tenant_smtp_settings', $data);
        }

        $_SESSION['flash_success'] = 'Configurações SMTP salvas';
        header('Location: ' . base_url($tenant['slug'] . '/admin/email/settings'));
    }

    /**
     * Test SMTP connection
     */
    public function testConnection(): void
    {
        $this->requireDirector();

        $tenant = App::tenant();
        $user = App::user();

        $settings = db_fetch_one(
            "SELECT * FROM tenant_smtp_settings WHERE tenant_id = ?",
            [$tenant['id']]
        );

        if (!$settings) {
            $this->jsonError('Configure o SMTP primeiro');
            return;
        }

        try {
            // Try to send test email
            $testEmail = $user['email'];
            $subject = 'Teste de Configuração SMTP - ' . $tenant['name'];
            $body = $this->wrapInTemplate($tenant, "
                <h2>✅ Configuração SMTP Funcionando!</h2>
                <p>Este é um email de teste para verificar que as configurações SMTP estão corretas.</p>
                <p>Se você recebeu este email, a configuração está funcionando corretamente.</p>
            ");

            $emailService = EmailService::getInstance();
            $result = $emailService->send($testEmail, $subject, $body);

            if ($result) {
                // Mark as verified
                db_update('tenant_smtp_settings', [
                    'is_verified' => 1,
                    'verified_at' => date('Y-m-d H:i:s')
                ], 'tenant_id = ?', [$tenant['id']]);

                $this->jsonSuccess('Conexão testada com sucesso! Email de teste enviado para ' . $testEmail);
            } else {
                // Fetch last error from logs
                $lastLog = db_fetch_one("SELECT error_message FROM email_logs WHERE tenant_id = ? ORDER BY id DESC LIMIT 1", [$tenant['id']]);
                $detailedError = $lastLog['error_message'] ?? 'Falha desconhecida no envio';
                $this->jsonError('Falha: ' . $detailedError);
            }
        } catch (\Exception $e) {
            $this->jsonError('Erro: ' . $e->getMessage());
        }
    }

    /**
     * Resolve recipients based on type
     */
    private function resolveRecipients(int $tenantId, string $type, array $ids): array
    {
        $emails = [];

        switch ($type) {
            case 'individual':
                if (!empty($ids)) {
                    $placeholders = implode(',', array_fill(0, count($ids), '?'));
                    $users = db_fetch_all(
                        "SELECT email FROM users WHERE id IN ({$placeholders}) AND tenant_id = ? AND status = 'active'",
                        [...$ids, $tenantId]
                    );
                    $emails = array_column($users, 'email');
                }
                break;

            case 'role':
                if (!empty($ids)) {
                    $roleName = $ids[0] ?? '';
                    $users = db_fetch_all(
                        "SELECT u.email FROM users u
                         JOIN roles r ON u.role_id = r.id
                         WHERE r.name = ? AND u.tenant_id = ? AND u.status = 'active'",
                        [$roleName, $tenantId]
                    );
                    $emails = array_column($users, 'email');
                }
                break;

            case 'unit':
                if (!empty($ids)) {
                    $unitId = $ids[0] ?? 0;
                    $users = db_fetch_all(
                        "SELECT u.email FROM users u
                         JOIN unit_members um ON u.id = um.user_id
                         WHERE um.unit_id = ? AND u.tenant_id = ? AND u.status = 'active'",
                        [$unitId, $tenantId]
                    );
                    $emails = array_column($users, 'email');
                }
                break;

            case 'all':
                $users = db_fetch_all(
                    "SELECT email FROM users WHERE tenant_id = ? AND status = 'active'",
                    [$tenantId]
                );
                $emails = array_column($users, 'email');
                break;
        }

        return array_unique($emails);
    }

    /**
     * Wrap email content in template
     */
    private function wrapInTemplate(array $tenant, string $content): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <link rel='preconnect' href='https://fonts.googleapis.com'>
            <link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
            <style>
                @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
                
                body {
                    margin: 0;
                    padding: 0;
                    background-color: #F8FAFC;
                    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                    -webkit-font-smoothing: antialiased;
                    color: #334155;
                    line-height: 1.6;
                }
                
                .wrapper {
                    width: 100%;
                    background-color: #F8FAFC;
                    padding: 40px 0;
                }
                
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 10px 15px -3px rgba(0, 0, 0, 0.05);
                }
                
                .header-accent {
                    height: 6px;
                    background: linear-gradient(90deg, #06b6d4, #3b82f6);
                    width: 100%;
                }
                
                .header {
                    text-align: center;
                    padding: 32px 32px 10px;
                    background-color: #ffffff;
                }
                
                .brand-logo {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 48px;
                    height: 48px;
                    background: #ECFEFF;
                    color: #06b6d4;
                    font-size: 24px;
                    border-radius: 12px;
                    margin-bottom: 16px;
                }
                
                .club-name {
                    font-size: 18px;
                    font-weight: 700;
                    color: #0F172A;
                    letter-spacing: -0.01em;
                    margin: 0;
                }
                
                .content {
                    padding: 20px 40px 40px;
                    color: #334155;
                    font-size: 16px;
                }
                
                .content h1 { font-size: 24px; font-weight: 700; color: #0F172A; margin-top: 0; margin-bottom: 16px; letter-spacing: -0.02em; }
                .content h2 { font-size: 20px; font-weight: 600; color: #1E293B; margin-top: 24px; margin-bottom: 12px; }
                .content p { margin-bottom: 16px; line-height: 1.7; }
                .content strong { color: #0F172A; font-weight: 600; }
                .content a { color: #0891b2; text-decoration: none; font-weight: 500; border-bottom: 1px dotted #0891b2; transition: all 0.2s; }
                .content a:hover { color: #06b6d4; border-bottom-style: solid; }
                
                .content ul { margin-bottom: 16px; padding-left: 20px; }
                .content li { margin-bottom: 8px; }
                
                .footer {
                    text-align: center;
                    padding: 30px 20px;
                    color: #94A3B8;
                    font-size: 13px;
                }
                
                .footer-link {
                    color: #64748B;
                    text-decoration: none;
                    margin: 0 8px;
                }
                .footer-link:hover { text-decoration: underline; color: #475569; }
                
                .badge {
                    display: inline-block;
                    padding: 4px 12px;
                    border-radius: 100px;
                    background: #F1F5F9;
                    color: #475569;
                    font-size: 12px;
                    font-weight: 600;
                    margin-top: 10px;
                }
                
                @media only screen and (max-width: 600px) {
                    .wrapper { padding: 10px; }
                    .content { padding: 20px; }
                }
            </style>
        </head>
        <body>
            <div class='wrapper'>
                <div class='email-container'>
                    <div class='header-accent'></div>
                    <div class='header'>
                        <div class='brand-logo'>⚡</div>
                        <div class='club-name'>{$tenant['name']}</div>
                        <!-- <div class='badge'>Notificação Oficial</div> -->
                    </div>
                    
                    <div class='content'>
                        {$content}
                    </div>
                </div>
                
                <div class='footer'>
                    <p style='margin-bottom: 10px;'>Enviado via <strong>DesbravaHub</strong></p>
                    <p>
                        <a href='#' class='footer-link'>Política de Privacidade</a> • 
                        <a href='#' class='footer-link'>Suporte</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Encrypt password for storage
     */
    private function encryptPassword(string $password): string
    {
        $key = env('APP_KEY', 'desbravahub_secret_key_2024');
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt password
     */
    public static function decryptPassword(string $encrypted): string
    {
        $key = env('APP_KEY', 'desbravahub_secret_key_2024');
        $data = base64_decode($encrypted);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Check if SMTP is configured
     */
    private function isSmtpConfigured(int $tenantId): bool
    {
        try {
            $settings = db_fetch_one(
                "SELECT id FROM tenant_smtp_settings WHERE tenant_id = ?",
                [$tenantId]
            );
            return $settings !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Require leadership role
     */
    private function requireLeadership(): void
    {
        $user = App::user();
        if (!$user) {
            header('Location: ' . base_url(App::tenant()['slug'] . '/login'));
            exit;
        }

        $role = $user['role_name'] ?? '';
        if (!in_array($role, ['admin', 'director', 'associate_director', 'counselor', 'instructor'])) {
            http_response_code(403);
            echo "Acesso negado";
            exit;
        }
    }

    /**
     * Require director role
     */
    private function requireDirector(): void
    {
        $user = App::user();
        if (!$user) {
            header('Location: ' . base_url(App::tenant()['slug'] . '/login'));
            exit;
        }

        $role = $user['role_name'] ?? '';
        if (!in_array($role, ['admin', 'director'])) {
            http_response_code(403);
            echo "Acesso negado. Apenas diretores podem configurar SMTP.";
            exit;
        }
    }

    /**
     * JSON success response
     */
    private function jsonSuccess(string $message, array $data = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message, ...$data]);
    }

    /**
     * JSON error response
     */
    private function jsonError(string $message): void
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
