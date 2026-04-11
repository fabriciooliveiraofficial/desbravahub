<?php
/**
 * Certificate Service
 *
 * Generates, caches and verifies PDF certificates for completed
 * specialties and learning programs.
 *
 * Dependencies: dompdf/dompdf ^3.0 (composer require dompdf/dompdf)
 */

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

class CertificateService
{
    private const CERT_DIR = '/storage/app/certificates';

    // ----------------------------------------------------------------
    // Public API
    // ----------------------------------------------------------------

    /**
     * Issue (or retrieve cached) certificate for a completed specialty assignment.
     *
     * @param  int $assignmentId  specialty_assignments.id
     * @param  int $tenantId
     * @return int|null  issued_certificates.id, null on failure
     */
    public static function generateForSpecialty(int $assignmentId, int $tenantId): ?int
    {
        // Already issued?
        $existing = db_fetch_one(
            "SELECT id FROM issued_certificates
             WHERE tenant_id = ? AND assignment_type = 'specialty' AND assignment_id = ?",
            [$tenantId, $assignmentId]
        );
        if ($existing) return (int) $existing['id'];

        // Load assignment data
        $row = db_fetch_one("
            SELECT sa.id, sa.user_id, sa.specialty_id, sa.completed_at, sa.xp_earned,
                   s.name  AS specialty_name, s.xp_reward, s.icon, s.type,
                   u.name  AS user_name, u.avatar_url,
                   t.name  AS tenant_name, t.slug  AS tenant_slug
            FROM specialty_assignments sa
            JOIN specialties         s  ON sa.specialty_id = s.id
            JOIN users               u  ON sa.user_id      = u.id
            JOIN tenants             t  ON sa.tenant_id    = t.id
            WHERE sa.id = ? AND sa.tenant_id = ? AND sa.status = 'completed'
        ", [$assignmentId, $tenantId]);

        if (!$row) return null;

        return self::issue('specialty', $assignmentId, (int) $row['user_id'], $tenantId, [
            'member_name'    => $row['user_name'],
            'achievement'    => $row['specialty_name'],
            'type_label'     => $row['type'] === 'class' ? 'a Classe' : 'a Especialidade',
            'xp'             => (int) ($row['xp_earned'] ?? $row['xp_reward'] ?? 0),
            'icon'           => $row['icon'] ?? 'noto:blue-book',
            'completed_date' => $row['completed_at'] ? date('d \d\e F \d\e Y', strtotime($row['completed_at'])) : date('d \d\e F \d\e Y'),
            'club_name'      => $row['tenant_name'],
            'tenant_slug'    => $row['tenant_slug'],
        ]);
    }

    /**
     * Issue (or retrieve cached) certificate for a completed learning program.
     *
     * @param  int $progressId  user_program_progress.id
     * @param  int $tenantId
     * @return int|null  issued_certificates.id, null on failure
     */
    public static function generateForProgram(int $progressId, int $tenantId): ?int
    {
        $existing = db_fetch_one(
            "SELECT id FROM issued_certificates
             WHERE tenant_id = ? AND assignment_type = 'program' AND assignment_id = ?",
            [$tenantId, $progressId]
        );
        if ($existing) return (int) $existing['id'];

        $row = db_fetch_one("
            SELECT upp.id, upp.user_id, upp.program_id, upp.completed_at,
                   lp.name   AS program_name, lp.xp_reward, lp.icon, lp.difficulty,
                   u.name    AS user_name, u.avatar_url,
                   t.name    AS tenant_name, t.slug AS tenant_slug
            FROM user_program_progress upp
            JOIN learning_programs     lp ON upp.program_id = lp.id
            JOIN users                 u  ON upp.user_id    = u.id
            JOIN tenants               t  ON upp.tenant_id  = t.id
            WHERE upp.id = ? AND upp.tenant_id = ? AND upp.status = 'completed'
        ", [$progressId, $tenantId]);

        if (!$row) return null;

        return self::issue('program', $progressId, (int) $row['user_id'], $tenantId, [
            'member_name'    => $row['user_name'],
            'achievement'    => $row['program_name'],
            'type_label'     => 'o Programa de Aprendizagem',
            'xp'             => (int) ($row['xp_reward'] ?? 0),
            'icon'           => $row['icon'] ?? 'noto:blue-book',
            'completed_date' => $row['completed_at'] ? date('d \d\e F \d\e Y', strtotime($row['completed_at'])) : date('d \d\e F \d\e Y'),
            'club_name'      => $row['tenant_name'],
            'tenant_slug'    => $row['tenant_slug'],
        ]);
    }

    /**
     * Verify a certificate by its public hash.
     * Returns the certificate record + metadata, or null if invalid.
     */
    public static function verify(string $hash): ?array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $hash)) return null;

        $cert = db_fetch_one("
            SELECT ic.*,
                   u.name  AS user_name, u.avatar_url,
                   t.name  AS club_name, t.slug AS tenant_slug
            FROM issued_certificates ic
            JOIN users   u ON ic.user_id   = u.id
            JOIN tenants t ON ic.tenant_id = t.id
            WHERE ic.certificate_hash = ?
        ", [$hash]);

        if (!$cert) return null;

        // Enrich with achievement name
        if ($cert['assignment_type'] === 'specialty') {
            $row = db_fetch_one("
                SELECT s.name, s.icon, sa.completed_at, sa.xp_earned
                FROM specialty_assignments sa
                JOIN specialties s ON sa.specialty_id = s.id
                WHERE sa.id = ?
            ", [$cert['assignment_id']]);
        } elseif ($cert['assignment_type'] === 'program') {
            $row = db_fetch_one("
                SELECT lp.name, lp.icon, upp.completed_at, lp.xp_reward AS xp_earned
                FROM user_program_progress upp
                JOIN learning_programs lp ON upp.program_id = lp.id
                WHERE upp.id = ?
            ", [$cert['assignment_id']]);
        } else {
            $row = db_fetch_one("
                SELECT e.title as name, 'noto:event-admission' as icon, ee.attended_at as completed_at, e.xp_reward as xp_earned
                FROM event_enrollments ee
                JOIN events e ON ee.event_id = e.id
                WHERE ee.id = ?
            ", [$cert['assignment_id']]);
        }

        $cert['achievement_name'] = $row['name']        ?? 'Conquista';
        $cert['achievement_icon'] = $row['icon']        ?? 'noto:blue-book';
        $cert['completed_at']     = $row['completed_at'] ?? $cert['issued_at'];
        $cert['xp_earned']        = $row['xp_earned']   ?? 0;

        return $cert;
    }

    /**
     * Get a user's certificates for the "Meus Diplomas" gallery.
     */
    public static function getUserCertificates(int $userId, int $tenantId): array
    {
        $certs = db_fetch_all("
            SELECT ic.*
            FROM issued_certificates ic
            WHERE ic.user_id = ? AND ic.tenant_id = ?
            ORDER BY ic.issued_at DESC
        ", [$userId, $tenantId]);

        foreach ($certs as &$cert) {
            if ($cert['assignment_type'] === 'specialty') {
                $row = db_fetch_one("
                    SELECT s.name, s.icon, sa.completed_at, sa.xp_earned
                    FROM specialty_assignments sa
                    JOIN specialties s ON sa.specialty_id = s.id
                    WHERE sa.id = ?
                ", [$cert['assignment_id']]);
            } elseif ($cert['assignment_type'] === 'program') {
                $row = db_fetch_one("
                    SELECT lp.name, lp.icon, upp.completed_at, lp.xp_reward AS xp_earned
                    FROM user_program_progress upp
                    JOIN learning_programs lp ON upp.program_id = lp.id
                    WHERE upp.id = ?
                ", [$cert['assignment_id']]);
            } else {
                $row = db_fetch_one("
                    SELECT e.title as name, 'noto:event-admission' as icon, ee.attended_at as completed_at, e.xp_reward as xp_earned
                    FROM event_enrollments ee
                    JOIN events e ON ee.event_id = e.id
                    WHERE ee.id = ?
                ", [$cert['assignment_id']]);
            }
            $cert['achievement_name'] = $row['name']         ?? 'Conquista';
            $cert['achievement_icon'] = $row['icon']         ?? 'noto:blue-book';
            $cert['completed_at']     = $row['completed_at'] ?? $cert['issued_at'];
            $cert['xp_earned']        = $row['xp_earned']    ?? 0;
        }

        return $certs;
    }

    /**
     * Issue (or retrieve cached) certificate for an event.
     *
     * @param  int $enrollmentId  event_enrollments.id
     * @param  int $tenantId
     * @return int|null  issued_certificates.id, null on failure
     */
    public static function generateForEvent(int $enrollmentId, int $tenantId): ?int
    {
        $existing = db_fetch_one(
            "SELECT id FROM issued_certificates
             WHERE tenant_id = ? AND assignment_type = 'event' AND assignment_id = ?",
            [$tenantId, $enrollmentId]
        );
        if ($existing) return (int) $existing['id'];

        $row = db_fetch_one("
            SELECT ee.id, ee.user_id, ee.event_id, ee.attended_at,
                   e.title AS event_name, e.xp_reward,
                   u.name  AS user_name, u.avatar_url,
                   t.name  AS tenant_name, t.slug AS tenant_slug
            FROM event_enrollments ee
            JOIN events            e  ON ee.event_id = e.id
            JOIN users             u  ON ee.user_id  = u.id
            JOIN tenants           t  ON ee.tenant_id = t.id
            WHERE ee.id = ? AND ee.tenant_id = ? AND ee.status = 'attended'
        ", [$enrollmentId, $tenantId]);

        if (!$row) return null;

        return self::issue('event', $enrollmentId, (int) $row['user_id'], $tenantId, [
            'member_name'    => $row['user_name'],
            'achievement'    => $row['event_name'],
            'type_label'     => 'o Evento Especial',
            'xp'             => (int) ($row['xp_reward'] ?? 0),
            'icon'           => 'noto:event-admission',
            'completed_date' => $row['attended_at'] ? date('d \d\e F \d\e Y', strtotime($row['attended_at'])) : date('d \d\e F \d\e Y'),
            'club_name'      => $row['tenant_name'],
            'tenant_slug'    => $row['tenant_slug'],
        ]);
    }

    /**
     * Get a single certificate by ID, enforcing ownership.
     */
    public static function getById(int $certId, int $userId, int $tenantId): ?array
    {
        return db_fetch_one(
            "SELECT * FROM issued_certificates
             WHERE id = ? AND user_id = ? AND tenant_id = ?",
            [$certId, $userId, $tenantId]
        );
    }

    /**
     * Stream (or generate-then-stream) the PDF for a certificate.
     * Sets appropriate headers and outputs PDF bytes.
     */
    public static function streamPdf(array $cert, array $certData): void
    {
        $path = self::getPdfPath($cert['certificate_hash'], (int) $cert['tenant_id']);

        // Lazy-generate if the file is missing
        if (!file_exists($path)) {
            try {
                self::writePdf($cert['certificate_hash'], (int) $cert['tenant_id'], $certData);
                db_update('issued_certificates', ['file_path' => self::relativePath($cert['certificate_hash'], (int) $cert['tenant_id'])], 'id = ?', [$cert['id']]);
            } catch (\Throwable $e) {
                error_log('[CertificateService] streamPdf: lazy generation failed: ' . $e->getMessage());
                http_response_code(500);
                echo 'Erro ao gerar certificado. Por favor, tente novamente.';
                exit;
            }
        }

        // Flush any accumulated output (warnings, whitespace) before sending the binary file
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $filename = 'certificado-' . substr($cert['certificate_hash'], 0, 8) . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        readfile($path);
        exit;
    }

    // ----------------------------------------------------------------
    // Internal helpers
    // ----------------------------------------------------------------

    private static function issue(
        string $type,
        int    $assignmentId,
        int    $userId,
        int    $tenantId,
        array  $certData
    ): ?int {
        $hash = hash('sha256', implode('|', [$tenantId, $userId, $type, $assignmentId, microtime()]));

        $certId = db_insert('issued_certificates', [
            'tenant_id'       => $tenantId,
            'user_id'         => $userId,
            'assignment_type' => $type,
            'assignment_id'   => $assignmentId,
            'certificate_hash'=> $hash,
            'file_path'       => null,
            'issued_at'       => date('Y-m-d H:i:s'),
        ]);

        if (!$certId) return null;

        // Generate PDF immediately and cache it
        try {
            self::writePdf($hash, $tenantId, $certData);
            $relPath = self::relativePath($hash, $tenantId);
            db_update('issued_certificates', ['file_path' => $relPath], 'id = ?', [$certId]);
        } catch (\Throwable $e) {
            error_log('[CertificateService] PDF generation failed: ' . $e->getMessage());
            // Certificate record still saved; PDF generated on first download
        }

        return (int) $certId;
    }

    private static function writePdf(string $hash, int $tenantId, array $data): void
    {
        $dir = BASE_PATH . self::CERT_DIR . '/' . $tenantId;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $html = self::renderTemplate(array_merge($data, ['hash' => $hash]));

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('dpi', 150);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');

        try {
            $dompdf->render();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Dompdf render failed: ' . $e->getMessage(), 0, $e);
        }

        $pdf = $dompdf->output();
        if (empty($pdf)) {
            throw new \RuntimeException('Dompdf returned empty output for hash ' . $hash);
        }

        file_put_contents(self::getPdfPath($hash, $tenantId), $pdf);
    }

    private static function getPdfPath(string $hash, int $tenantId): string
    {
        return BASE_PATH . self::CERT_DIR . '/' . $tenantId . '/' . $hash . '.pdf';
    }

    private static function relativePath(string $hash, int $tenantId): string
    {
        return self::CERT_DIR . '/' . $tenantId . '/' . $hash . '.pdf';
    }

    // ----------------------------------------------------------------
    // HTML Template  (A4 landscape, dompdf-safe)
    // ----------------------------------------------------------------

    private static function renderTemplate(array $d): string
    {
        $clubName      = htmlspecialchars($d['club_name']    ?? 'Clube de Desbravadores');
        $memberName    = htmlspecialchars($d['member_name']  ?? '');
        $typeLabel     = $d['type_label']    ?? 'a Especialidade';
        $achievement   = htmlspecialchars($d['achievement']  ?? '');
        $completedDate = $d['completed_date'] ?? date('d \d\e F \d\e Y');
        $xp            = (int) ($d['xp'] ?? 0);
        $hash          = $d['hash']          ?? '';
        $hashShort     = strtoupper(substr($hash, 0, 16));
        $verifyUrl     = (isset($_SERVER['HTTP_HOST']) ? 'https://' . $_SERVER['HTTP_HOST'] : 'https://desbravahub.com') . '/v/' . $hash;

        // Month names in Portuguese
        $months = [
            'January'=>'janeiro','February'=>'fevereiro','March'=>'março',
            'April'=>'abril','May'=>'maio','June'=>'junho',
            'July'=>'julho','August'=>'agosto','September'=>'setembro',
            'October'=>'outubro','November'=>'novembro','December'=>'dezembro',
        ];
        foreach ($months as $en => $pt) {
            $completedDate = str_ireplace($en, $pt, $completedDate);
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<style>
@page {
    size: A4 landscape;
    margin: 0;
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    width: 297mm;
    height: 210mm;
    background: #ffffff;
    font-family: "DejaVu Sans", sans-serif;
    color: #1a1a2e;
    position: relative;
    overflow: hidden;
}

/* ---- Decorative corner backgrounds ---- */
.bg-corner-tl {
    position: absolute; top: 0; left: 0;
    width: 80mm; height: 80mm;
    background: #0d1b3e;
    clip-path: polygon(0 0, 100% 0, 0 100%);
    opacity: 0.06;
}
.bg-corner-br {
    position: absolute; bottom: 0; right: 0;
    width: 80mm; height: 80mm;
    background: #0d1b3e;
    clip-path: polygon(100% 0, 100% 100%, 0 100%);
    opacity: 0.06;
}

/* ---- Outer decorative frame ---- */
.frame-outer {
    position: absolute;
    top: 7mm; left: 7mm; right: 7mm; bottom: 7mm;
    border: 2.5pt solid #06b6d4;
}
.frame-inner {
    position: absolute;
    top: 10mm; left: 10mm; right: 10mm; bottom: 10mm;
    border: 0.75pt solid #7c3aed;
}

/* ---- Corner ornament dots ---- */
.dot {
    position: absolute;
    width: 5mm; height: 5mm;
    border-radius: 50%;
    background: #06b6d4;
}
.dot-tl { top: 4.5mm;  left:  4.5mm; }
.dot-tr { top: 4.5mm;  right: 4.5mm; }
.dot-bl { bottom: 4.5mm; left:  4.5mm; }
.dot-br { bottom: 4.5mm; right: 4.5mm; }

/* ---- Accent top stripe ---- */
.stripe-top {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4mm;
    background: linear-gradient(to right, #06b6d4, #7c3aed, #06b6d4);
}
.stripe-bottom {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 4mm;
    background: linear-gradient(to right, #7c3aed, #06b6d4, #7c3aed);
}

/* ---- Main content wrapper ---- */
.content {
    position: absolute;
    top: 14mm; left: 14mm; right: 14mm; bottom: 14mm;
}

/* ---- Header row ---- */
.header {
    text-align: center;
    padding-bottom: 3mm;
    border-bottom: 0.5pt solid #e2e8f0;
    margin-bottom: 5mm;
}
.club-label {
    font-size: 7.5pt;
    letter-spacing: 3pt;
    text-transform: uppercase;
    color: #7c3aed;
    font-weight: bold;
}
.cert-title {
    font-family: "DejaVu Serif", serif;
    font-size: 22pt;
    font-weight: bold;
    color: #0d1b3e;
    letter-spacing: 4pt;
    text-transform: uppercase;
    line-height: 1.1;
    margin-top: 1mm;
}
.cert-subtitle {
    font-size: 7pt;
    color: #94a3b8;
    letter-spacing: 2pt;
    text-transform: uppercase;
    margin-top: 1mm;
}

/* ---- Body ---- */
.body-text {
    text-align: center;
    font-size: 8.5pt;
    color: #475569;
    letter-spacing: 1.5pt;
    text-transform: uppercase;
    margin-bottom: 2mm;
}
.member-name {
    text-align: center;
    font-family: "DejaVu Serif", serif;
    font-size: 26pt;
    color: #06b6d4;
    font-style: italic;
    font-weight: bold;
    line-height: 1.1;
    margin-bottom: 3mm;
    padding: 2mm 0;
}
.achievement-label {
    text-align: center;
    font-size: 8pt;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1.5pt;
    margin-bottom: 1mm;
}
.achievement-name {
    text-align: center;
    font-family: "DejaVu Serif", serif;
    font-size: 16pt;
    font-weight: bold;
    color: #1a1a2e;
    margin-bottom: 3mm;
}
.meta-row {
    text-align: center;
    font-size: 7.5pt;
    color: #94a3b8;
    letter-spacing: 1pt;
    margin-bottom: 4mm;
}
.meta-badge {
    display: inline-block;
    background: #f0fdf4;
    border: 0.5pt solid #86efac;
    border-radius: 3mm;
    padding: 1mm 4mm;
    color: #166534;
    font-weight: bold;
    font-size: 7pt;
    margin: 0 2mm;
}

/* ---- Footer ---- */
.footer {
    position: absolute;
    bottom: 0; left: 0; right: 0;
}
.signature-table {
    width: 100%;
}
.sig-line {
    border-top: 0.75pt solid #cbd5e1;
    margin-bottom: 1mm;
}
.sig-name {
    font-size: 7pt;
    font-weight: bold;
    color: #1a1a2e;
    text-align: center;
}
.sig-role {
    font-size: 6pt;
    color: #94a3b8;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 1pt;
}
.verify-block {
    text-align: center;
    padding: 2mm;
    background: #f8fafc;
    border: 0.5pt solid #e2e8f0;
    border-radius: 2mm;
}
.verify-hash {
    font-family: "DejaVu Sans Mono", monospace;
    font-size: 5.5pt;
    color: #7c3aed;
    letter-spacing: 1pt;
    word-break: break-all;
}
.verify-label {
    font-size: 5.5pt;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 1pt;
    margin-bottom: 1mm;
}
</style>
</head>
<body>

<div class="bg-corner-tl"></div>
<div class="bg-corner-br"></div>
<div class="stripe-top"></div>
<div class="stripe-bottom"></div>
<div class="frame-outer"></div>
<div class="frame-inner"></div>
<div class="dot dot-tl"></div>
<div class="dot dot-tr"></div>
<div class="dot dot-bl"></div>
<div class="dot dot-br"></div>

<div class="content">

    <!-- Header -->
    <div class="header">
        <div class="club-label">{$clubName}</div>
        <div class="cert-title">Certificado de Conclusão</div>
        <div class="cert-subtitle">Desbravadores &nbsp;·&nbsp; Formação &amp; Desenvolvimento</div>
    </div>

    <!-- Body -->
    <div class="body-text">Certificamos que o(a) Desbravador(a)</div>
    <div class="member-name">{$memberName}</div>
    <div class="achievement-label">concluiu com êxito {$typeLabel}</div>
    <div class="achievement-name">{$achievement}</div>

    <div class="meta-row">
        <span class="meta-badge">&#10003; Concluído em {$completedDate}</span>
        <span class="meta-badge">+{$xp} XP</span>
    </div>

    <!-- Footer: signatures + verification -->
    <div class="footer">
        <table class="signature-table" cellpadding="0" cellspacing="0">
            <tr>
                <td width="30%" style="padding: 0 4mm 0 0;">
                    <div class="sig-line"></div>
                    <div class="sig-name">{$clubName}</div>
                    <div class="sig-role">Diretor do Clube</div>
                </td>
                <td width="40%" style="padding: 0 4mm;">
                    <div class="verify-block">
                        <div class="verify-label">&#9632; Código de Autenticidade</div>
                        <div class="verify-hash">{$hashShort}</div>
                        <div class="verify-label" style="margin-top:1mm;">Verificar: {$verifyUrl}</div>
                    </div>
                </td>
                <td width="30%" style="padding: 0 0 0 4mm;">
                    <div class="sig-line"></div>
                    <div class="sig-name">Associação Geral</div>
                    <div class="sig-role">Desbravadores Brasil</div>
                </td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
HTML;
    }
}
