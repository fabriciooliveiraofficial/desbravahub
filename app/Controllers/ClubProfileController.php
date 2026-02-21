<?php
/**
 * Club Profile Controller
 * 
 * Manages the public identity, SEO, and growth features of a Club (Tenant).
 */

namespace App\Controllers;

use App\Core\View;
use App\Core\App;

class ClubProfileController
{
    /**
     * Show the edit profile form
     */
    public function edit(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        $user = App::user();

        // Ensure club_profiles record exists
        $profile = db_fetch_one("SELECT * FROM club_profiles WHERE tenant_id = ?", [$tenant['id']]);

        // If not, pre-fill with tenant data
        if (!$profile) {
            $profile = [
                'display_name' => $tenant['name'],
                'slug' => $tenant['slug'],
                'logo_url' => $tenant['logo_url'],
                'cover_image_url' => '',
                'meeting_address' => '',
                'meeting_time' => '',
                'social_instagram' => '',
                'social_whatsapp_group' => '',
                'welcome_message' => 'Bem-vindo ao nosso clube!',
                'leaders_json' => '[]',
                'seo_meta_description' => ''
            ];
        }

        // Fetch Growth Tools Data
        $growth = db_fetch_one("SELECT * FROM club_growth_tools WHERE tenant_id = ?", [$tenant['id']]);

        View::render('admin/club_profile', [
            'tenant' => $tenant,
            'user' => $user,
            'profile' => $profile,
            'growth' => $growth,
            'pageTitle' => 'Perfil do Clube & Crescimento',
            'pageIcon' => 'storefront'
        ]);
    }

    /**
     * Update the club profile
     */
    public function update(): void
    {
        $this->requireAdmin();

        $tenant = App::tenant();
        
        // Allowed fields
        $data = [
            'display_name' => $_POST['display_name'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'logo_url' => $_POST['logo_url'] ?? '',
            'cover_image_url' => $_POST['cover_image_url'] ?? '',
            'meeting_address' => $_POST['meeting_address'] ?? '',
            'meeting_time' => $_POST['meeting_time'] ?? '',
            'social_instagram' => $_POST['social_instagram'] ?? '',
            'social_whatsapp_group' => $_POST['social_whatsapp_group'] ?? '',
            'welcome_message' => $_POST['welcome_message'] ?? '',
            'seo_meta_description' => $_POST['seo_meta_description'] ?? ''
        ];

        // Ensure slug is unique but ignoring own tenant
        $slugCheck = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE slug = ? AND tenant_id != ?", [$data['slug'], $tenant['id']]);
        if ($slugCheck) {
            $this->jsonError('Este slug já está sendo usado por outro clube.');
            return;
        }

        // Ensure formatting (leaders_json could be built from an array in the request)
        if (isset($_POST['leaders']) && is_array($_POST['leaders'])) {
            $data['leaders_json'] = json_encode(array_values($_POST['leaders']));
        }

        $exists = db_fetch_one("SELECT tenant_id FROM club_profiles WHERE tenant_id = ?", [$tenant['id']]);

        try {
            if ($exists) {
                db_update('club_profiles', $data, 'tenant_id = ?', [$tenant['id']]);
            } else {
                $data['tenant_id'] = $tenant['id'];
                db_insert('club_profiles', $data);
            }
            
            // Generate QR Code if it doesn't exist
            $this->generateOfflineCacheQrCode($tenant['id'], $data['slug']);

            $this->json([
                'success' => true,
                'message' => 'Perfil atualizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            $this->jsonError('Erro ao atualizar perfil: ' . $e->getMessage());
        }
    }

    /**
     * Manually trigger QR Code generation
     */
    public function generateQRCode(): void
    {
        $this->requireAdmin();
        $tenant = App::tenant();
        
        $profile = db_fetch_one("SELECT slug FROM club_profiles WHERE tenant_id = ?", [$tenant['id']]);
        if (!$profile || empty($profile['slug'])) {
            $this->jsonError('Configure o slug do perfil primeiro.');
            return;
        }

        try {
            $path = $this->generateOfflineCacheQrCode($tenant['id'], $profile['slug'], true);
            $this->json([
                'success' => true,
                'path' => $path,
                'message' => 'QR Code gerado com sucesso!'
            ]);
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * Generate QR Code via external API and save locally (zero dependency)
     */
    private function generateOfflineCacheQrCode(int $tenantId, string $slug, bool $force = false): string
    {
        $growth = db_fetch_one("SELECT * FROM club_growth_tools WHERE tenant_id = ?", [$tenantId]);
        
        if ($growth && $growth['qr_code_path'] && !$force) {
            return $growth['qr_code_path'];
        }

        $appUrl = rtrim(config('app.base_url'), '/');
        // Final destination URL for the QR code
        $targetUrl = "{$appUrl}/c/{$slug}?utm_source=qr_offline";
        
        $width = 500;
        $height = 500;
        $apiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$width}x{$height}&data=" . urlencode($targetUrl);

        $uploadDir = BASE_PATH . '/public/uploads/qrcodes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = "qr_club_{$tenantId}_" . time() . ".png";
        $filePath = $uploadDir . $fileName;

        $imageContent = @file_get_contents($apiUrl);
        if ($imageContent === false) {
            throw new \Exception("Failed to generate QR Code from external service.");
        }

        file_put_contents($filePath, $imageContent);

        $dbPath = '/uploads/qrcodes/' . $fileName;

        if ($growth) {
            db_update('club_growth_tools', ['qr_code_path' => $dbPath], 'tenant_id = ?', [$tenantId]);
        } else {
            db_insert('club_growth_tools', [
                'tenant_id' => $tenantId,
                'qr_code_path' => $dbPath,
                'campaign_source' => 'qr_offline',
                'visits_count' => 0
            ]);
        }

        return $dbPath;
    }

    private function requireAdmin(): void
    {
        $user = App::user();
        $roleName = $user['role_name'] ?? '';

        if (!in_array($roleName, ['admin', 'director', 'associate_director'])) {
            http_response_code(403);
            echo "Acesso negado";
            exit;
        }
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    private function jsonError(string $message, int $code = 400): void
    {
        http_response_code($code);
        $this->json(['error' => $message]);
    }
}
