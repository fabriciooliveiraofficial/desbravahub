<?php
/**
 * Referral Service
 * 
 * Handles invitation generation, tracking, conversion, and XP rewards.
 * Uses the Club's SMTP via EmailService for sending invites.
 */

namespace App\Services;

use App\Core\App;

class ReferralService
{
    /** XP reward per converted referral (highest in the system) */
    const REFERRAL_XP_REWARD = 750;

    /**
     * Generate and send an invitation email
     */
    public static function sendInvite(int $referrerId, string $email): array
    {
        $tenant = App::tenant();
        $user = App::user();

        // Validate: don't invite existing members
        $existing = db_fetch_one(
            "SELECT id FROM users WHERE email = ? AND tenant_id = ? AND deleted_at IS NULL",
            [$email, $tenant['id']]
        );
        if ($existing) {
            return ['success' => false, 'message' => 'Este email já é um membro do clube.'];
        }

        // Validate: don't re-invite recently (within 7 days)
        $recent = db_fetch_one(
            "SELECT id FROM referral_invites WHERE referrer_id = ? AND email = ? AND tenant_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$referrerId, $email, $tenant['id']]
        );
        if ($recent) {
            return ['success' => false, 'message' => 'Convite já enviado para este email nos últimos 7 dias.'];
        }

        // Generate unique token
        $token = bin2hex(random_bytes(24));

        $email = strtolower(trim($email));

        // Insert invite record
        $inviteId = db_insert('referral_invites', [
            'tenant_id'   => $tenant['id'],
            'referrer_id' => $referrerId,
            'email'       => $email,
            'token'       => $token,
            'status'      => 'pending',
        ]);

        // Build invite link
        $inviteLink = base_url($tenant['slug'] . '/convite/' . $token);

        // Build email HTML
        $referrerName = htmlspecialchars($user['name']);
        $clubName = htmlspecialchars($tenant['name']);
        $htmlBody = self::buildInviteEmail($referrerName, $clubName, $inviteLink);

        // Send via EmailService
        try {
            $emailService = EmailService::getInstance();
            $sent = $emailService->send(
                $email,
                "🏕️ {$referrerName} te convidou para o {$clubName}!",
                $htmlBody
            );

            if (!$sent) {
                return ['success' => false, 'message' => 'Falha ao enviar o email. Verifique as configurações SMTP do clube.'];
            }
        } catch (\Exception $e) {
            error_log("ReferralService::sendInvite error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao enviar email: ' . $e->getMessage()];
        }

        return ['success' => true, 'message' => 'Convite enviado com sucesso!', 'invite_id' => $inviteId];
    }

    /**
     * Resend an existing invitation
     */
    public static function resendInvite(int $referrerId, int $inviteId): array
    {
        $tenant = App::tenant();
        $user = App::user();

        // Find the invite
        $invite = db_fetch_one(
            "SELECT * FROM referral_invites WHERE id = ? AND referrer_id = ? AND tenant_id = ?",
            [$inviteId, $referrerId, $tenant['id']]
        );

        if (!$invite) {
            return ['success' => false, 'message' => 'Convite não encontrado ou não pertence a você.'];
        }

        // Only allowed for non-converted ones
        if (!in_array($invite['status'], ['pending', 'clicked'])) {
            return ['success' => false, 'message' => 'Este convite já foi convertido ou não pode ser reenviado.'];
        }

        // Build invite link (reuse existing token)
        $inviteLink = base_url($tenant['slug'] . '/convite/' . $invite['token']);

        // Build email HTML
        $referrerName = htmlspecialchars($user['name']);
        $clubName = htmlspecialchars($tenant['name']);
        $htmlBody = self::buildInviteEmail($referrerName, $clubName, $inviteLink);

        // Send via EmailService
        try {
            $emailService = EmailService::getInstance();
            $sent = $emailService->send(
                $invite['email'],
                "🏕️ REENVIADO: {$referrerName} te convidou para o {$clubName}!",
                $htmlBody
            );

            if (!$sent) {
                return ['success' => false, 'message' => 'Falha ao enviar o email. Verifique o SMTP.'];
            }

            // Update timestamp
            db_query("UPDATE referral_invites SET updated_at = NOW() WHERE id = ?", [$inviteId]);

        } catch (\Exception $e) {
            error_log("ReferralService::resendInvite error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro ao reenviar email: ' . $e->getMessage()];
        }

        return ['success' => true, 'message' => 'Convite reenviado com sucesso!'];
    }

    /**
     * Revoke an invitation (Delete it)
     */
    public static function revokeInvite(int $referrerId, int $inviteId): array
    {
        $tenant = App::tenant();

        // Check if exists and belongs to user
        $invite = db_fetch_one(
            "SELECT * FROM referral_invites WHERE id = ? AND referrer_id = ? AND tenant_id = ?",
            [$inviteId, $referrerId, $tenant['id']]
        );

        if (!$invite) {
            return ['success' => false, 'message' => 'Convite não encontrado.'];
        }

        // Only allow if not yet converted
        if (!in_array($invite['status'], ['pending', 'clicked'])) {
            return ['success' => false, 'message' => 'Não é possível revogar um convite que já foi registrado ou ativado.'];
        }

        // Physical delete to clean up the screen as requested
        db_query("DELETE FROM referral_invites WHERE id = ?", [$inviteId]);

        return ['success' => true, 'message' => 'Convite revogado com sucesso!'];
    }

    /**
     * Handle invite link click (track engagement)
     */
    public static function trackClick(string $token): ?array
    {
        $invite = db_fetch_one("SELECT * FROM referral_invites WHERE token = ?", [$token]);
        if (!$invite) return null;

        // Update status if still pending
        if ($invite['status'] === 'pending') {
            db_update('referral_invites', [
                'status'     => 'clicked',
                'clicked_at' => date('Y-m-d H:i:s'),
            ], 'id = ?', [$invite['id']]);
        }

        return $invite;
    }

    /**
     * Handle registration conversion — called during user signup
     */
    public static function handleRegistration(int $newUserId, string $email, int $tenantId): void
    {
        // Find the most recent invite for this email
        $invite = db_fetch_one(
            "SELECT * FROM referral_invites WHERE LOWER(email) = LOWER(?) AND tenant_id = ? AND status IN ('pending', 'clicked') ORDER BY created_at DESC LIMIT 1",
            [$email, $tenantId]
        );

        if (!$invite) return;

        // Update invite status
        db_update('referral_invites', [
            'status'            => 'registered',
            'converted_user_id' => $newUserId,
            'registered_at'     => date('Y-m-d H:i:s'),
        ], 'id = ?', [$invite['id']]);

        // Mark user as referred
        db_query("UPDATE users SET referred_by_id = ? WHERE id = ?", [$invite['referrer_id'], $newUserId]);
    }

    /**
     * Activate referral and award XP — called when new member completes profile
     */
    public static function checkAndActivate(int $userId): void
    {
        $tenant = App::tenant();

        // Find registered (but not yet active) invites for this user
        $invite = db_fetch_one(
            "SELECT * FROM referral_invites WHERE converted_user_id = ? AND tenant_id = ? AND status = 'registered'",
            [$userId, $tenant['id']]
        );

        // Fallback: If not found by ID, try finding by email if for some reason the link is missing
        if (!$invite) {
            $userRecord = db_fetch_one("SELECT email FROM users WHERE id = ?", [$userId]);
            if ($userRecord) {
                $invite = db_fetch_one(
                    "SELECT * FROM referral_invites WHERE LOWER(email) = LOWER(?) AND tenant_id = ? AND status IN ('pending', 'clicked', 'registered')",
                    [$userRecord['email'], $tenant['id']]
                );
                
                // If found by email, perform a late link
                if ($invite) {
                    db_update('referral_invites', ['converted_user_id' => $userId], 'id = ?', [$invite['id']]);
                }
            }
        }

        if (!$invite) return;

        // Mark as active
        db_update('referral_invites', [
            'status'       => 'active',
            'activated_at' => date('Y-m-d H:i:s'),
            'xp_rewarded'  => self::REFERRAL_XP_REWARD,
            'rewarded_at'  => date('Y-m-d H:i:s'),
        ], 'id = ?', [$invite['id']]);

        // Award POWER XP to referrer
        $progressionService = new ProgressionService();
        $progressionService->addXp($invite['referrer_id'], self::REFERRAL_XP_REWARD, 'referral', $invite['id']);

        // Send notification to referrer
        try {
            $newUser = db_fetch_one("SELECT name FROM users WHERE id = ?", [$userId]);
            $newUserName = $newUser['name'] ?? 'Novo Membro';

            $notifService = new NotificationService();
            $notifService->send(
                $invite['referrer_id'],
                'achievement',
                '🎉 Convite Convertido!',
                "{$newUserName} se tornou um membro ativo! Você recebeu " . self::REFERRAL_XP_REWARD . " XP de Recrutamento!",
                ['channels' => ['toast', 'push'], 'priority' => 'high']
            );
        } catch (\Exception $e) {
            error_log("ReferralService::checkAndActivate notification error: " . $e->getMessage());
        }
    }

    /**
     * Get referral stats for a user (for Profile page)
     */
    public static function getUserStats(int $userId, int $tenantId): array
    {
        $total = (int) db_fetch_column(
            "SELECT COUNT(*) FROM referral_invites WHERE referrer_id = ? AND tenant_id = ?",
            [$userId, $tenantId]
        );

        $converted = (int) db_fetch_column(
            "SELECT COUNT(*) FROM referral_invites WHERE referrer_id = ? AND tenant_id = ? AND status = 'active'",
            [$userId, $tenantId]
        );

        $xpEarned = (int) db_fetch_column(
            "SELECT COALESCE(SUM(xp_rewarded), 0) FROM referral_invites WHERE referrer_id = ? AND tenant_id = ? AND status = 'active'",
            [$userId, $tenantId]
        );

        $pending = (int) db_fetch_column(
            "SELECT COUNT(*) FROM referral_invites WHERE referrer_id = ? AND tenant_id = ? AND status IN ('pending', 'clicked', 'registered')",
            [$userId, $tenantId]
        );

        return compact('total', 'converted', 'xpEarned', 'pending');
    }

    /**
     * Get individual invite history for a user
     */
    public static function getUserInvites(int $userId, int $tenantId): array
    {
        return db_fetch_all(
            "SELECT ri.*, u.name as converted_name 
             FROM referral_invites ri 
             LEFT JOIN users u ON ri.converted_user_id = u.id
             WHERE ri.referrer_id = ? AND ri.tenant_id = ?
             ORDER BY ri.created_at DESC",
            [$userId, $tenantId]
        );
    }

    /**
     * Get recruitment leaderboard (Top recruiters by active conversions)
     */
    public static function getRecruitmentLeaderboard(int $tenantId, int $limit = 100): array
    {
        return db_fetch_all(
            "SELECT 
                u.id, u.name, u.avatar_url, u.xp_points,
                l.level_number,
                COUNT(ri.id) as total_converted,
                COALESCE(SUM(ri.xp_rewarded), 0) as referral_xp
             FROM referral_invites ri
             JOIN users u ON ri.referrer_id = u.id
             LEFT JOIN levels l ON u.level_id = l.id
             WHERE ri.tenant_id = ? AND ri.status = 'active'
             GROUP BY u.id
             ORDER BY total_converted DESC, referral_xp DESC
             LIMIT ?",
            [$tenantId, $limit]
        );
    }

    /**
     * Get ALL referral invites for a tenant (for admin overview)
     */
    public static function getAllInvitesForTenant(int $tenantId, int $limit = 500): array
    {
        $invites = db_fetch_all(
            "SELECT ri.*, 
                    u.name as referrer_name, u.email as referrer_email,
                    u_conv.name as converted_name
             FROM referral_invites ri
             JOIN users u ON ri.referrer_id = u.id
             LEFT JOIN users u_conv ON ri.converted_user_id = u_conv.id
             WHERE ri.tenant_id = ?
             ORDER BY ri.created_at DESC
             LIMIT ?",
            [$tenantId, $limit]
        );

        // AUTO-REPAIR: If an invite is pending/clicked but the user already exists, link them now
        $hasRepairs = false;
        foreach ($invites as &$invite) {
            // Case A: Link pending/clicked invitations to existing users
            if ($invite['status'] === 'pending' || $invite['status'] === 'clicked') {
                $existingUser = db_fetch_one(
                    "SELECT id, name FROM users WHERE LOWER(email) = LOWER(?) AND tenant_id = ? LIMIT 1",
                    [$invite['email'], $tenantId]
                );

                if ($existingUser) {
                    db_update('referral_invites', [
                        'status'            => 'registered',
                        'converted_user_id' => $existingUser['id'],
                        'registered_at'     => date('Y-m-d H:i:s'),
                    ], 'id = ?', [$invite['id']]);
                    
                    // Update local object
                    $invite['status'] = 'registered';
                    $invite['converted_user_id'] = $existingUser['id'];
                    $invite['converted_name'] = $existingUser['name'];
                    $hasRepairs = true;
                }
            }

            // Case B: If registered but no XP yet, check if profile is complete to reward them
            if ($invite['status'] === 'registered' && empty($invite['xp_rewarded']) && !empty($invite['converted_user_id'])) {
                $hasProfile = db_fetch_one("SELECT 1 FROM user_profiles WHERE user_id = ? LIMIT 1", [$invite['converted_user_id']]);
                if ($hasProfile) {
                    try {
                        self::checkAndActivate($invite['converted_user_id']);
                        
                        // Re-fetch the updated state for this invite
                        $fresh = db_fetch_one("SELECT status, xp_rewarded, activated_at FROM referral_invites WHERE id = ?", [$invite['id']]);
                        if ($fresh) {
                            $invite['status'] = $fresh['status'];
                            $invite['xp_rewarded'] = $fresh['xp_rewarded'];
                            $invite['activated_at'] = $fresh['activated_at'];
                            $hasRepairs = true;
                        }
                    } catch (\Exception $e) {
                        error_log("XP Heal error for User {$invite['converted_user_id']}: " . $e->getMessage());
                    }
                }
            }
        }

        return $invites;
    }

    /**
     * Build the beautiful invite email HTML
     */
    private static function buildInviteEmail(string $referrerName, string $clubName, string $inviteLink): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background-color:#1a1a2e;">
<table width="100%" style="max-width:600px;margin:0 auto;padding:40px 20px;">
<tr><td style="background:linear-gradient(135deg,#16213e,#1a1a2e);border-radius:16px;padding:40px;border:1px solid rgba(0,217,255,0.2);">
    <div style="text-align:center;font-size:56px;margin-bottom:16px;">🏕️</div>
    <h1 style="color:#00d9ff;text-align:center;margin:0 0 8px;font-size:22px;">Você foi convidado!</h1>
    <p style="color:#94a3b8;text-align:center;margin:0 0 24px;font-size:14px;">Para o Clube de Desbravadores</p>
    
    <div style="background:rgba(0,217,255,0.08);border:1px solid rgba(0,217,255,0.15);padding:20px;border-radius:12px;margin-bottom:24px;">
        <p style="color:#e0e0e0;margin:0;font-size:16px;line-height:1.6;text-align:center;">
            <strong style="color:#00d9ff;">{$referrerName}</strong> te convidou para fazer parte do <strong style="color:#22c55e;">{$clubName}</strong>!
        </p>
    </div>

    <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.15);padding:16px;border-radius:12px;margin-bottom:24px;">
        <p style="color:#94a3b8;margin:0 0 8px;font-size:12px;text-transform:uppercase;letter-spacing:0.1em;font-weight:700;">O que te espera:</p>
        <p style="color:#e0e0e0;margin:0;font-size:14px;line-height:1.8;">
            ⭐ Especialidades e classes interativas<br>
            🏆 Sistema de XP e conquistas<br>
            📊 Rankings e competições entre unidades<br>
            🎯 Missões e desafios exclusivos
        </p>
    </div>

    <div style="text-align:center;margin:32px 0;">
        <a href="{$inviteLink}" style="display:inline-block;background:linear-gradient(135deg,#00d9ff,#22c55e);color:#0f172a;padding:16px 40px;border-radius:12px;text-decoration:none;font-weight:800;font-size:16px;letter-spacing:0.05em;box-shadow:0 4px 20px rgba(0,217,255,0.3);">
            ACEITAR CONVITE
        </a>
    </div>

    <hr style="border:none;border-top:1px solid rgba(255,255,255,0.08);margin:24px 0;">
    <p style="color:#64748b;font-size:11px;text-align:center;margin:0;">
        Este convite foi enviado por {$referrerName} via {$clubName} • DesbravaHub
    </p>
</td></tr></table>
</body></html>
HTML;
    }
}
