<?php
/**
 * SOS Controller
 * 
 * Handles emergency SOS alerts during events.
 * Captures geolocation and notifies all club leaders immediately.
 */

namespace App\Controllers;

use App\Core\App;
use App\Services\NotificationService;

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
        header('Content-Type: application/json');

        $user = App::user();
        $tenant = App::tenant();

        if (!$user || !$tenant) {
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
        if ($lat && $lng) {
            $mapLink = "https://www.google.com/maps?q={$lat},{$lng}";
            $locationText = "Lat: {$lat}, Lng: {$lng}";
        }

        // Log the SOS event
        try {
            db_query("
                INSERT INTO sos_alerts (user_id, tenant_id, latitude, longitude, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", [$user['id'], $tenant['id'], $lat, $lng]);
        } catch (\Exception $e) {
            // Table might not exist yet — create it on-the-fly
            try {
                db_query("
                    CREATE TABLE IF NOT EXISTS sos_alerts (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        user_id INT NOT NULL,
                        tenant_id INT NOT NULL,
                        latitude DECIMAL(10, 8),
                        longitude DECIMAL(11, 8),
                        resolved_at DATETIME NULL,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
                db_query("
                    INSERT INTO sos_alerts (user_id, tenant_id, latitude, longitude, created_at)
                    VALUES (?, ?, ?, ?, NOW())
                ", [$user['id'], $tenant['id'], $lat, $lng]);
            } catch (\Exception $e2) {
                // Log but continue — the alert notification is more important
                error_log('[SOS] DB Error: ' . $e2->getMessage());
            }
        }

        // Notify all leaders/admins in the club
        $notificationService = new NotificationService();

        // Get all admin/leader users in this tenant
        $leaders = db_fetch_all("
            SELECT u.id, u.name 
            FROM users u 
            JOIN user_roles ur ON u.id = ur.user_id AND ur.tenant_id = ?
            JOIN roles r ON ur.role_id = r.id
            WHERE r.slug IN ('admin', 'director', 'leader', 'secretary')
            AND u.is_active = 1
        ", [$tenant['id']]);

        $sosTitle = "🆘 ALERTA SOS - {$userName}";
        $sosMessage = "O desbravador {$userName} acionou o botão SOS!";
        if ($mapLink) {
            $sosMessage .= " 📍 Ver localização: {$mapLink}";
        }

        $alertCount = 0;
        foreach ($leaders as $leader) {
            try {
                $notificationService->send(
                    $leader['id'],
                    'sos_alert',
                    $sosTitle,
                    $sosMessage,
                    [
                        'priority' => 'urgent',
                        'channels' => ['toast', 'push', 'email'],
                        'data' => [
                            'url' => $mapLink ?: '/',
                            'sos' => true,
                            'user_name' => $userName,
                            'lat' => $lat,
                            'lng' => $lng
                        ]
                    ]
                );
                $alertCount++;
            } catch (\Exception $e) {
                error_log('[SOS] Notification failed for leader ' . $leader['id'] . ': ' . $e->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'message' => "SOS enviado! {$alertCount} líder(es) notificado(s).",
            'leaders_notified' => $alertCount
        ]);
    }
}
