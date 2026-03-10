<?php
/**
 * Certificate Controller
 *
 * Handles certificate download (authenticated) and public hash verification.
 */

namespace App\Controllers;

use App\Core\App;
use App\Core\View;
use App\Services\CertificateService;

class CertificateController
{
    // ----------------------------------------------------------------
    // Private: download (authenticated)
    // ----------------------------------------------------------------

    /**
     * GET /{tenant}/certificados/{id}/download
     * Serves the PDF to the authenticated user who owns it.
     */
    public function download(array $params): void
    {
        $tenant = App::tenant();
        $user   = App::user();

        if (!$user || !$tenant) {
            http_response_code(403);
            echo 'Acesso negado';
            return;
        }

        $certId = (int) ($params['id'] ?? 0);
        $cert   = CertificateService::getById($certId, (int) $user['id'], (int) $tenant['id']);

        if (!$cert) {
            http_response_code(404);
            echo 'Certificado não encontrado';
            return;
        }

        // Build cert data for PDF rendering (needed if file was never cached)
        $certData = $this->buildCertData($cert);

        // Stream PDF (generates lazily if file_path missing)
        CertificateService::streamPdf($cert, $certData);
    }

    // ----------------------------------------------------------------
    // Public: verify by hash
    // ----------------------------------------------------------------

    /**
     * GET /v/{hash}
     * Public landing page — validates authenticity of a certificate.
     */
    public function verify(array $params): void
    {
        $hash = trim($params['hash'] ?? '');
        $cert = $hash ? CertificateService::verify($hash) : null;

        // Standalone page — no layout required
        extract(['cert' => $cert, 'hash' => $hash, 'valid' => $cert !== null]);
        require BASE_PATH . '/views/certificates/verify.php';
    }

    // ----------------------------------------------------------------
    // Member: gallery (Meus Diplomas)
    // ----------------------------------------------------------------

    /**
     * GET /{tenant}/meus-diplomas
     */
    public function myDiplomas(): void
    {
        $tenant = App::tenant();
        $user   = App::user();

        $certificates = CertificateService::getUserCertificates(
            (int) $user['id'],
            (int) $tenant['id']
        );

        View::render('pathfinder/diplomas/index', [
            'tenant'       => $tenant,
            'user'         => $user,
            'certificates' => $certificates,
            'page_title'   => 'Certificados',
        ], 'member');
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------

    private function buildCertData(array $cert): array
    {
        // Re-fetch display data for PDF rendering
        $full = CertificateService::verify($cert['certificate_hash']);

        if (!$full) return [];

        $typeLabel = $cert['assignment_type'] === 'specialty'
            ? 'a Especialidade'
            : 'o Programa de Aprendizagem';

        $completedDate = $full['completed_at']
            ? date('d \d\e F \d\e Y', strtotime($full['completed_at']))
            : date('d \d\e F \d\e Y');

        // Month translation
        $months = [
            'January'=>'janeiro','February'=>'fevereiro','March'=>'março',
            'April'=>'abril','May'=>'maio','June'=>'junho',
            'July'=>'julho','August'=>'agosto','September'=>'setembro',
            'October'=>'outubro','November'=>'novembro','December'=>'dezembro',
        ];
        foreach ($months as $en => $pt) {
            $completedDate = str_ireplace($en, $pt, $completedDate);
        }

        return [
            'member_name'    => $full['user_name'],
            'achievement'    => $full['achievement_name'],
            'type_label'     => $typeLabel,
            'xp'             => (int) ($full['xp_earned'] ?? 0),
            'icon'           => $full['achievement_icon'] ?? 'noto:blue-book',
            'completed_date' => $completedDate,
            'club_name'      => $full['club_name'],
            'tenant_slug'    => $full['tenant_slug'],
            'hash'           => $full['certificate_hash'],
        ];
    }
}
