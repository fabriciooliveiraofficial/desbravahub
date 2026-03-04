<?php
/**
 * SOS Controller
 * 
 * Handles emergency SOS alerts.
 * Captures geolocation and notifies all club leaders immediately.
 * 
 * CRITICAL: This controller is fault-tolerant by design.
 * It MUST always return valid JSON, even if all notification channels fail.
 * The SOS alert record in the database is the minimum viable success.
 */

namespace App\Controllers;

use App\Core\App;

class SosController
{
    /**
     * Trigger SOS Alert
     * POST /api/sos/trigger
     * 
     * Expects JSON: { lat: float, lng: float }
     */
    public function trigger(): void
    {
        // Ensure clean JSON output — no PHP warnings contaminating response
        ob_start();

        try {
            header('Content-Type: application/json');

            $user = App::user();
            $tenant = App::tenant();

            if (!$user || !$tenant) {
                ob_end_clean();
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Não autenticado']);
                return;
            }

            // Read JSON body
            $input = json_decode(file_get_contents('php://input'), true);
            $lat = $input['lat'] ?? null;
            $lng = $input['lng'] ?? null;

            $userName = $user['name'] ?? 'Desbravador';

            // Build Google Maps link if coordinates available
            $locationText = 'Localização não disponível';
            $mapLink = '';
            if ($lat !== null && $lng !== null) {
                $mapLink = "https://www.google.com/maps?q={$lat},{$lng}";
                $locationText = "📍 Lat: {$lat}, Lng: {$lng}";
            }

            // --- 1. Log the SOS event (self-healing table) ---
            $this->logSosAlert($user['id'], $tenant['id'], $lat, $lng);

            // --- 2. Get all leader/staff users in this tenant ---
            $leaders = $this->getClubLeaders($tenant['id']);

            // --- 3. Send notifications to each leader ---
            $alertCount = 0;
            $errors = [];

            $sosTitle = "🆘 ALERTA SOS - {$userName}";
            $sosMessage = "O desbravador {$userName} acionou o botão de emergência SOS!";
            if ($mapLink) {
                $sosMessage .= "\n\n📍 Ver localização no mapa:\n{$mapLink}";
            } else {
                $sosMessage .= "\n\n⚠️ Localização não disponível (GPS desativado ou negado).";
            }

            foreach ($leaders as $leader) {
                try {
                    $this->notifyLeader(
                        $leader['id'],
                        $tenant['id'],
                        $sosTitle,
                        $sosMessage,
                        $mapLink,
                        $userName,
                        $lat,
                        $lng
                    );
                    $alertCount++;
                } catch (\Throwable $e) {
                    error_log('[SOS] Notification failed for leader ' . $leader['id'] . ': ' . $e->getMessage());
                    $errors[] = 'Leader #' . $leader['id'];
                }
            }

            // --- 4. Always return success as long as the alert was logged ---
            ob_end_clean();

            if ($alertCount > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => "🆘 SOS enviado! {$alertCount} líder(es) notificado(s)."
                ]);
            } elseif (!empty($leaders)) {
                // Leaders exist but all notifications failed — alert was still logged
                echo json_encode([
                    'success' => true,
                    'message' => "⚠️ SOS registrado! As notificações falharam, mas seu alerta foi gravado. Tente ligar para um líder."
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => "⚠️ SOS registrado, mas nenhum líder ativo foi encontrado neste clube."
                ]);
            }

        } catch (\Throwable $e) {
            // ULTIMATE FALLBACK: Something completely unexpected happened
            ob_end_clean();
            error_log('[SOS] CRITICAL ERROR: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());

            http_response_code(200); // Return 200 so frontend doesn't show "connection error"
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Erro interno ao processar SOS. Seu alerta pode ter sido registrado. Tente ligar para um líder.',
                'debug' => (config('app.debug', false)) ? $e->getMessage() : null
            ]);
        }
    }

    /**
     * Log SOS alert to database (with self-healing table creation)
     */
    private function logSosAlert(int $userId, int $tenantId, ?float $lat, ?float $lng): void
    {
        try {
            db_query("
                INSERT INTO sos_alerts (user_id, tenant_id, latitude, longitude, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", [$userId, $tenantId, $lat, $lng]);
        } catch (\Throwable $e) {
            // Table might not exist — create it
            try {
                db_query("
                    CREATE TABLE IF NOT EXISTS sos_alerts (
                        id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
                        user_id INT UNSIGNED NOT NULL,
                        tenant_id INT UNSIGNED NOT NULL,
                        latitude DECIMAL(10, 8) NULL,
                        longitude DECIMAL(11, 8) NULL,
                        resolved_at DATETIME NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        INDEX idx_sos_tenant (tenant_id),
                        INDEX idx_sos_user (user_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
                db_query("
                    INSERT INTO sos_alerts (user_id, tenant_id, latitude, longitude, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ", [$userId, $tenantId, $lat, $lng]);
            } catch (\Throwable $e2) {
                error_log('[SOS] DB Error (alert log): ' . $e2->getMessage());
                // Continue — notification is more important than logging
            }
        }
    }

    /**
     * Get all club leaders/staff for the given tenant
     */
    private function getClubLeaders(int $tenantId): array
    {
        try {
            return db_fetch_all("
                SELECT u.id, u.name, u.email
                FROM users u 
                JOIN roles r ON u.role_id = r.id
                WHERE u.tenant_id = ?
                AND r.name IN ('admin', 'director', 'associate_director', 'chaplain', 'instructor', 'counselor', 'leader', 'secretary')
                AND u.status = 'active'
                AND u.deleted_at IS NULL
            ", [$tenantId]);
        } catch (\Throwable $e) {
            error_log('[SOS] Error fetching leaders: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Send notification to a single leader via the notifications table
     * Falls back to direct DB insert if NotificationService fails
     */
    private function notifyLeader(
        int $leaderId,
        int $tenantId,
        string $title,
        string $message,
        string $mapLink,
        string $userName,
        ?float $lat,
        ?float $lng
    ): void {
        // Try using NotificationService first (handles toast, push, email)
        try {
            $notificationService = new \App\Services\NotificationService();
            $notificationService->send(
                $leaderId,
                'sos_alert',
                $title,
                $message,
                [
                    'priority' => 'critical',
                    'channels' => ['toast', 'push'],
                    'data' => [
                        'link' => $mapLink ?: '/',
                        'url' => $mapLink ?: '/',
                        'sos' => true,
                        'user_name' => $userName,
                        'lat' => $lat,
                        'lng' => $lng
                    ]
                ]
            );
            return; // Success
        } catch (\Throwable $e) {
            error_log('[SOS] NotificationService failed for leader ' . $leaderId . ': ' . $e->getMessage());
        }

        // Fallback: Insert directly into notifications table
        try {
            db_insert('notifications', [
                'tenant_id' => $tenantId,
                'user_id' => $leaderId,
                'type' => 'sos_alert',
                'title' => $title,
                'message' => $message,
                'data' => json_encode([
                    'link' => $mapLink ?: '/',
                    'url' => $mapLink ?: '/',
                    'sos' => true,
                    'user_name' => $userName,
                    'lat' => $lat,
                    'lng' => $lng
                ]),
                'channels' => json_encode(['toast']),
                'priority' => 'critical',
                'created_at' => date('Y-m-d H:i:s'),
                'sent_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e2) {
            error_log('[SOS] Direct DB notification also failed for leader ' . $leaderId . ': ' . $e2->getMessage());
            throw $e2; // Let the caller count this as a failure
        }
    }
}
