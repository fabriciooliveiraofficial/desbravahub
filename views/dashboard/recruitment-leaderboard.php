<?php
/**
 * Recruitment Leaderboard - Ranking de Recrutamento
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */
?>
<style>
    .leaderboard-full {
        background: var(--bg-card, var(--hud-glass-panel));
        border: 1px solid var(--border-light, var(--hud-glass-border));
        border-radius: 16px;
        overflow: hidden;
    }

    .leaderboard-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--border-light, var(--hud-glass-border));
        transition: background 0.2s;
    }

    .leaderboard-row:last-child { border-bottom: none; }
    .leaderboard-row:hover { background: rgba(255, 255, 255, 0.02); }
    .leaderboard-row.is-you { background: rgba(0, 217, 255, 0.08); }
    .leaderboard-row.top-3 { background: rgba(247, 179, 43, 0.04); }

    .rank {
        font-family: var(--font-heading);
        font-size: 1.2rem;
        font-weight: 700;
        min-width: 36px;
        text-align: center;
    }

    .rank.gold { color: #FFD700; }
    .rank.silver { color: #C0C0C0; }
    .rank.bronze { color: #CD7F32; }

    .member-info {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .member-avatar-lg {
        width: 42px;
        height: 42px;
        background: var(--gradient-primary, linear-gradient(135deg, var(--accent-cyan), #0ea5e9));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--bg-dark, #0f172a);
        font-family: var(--font-heading);
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .member-details {
        min-width: 0;
    }

    .member-details h3 {
        font-size: var(--fs-card-title);
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .member-details span {
        font-size: var(--fs-caption);
        color: var(--text-secondary, var(--hud-text-dim));
    }

    .member-stats {
        text-align: right;
        flex-shrink: 0;
    }

    .member-xp {
        font-family: var(--font-mono);
        font-size: 1rem;
        font-weight: 700;
        color: var(--accent-green);
    }

    .member-converts {
        font-size: var(--fs-caption);
        color: var(--accent-cyan);
        font-weight: 700;
    }

    /* Stats Cards */
    .recruit-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }

    .recruit-stat {
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 16px;
        padding: 16px;
        text-align: center;
    }

    .recruit-stat-value {
        font-family: var(--font-mono);
        font-size: 1.6rem;
        font-weight: 800;
    }

    .recruit-stat-label {
        font-size: var(--fs-caption);
        color: var(--hud-text-dim);
        margin-top: 4px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-weight: 700;
    }

    /* Invite History */
    .invite-history {
        margin-top: 32px;
    }

    .invite-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: var(--hud-glass-panel);
        border: 1px solid var(--hud-glass-border);
        border-radius: 12px;
        margin-bottom: 8px;
        gap: 12px;
    }

    .invite-email {
        font-size: 0.85rem;
        font-weight: 600;
        color: #e2e8f0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
        flex: 1;
    }

    .invite-status {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        flex-shrink: 0;
    }

    .invite-status.status-pending {
        background: rgba(251, 191, 36, 0.15);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .invite-status.status-clicked {
        background: rgba(99, 102, 241, 0.15);
        color: #818cf8;
        border: 1px solid rgba(99, 102, 241, 0.3);
    }

    .invite-status.status-registered {
        background: rgba(0, 217, 255, 0.15);
        color: #00d9ff;
        border: 1px solid rgba(0, 217, 255, 0.3);
    }

    .invite-status.status-active {
        background: rgba(34, 197, 94, 0.15);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .invite-xp {
        font-family: var(--font-mono);
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--accent-green);
        flex-shrink: 0;
    }

    .invite-actions {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
    }

    .btn-invite-action {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--hud-text-dim);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-invite-action:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-invite-action.resend:hover {
        color: var(--accent-cyan);
        border-color: rgba(0, 217, 255, 0.3);
        background: rgba(0, 217, 255, 0.08);
    }

    .btn-invite-action.revoke:hover {
        color: #f87171;
        border-color: rgba(248, 113, 113, 0.3);
        background: rgba(248, 113, 113, 0.08);
    }

    .btn-invite-action i {
        font-size: 1.1rem;
    }

    /* Send Invite Form */
    .invite-form-card {
        background: linear-gradient(135deg, rgba(0, 217, 255, 0.06), rgba(34, 197, 94, 0.06));
        border: 1px solid rgba(0, 217, 255, 0.2);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .invite-form-card h3 {
        font-size: 0.9rem;
        font-weight: 800;
        color: var(--accent-cyan);
        margin: 0 0 4px;
    }

    .invite-form-card p {
        font-size: var(--fs-caption);
        color: var(--hud-text-dim);
        margin: 0 0 16px;
    }

    .invite-form-row {
        display: flex;
        gap: 8px;
    }

    .invite-form-row input {
        flex: 1;
        background: rgba(0, 0, 0, 0.3);
        border: 1px solid var(--hud-glass-border);
        border-radius: 10px;
        padding: 10px 14px;
        color: #e2e8f0;
        font-size: 0.85rem;
        outline: none;
    }

    .invite-form-row input:focus {
        border-color: var(--accent-cyan);
        box-shadow: 0 0 0 2px rgba(0, 217, 255, 0.15);
    }

    .invite-form-row button {
        background: linear-gradient(135deg, var(--accent-cyan), #22c55e);
        color: #0f172a;
        border: none;
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 800;
        font-size: 0.8rem;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
    }

    .invite-form-row button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 217, 255, 0.3);
    }

    .invite-form-row button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .xp-highlight {
        background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(0, 217, 255, 0.1));
        border: 1px solid rgba(34, 197, 94, 0.2);
        border-radius: 12px;
        padding: 12px 16px;
        text-align: center;
        margin-bottom: 24px;
    }

    .xp-highlight-value {
        font-family: var(--font-mono);
        font-size: 1.8rem;
        font-weight: 900;
        background: linear-gradient(135deg, #22c55e, #00d9ff);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .xp-highlight-label {
        font-size: 0.65rem;
        color: var(--hud-text-dim);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 700;
        margin-top: 2px;
    }

    @media (max-width: 480px) {
        .recruit-stats { grid-template-columns: repeat(2, 1fr); gap: 8px; }
        .recruit-stat { padding: 12px; }
        .recruit-stat-value { font-size: 1.3rem; }
        .invite-form-row { flex-direction: column; }
        .invite-card { flex-wrap: wrap; }
    }
</style>

<header class="page-header">
    <h1>🎯 Ranking de Recrutamento</h1>
    <p>Os maiores embaixadores do clube!</p>
</header>

<div class="ranking-tabs">
    <a href="<?= base_url($tenant['slug'] . '/ranking') ?>" class="ranking-tab">Geral</a>
    <a href="<?= base_url($tenant['slug'] . '/ranking-unidades') ?>" class="ranking-tab">Unidades</a>
    <a href="<?= base_url($tenant['slug'] . '/ranking-recrutamento') ?>" class="ranking-tab active">Recrutamento</a>
</div>

<!-- XP Reward Highlight -->
<div class="xp-highlight">
    <div class="xp-highlight-value">+<?= number_format($referralXpReward) ?> XP</div>
    <div class="xp-highlight-label">Por cada convite convertido em membro ativo</div>
</div>

<!-- Send Invite Form -->
<div class="invite-form-card">
    <h3>📨 Enviar Convite</h3>
    <p>Convide alguém para o clube e ganhe <?= $referralXpReward ?> XP quando se tornar membro ativo!</p>
    <div class="invite-form-row">
        <input type="email" id="inviteEmail" placeholder="Digite o email do convidado..." autocomplete="off">
        <button id="btnSendInvite" onclick="sendInvite()">ENVIAR</button>
    </div>
    <div id="inviteMessage" style="margin-top: 10px; font-size: 0.8rem; display: none;"></div>
</div>

<!-- My Stats -->
<div class="recruit-stats">
    <div class="recruit-stat">
        <div class="recruit-stat-value" style="color: var(--accent-cyan)"><?= $userStats['total'] ?></div>
        <div class="recruit-stat-label">Enviados</div>
    </div>
    <div class="recruit-stat">
        <div class="recruit-stat-value" style="color: var(--accent-green)"><?= $userStats['converted'] ?></div>
        <div class="recruit-stat-label">Convertidos</div>
    </div>
    <div class="recruit-stat">
        <div class="recruit-stat-value" style="color: var(--accent-warning)"><?= $userStats['pending'] ?></div>
        <div class="recruit-stat-label">Pendentes</div>
    </div>
    <div class="recruit-stat">
        <div class="recruit-stat-value" style="color: #22c55e"><?= number_format($userStats['xpEarned']) ?></div>
        <div class="recruit-stat-label">XP Ganho</div>
    </div>
</div>

<!-- Recruitment Leaderboard -->
<?php if ($userRecruitPosition): ?>
    <div class="your-position">
        <h2>#<?= $userRecruitPosition ?></h2>
        <p>Sua posição no ranking de recrutamento</p>
    </div>
<?php endif; ?>

<div class="leaderboard-full">
    <?php if (empty($recruitLeaderboard)): ?>
        <div style="padding: 40px; text-align: center; color: var(--hud-text-dim);">
            <i class="material-icons-round" style="font-size: 3rem; opacity: 0.3; display: block; margin-bottom: 12px;">group_add</i>
            <p>Nenhum convite convertido ainda. Seja o primeiro!</p>
        </div>
    <?php else: ?>
        <?php foreach ($recruitLeaderboard as $index => $recruiter): ?>
            <?php
            $position = $index + 1;
            $isYou = $recruiter['id'] === $user['id'];
            $isTop3 = $position <= 3;
            ?>
            <div class="leaderboard-row <?= $isYou ? 'is-you' : '' ?> <?= $isTop3 ? 'top-3' : '' ?>">
                <div class="rank <?= $position === 1 ? 'gold' : ($position === 2 ? 'silver' : ($position === 3 ? 'bronze' : '')) ?>">
                    <?php if ($position === 1): ?>🥇<?php elseif ($position === 2): ?>🥈<?php elseif ($position === 3): ?>🥉<?php else: ?><?= $position ?><?php endif; ?>
                </div>

                <div class="member-info">
                    <div class="member-avatar-lg">
                        <?= strtoupper(substr($recruiter['name'], 0, 1)) ?>
                    </div>
                    <div class="member-details">
                        <h3>
                            <?= htmlspecialchars($recruiter['name']) ?>
                            <?= $isYou ? '(você)' : '' ?>
                        </h3>
                        <span><?= $recruiter['total_converted'] ?> <?= $recruiter['total_converted'] === 1 ? 'recrutado' : 'recrutados' ?></span>
                    </div>
                </div>

                <div class="member-stats">
                    <div class="member-xp">+<?= number_format($recruiter['referral_xp']) ?> XP</div>
                    <div class="member-converts"><?= $recruiter['total_converted'] ?> ✓</div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- My Invite History -->
<?php if (!empty($userInvites)): ?>
<div class="invite-history">
    <h2 class="hud-section-title" style="margin-bottom: 12px;">Meus Convites</h2>
    <?php
    $statusLabels = [
        'pending'    => ['ENVIADO', 'status-pending'],
        'clicked'    => ['CLICADO', 'status-clicked'],
        'registered' => ['REGISTRADO', 'status-registered'],
        'active'     => ['ATIVO ✓', 'status-active'],
    ];
    foreach ($userInvites as $invite):
        $st = $statusLabels[$invite['status']] ?? $statusLabels['pending'];
    ?>
        <div class="invite-card">
            <div class="invite-email">
                <?= $invite['status'] === 'active' && $invite['converted_name']
                    ? htmlspecialchars($invite['converted_name'])
                    : htmlspecialchars($invite['email'])
                ?>
            </div>
            <span class="invite-status <?= $st[1] ?>"><?= $st[0] ?></span>
            
            <?php if (in_array($invite['status'], ['pending', 'clicked'])): ?>
                <div class="invite-actions">
                    <button class="btn-invite-action resend" onclick="resendInvite(<?= $invite['id'] ?>)" title="Reenviar Convite">
                        <i class="material-icons-round">refresh</i>
                    </button>
                    <button class="btn-invite-action revoke" onclick="revokeInvite(<?= $invite['id'] ?>)" title="Revogar Convite">
                        <i class="material-icons-round">delete_outline</i>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($invite['xp_rewarded'] > 0): ?>
                <span class="invite-xp">+<?= $invite['xp_rewarded'] ?> XP</span>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
async function sendInvite() {
    const emailInput = document.getElementById('inviteEmail');
    const btn = document.getElementById('btnSendInvite');
    const msgDiv = document.getElementById('inviteMessage');
    const email = emailInput.value.trim();

    if (!email || !email.includes('@')) {
        msgDiv.style.display = 'block';
        msgDiv.style.color = '#ef4444';
        msgDiv.textContent = 'Por favor, insira um email válido.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'ENVIANDO...';
    msgDiv.style.display = 'none';

    try {
        const res = await fetch('<?= base_url($tenant['slug'] . '/convite/enviar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });

        const data = await res.json();

        msgDiv.style.display = 'block';
        if (data.success) {
            msgDiv.style.color = '#22c55e';
            msgDiv.textContent = '✅ ' + data.message;
            if (window.toast) window.toast.success('Sucesso', data.message);
            emailInput.value = '';
            // Reload after 1.5s to update stats
            setTimeout(() => location.reload(), 2000);
        } else {
            msgDiv.style.color = '#ef4444';
            msgDiv.textContent = '❌ ' + data.message;
            if (window.toast) window.toast.error('Erro', data.message);
        }
    } catch (err) {
        msgDiv.style.display = 'block';
        msgDiv.style.color = '#ef4444';
        msgDiv.textContent = 'Erro de conexão.';
        if (window.toast) window.toast.error('Erro', 'Erro de conexão.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'ENVIAR';
    }
}

async function resendInvite(id) {
    const confirmed = window.toast 
        ? await window.toast.confirm('Reenviar Convite', 'Deseja reenviar este convite?', { confirmText: 'Reenviar' })
        : confirm('Deseja reenviar este convite?');

    if (!confirmed) return;

    try {
        const res = await fetch('<?= base_url($tenant['slug'] . '/convite/reenviar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        
        if (data.success) {
            if (window.toast) window.toast.success('Sucesso', data.message);
            else alert('✅ ' + data.message);
        } else {
            if (window.toast) window.toast.error('Erro', data.message);
            else alert('❌ ' + data.message);
        }
    } catch (err) {
        if (window.toast) window.toast.error('Erro', 'Erro ao reenviar convite.');
        else alert('Erro ao reenviar convite.');
    }
}

async function revokeInvite(id) {
    const confirmed = window.toast 
        ? await window.toast.confirm('Revogar Convite', 'Deseja revogar (excluir) este convite? Esta ação não pode ser desfeita.', { confirmText: 'Revogar', confirmBg: '#ef4444' })
        : confirm('Deseja revogar (excluir) este convite? Esta ação não pode ser desfeita.');

    if (!confirmed) return;

    try {
        const res = await fetch('<?= base_url($tenant['slug'] . '/convite/revogar') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const data = await res.json();
        
        if (data.success) {
            if (window.toast) window.toast.success('Revogado', 'O convite foi revogado com sucesso.');
            setTimeout(() => location.reload(), 1500);
        } else {
            if (window.toast) window.toast.error('Erro', data.message);
            else alert('❌ ' + data.message);
        }
    } catch (err) {
        if (window.toast) window.toast.error('Erro', 'Erro ao revogar convite.');
        else alert('Erro ao revogar convite.');
    }
}
</script>
