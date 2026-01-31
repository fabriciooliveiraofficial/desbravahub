<?php
/**
 * Email Service
 * 
 * Handles SMTP email sending with logging and learning notifications.
 */

namespace App\Services;

use App\Core\App;

class EmailService
{
    private static ?self $instance = null;

    private string $smtpHost;
    private int $smtpPort;
    private string $smtpUser;
    private string $smtpPass;
    private string $fromEmail;
    private string $fromName;
    private bool $smtpEnabled;

    private function __construct()
    {
        $this->smtpHost = env('SMTP_HOST', 'smtp.gmail.com');
        $this->smtpPort = (int) env('SMTP_PORT', 587);
        $this->smtpUser = env('SMTP_USER', '');
        $this->smtpPass = env('SMTP_PASS', '');
        $this->fromEmail = env('MAIL_FROM', 'noreply@desbravahub.com');
        $this->fromName = env('MAIL_FROM_NAME', 'DesbravaHub');
        $this->smtpEnabled = !empty($this->smtpUser) && !empty($this->smtpPass);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Send an email
     */
    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null): bool
    {
        $tenantId = App::tenantId();
        $tenantId = App::tenantId();
        $userId = null;

        // Load Tenant SMTP Settings
        try {
            $settings = db_fetch_one("SELECT * FROM tenant_smtp_settings WHERE tenant_id = ?", [$tenantId]);
            if ($settings) {
                $this->smtpHost = $settings['smtp_host'];
                $this->smtpPort = (int)$settings['smtp_port'];
                $this->smtpUser = $settings['smtp_user'];
                
                // Decrypt password
                if (!empty($settings['smtp_pass_encrypted'])) {
                    $this->smtpPass = \App\Controllers\EmailController::decryptPassword($settings['smtp_pass_encrypted']);
                }
                
                $this->fromEmail = $settings['smtp_user']; // Use SMTP user as from email usually
                //$this->fromName = ... ? Keep default or add to DB.
                
                $this->smtpEnabled = !empty($this->smtpHost) && !empty($this->smtpUser) && !empty($this->smtpPass);
            }
        } catch (\Exception $e) {
            // Ignore DB errors, stick to env defaults
        }

        // Try to find user by email
        try {
            $user = db_fetch_one("SELECT id FROM users WHERE email = ? AND tenant_id = ?", [$to, $tenantId]);
            if ($user) {
                $userId = $user['id'];
            }

            // Log the email attempt
            $logId = db_insert('email_logs', [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'to_email' => $to,
                'subject' => $subject,
                'template' => null,
                'status' => 'queued',
            ]);
        } catch (\Exception $e) {
            $logId = null;
        }

        try {
            // Try SMTP first if configured
            if ($this->smtpEnabled && class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
                $result = $this->sendWithPHPMailer($to, $subject, $htmlBody, $textBody);
            } else {
                $result = $this->sendViaMail($to, $subject, $htmlBody);
            }

            if ($logId) {
                if ($result) {
                    db_update('email_logs', [
                        'status' => 'sent',
                        'sent_at' => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$logId]);
                } else {
                    db_update('email_logs', [
                        'status' => 'failed',
                        'error_message' => 'Send failed',
                    ], 'id = ?', [$logId]);
                }
            }
            return $result;

        } catch (\Exception $e) {
            if ($logId) {
                db_update('email_logs', [
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ], 'id = ?', [$logId]);
            }
            $logFile = '/var/www/html/debug_email_errors.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($logFile, "[$timestamp] Email Service Error (Send): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
            error_log("Email Service Error (Send): " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Send using PHPMailer (SMTP)
     */
    private function sendWithPHPMailer(string $to, string $subject, string $htmlBody, ?string $textBody): bool
    {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            // Enable verbose debug output to error_log
            // $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER; 
            // $mail->Debugoutput = 'error_log';

            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUser;
            $mail->Password = $this->smtpPass;
            $mail->SMTPSecure = $this->smtpPort === 465 ? 'ssl' : 'tls';
            $mail->Port = $this->smtpPort;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            if ($textBody) {
                $mail->AltBody = $textBody;
            }

            return $mail->send();
        } catch (\Exception $e) {
            $logFile = '/var/www/html/debug_email_errors.log';
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($logFile, "[$timestamp] PHPMailer Error: " . $e->getMessage() . "\n", FILE_APPEND);
            error_log("PHPMailer Error: " . $e->getMessage());
            throw $e; // Re-throw to be caught by public send()
        }
    }

    /**
     * Send via PHP mail() function (fallback)
     */
    private function sendViaMail(string $to, string $subject, string $htmlBody): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
        ];

        return mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    }

    /**
     * Send learning notification email
     */
    public static function sendNotification(string $toEmail, string $toName, array $notification, string $tenantSlug): bool
    {
        $service = self::getInstance();
        $subject = $notification['title'];
        $htmlBody = self::buildNotificationEmail($toName, $notification, $tenantSlug);

        return $service->send($toEmail, $subject, $htmlBody);
    }

    /**
     * Build HTML email template for notifications
     */
    private static function buildNotificationEmail(string $userName, array $notification, string $tenantSlug): string
    {
        $link = $notification['link'] ? base_url($tenantSlug . '/' . $notification['link']) : '';
        $icon = $notification['icon'] ?? '📌';
        $ctaButton = $link ? '<div style="text-align:center;margin:32px 0;"><a href="' . $link . '" style="display:inline-block;background:linear-gradient(135deg,#00d9ff,#00ff88);color:#1a1a2e;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;">Ver Detalhes</a></div>' : '';

        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background-color:#1a1a2e;">
<table width="100%" style="max-width:600px;margin:0 auto;padding:40px 20px;">
<tr><td style="background:linear-gradient(135deg,#16213e,#1a1a2e);border-radius:16px;padding:40px;">
<div style="text-align:center;font-size:48px;margin-bottom:24px;">{$icon}</div>
<h1 style="color:#00d9ff;text-align:center;margin:0 0 16px;font-size:24px;">{$notification['title']}</h1>
<p style="color:#e0e0e0;font-size:16px;margin:0 0 24px;">Olá, <strong>{$userName}</strong>!</p>
<div style="background:rgba(0,217,255,0.1);border-left:4px solid #00d9ff;padding:16px;border-radius:8px;margin-bottom:24px;">
<p style="color:#e0e0e0;margin:0;font-size:16px;line-height:1.6;">{$notification['message']}</p>
</div>
{$ctaButton}
<hr style="border:none;border-top:1px solid rgba(255,255,255,0.1);margin:32px 0;">
<p style="color:#888;font-size:12px;text-align:center;margin:0;">Email automático do DesbravaHub</p>
</td></tr></table>
</body></html>
HTML;
    }

    /**
     * Get email logs for tenant
     */
    public function getLogs(int $limit = 100): array
    {
        $tenantId = App::tenantId();

        return db_fetch_all(
            "SELECT el.*, u.name as user_name 
             FROM email_logs el
             LEFT JOIN users u ON el.user_id = u.id
             WHERE el.tenant_id = ?
             ORDER BY el.created_at DESC
             LIMIT ?",
            [$tenantId, $limit]
        );
    }
}

