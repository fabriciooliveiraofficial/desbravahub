<?php
/**
 * Admin — Communication & Engagement Hub
 */
$tenantSlug  = $tenant['slug'] ?? '';
$baseUrl     = base_url($tenantSlug . '/admin/comunicacao');
?>

<style>
/* ── Hub Stats ──────────────────────────────────── */
.hub-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}
@media (max-width: 900px) { .hub-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .hub-stats { grid-template-columns: 1fr 1fr; } }

.hub-stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.hub-stat-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.hub-stat-icon .material-icons-round { font-size: 24px; }
.hub-stat-value { font-size: 1.8rem; font-weight: 900; line-height: 1; }
.hub-stat-label { font-size: 0.8rem; color: var(--text-secondary); margin-top: 3px; }

/* ── Tabs ───────────────────────────────────────── */
.hub-tabs {
    display: flex; gap: 6px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    border-bottom: 2px solid var(--border-light);
    padding-bottom: 0;
}
.hub-tab {
    padding: 10px 22px;
    border-radius: 10px 10px 0 0;
    font-weight: 700; font-size: 0.88rem;
    text-decoration: none;
    color: var(--text-secondary);
    display: flex; align-items: center; gap: 7px;
    border: 1px solid transparent;
    border-bottom: none;
    margin-bottom: -2px;
    transition: all .2s;
}
.hub-tab:hover { color: var(--text-primary); background: var(--bg-elevated); }
.hub-tab.active {
    background: var(--bg-surface);
    color: var(--text-primary);
    border-color: var(--border-light);
    border-bottom-color: var(--bg-surface);
}
.hub-tab .badge {
    background: var(--accent); color: #000;
    font-size: 0.72rem; font-weight: 800;
    padding: 1px 7px; border-radius: 20px;
    line-height: 1.4;
}
.hub-tab .badge.alert { background: #ef4444; color: white; }

/* ── Lead cards ──────────────────────────────────── */
.lead-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
}
.lead-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    transition: border-color .2s;
}
.lead-card:hover { border-color: var(--accent); }
.lead-card__name {
    font-size: 1.05rem; font-weight: 800;
    display: flex; align-items: center; gap: 8px;
}
.lead-card__name .material-icons-round { font-size: 20px; color: var(--accent); }
.lead-card__meta { font-size: 0.82rem; color: var(--text-secondary); display: flex; flex-direction: column; gap: 4px; }
.lead-card__meta span { display: flex; align-items: center; gap: 6px; }
.lead-card__meta .material-icons-round { font-size: 15px; color: var(--text-secondary); }
.lead-card__msg {
    font-size: 0.85rem; color: var(--text-primary);
    background: var(--bg-elevated);
    border-radius: 8px; padding: 8px 12px;
    border-left: 3px solid var(--accent);
    font-style: italic;
}
.lead-card__actions { display: flex; gap: 8px; flex-wrap: wrap; }

.btn-wa {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 10px; border: none;
    background: #22c55e; color: #fff;
    font-weight: 700; font-size: 0.82rem; cursor: pointer;
    text-decoration: none; transition: opacity .2s;
}
.btn-wa:hover { opacity: .85; }
.btn-email {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 10px; border: none;
    background: #3b82f6; color: #fff;
    font-weight: 700; font-size: 0.82rem; cursor: pointer;
    text-decoration: none; transition: opacity .2s;
}
.btn-email:hover { opacity: .85; }
.btn-status {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 7px 12px; border-radius: 9px;
    font-weight: 700; font-size: 0.78rem; cursor: pointer;
    border: 1px solid var(--border-light);
    background: var(--bg-elevated); color: var(--text-secondary);
    transition: all .2s;
}
.btn-status:hover { border-color: var(--accent); color: var(--accent); }
.btn-status .material-icons-round { font-size: 14px; }

.lead-status-pill {
    padding: 3px 10px; border-radius: 20px;
    font-size: 0.72rem; font-weight: 700;
    margin-left: auto;
}
.lead-status-pill.new        { background: rgba(0,204,255,.15); color: #00ccff; }
.lead-status-pill.contacting { background: rgba(251,191,36,.15); color: #f59e0b; }
.lead-status-pill.converted  { background: rgba(34,197,94,.15);  color: #22c55e; }
.lead-status-pill.dismissed  { background: rgba(239,68,68,.15);  color: #ef4444; }

/* Lead filter tabs */
.lead-filter-tabs {
    display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;
}
.lead-filter-tab {
    padding: 6px 16px; border-radius: 20px;
    font-weight: 700; font-size: 0.82rem;
    text-decoration: none; cursor: pointer;
    background: var(--bg-elevated); color: var(--text-secondary);
    border: 1px solid var(--border-light);
    transition: all .2s;
}
.lead-filter-tab.active, .lead-filter-tab:hover {
    background: var(--accent); color: #000; border-color: var(--accent);
}

/* ── Comment cards (reuse from public-comments) ── */
.comment-card {
    background: var(--bg-surface); border: 1px solid var(--border-light);
    border-radius: 14px; padding: 18px; margin-bottom: 14px;
    display: flex; flex-direction: column; gap: 10px;
}
.comment-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; }
.comment-author { font-weight: 700; }
.comment-meta   { font-size: 0.8rem; color: var(--text-secondary); }
.comment-body   { font-size: 0.9rem; line-height: 1.5; }
.comment-source { font-size: 0.78rem; color: var(--text-secondary); }
.comment-actions { display: flex; gap: 8px; }
.btn-approve { padding: 6px 14px; border-radius: 8px; border: none; cursor: pointer; background: #22c55e; color: #fff; font-weight: 700; font-size: 0.82rem; display: flex; align-items: center; gap: 5px; }
.btn-reject  { padding: 6px 14px; border-radius: 8px; border: none; cursor: pointer; background: #ef4444; color: #fff; font-weight: 700; font-size: 0.82rem; display: flex; align-items: center; gap: 5px; }
.status-pill { padding: 3px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; }
.status-pill.pending  { background: rgba(251,191,36,.15); color: #f59e0b; }
.status-pill.approved { background: rgba(34,197,94,.15);  color: #22c55e; }
.status-pill.rejected { background: rgba(239,68,68,.15);  color: #ef4444; }

/* ── Campaign form ──────────────────────────────── */
.campaign-form-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-light);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 28px;
}
.campaign-form-card h3 {
    margin: 0 0 18px;
    font-size: 1rem; font-weight: 800;
    display: flex; align-items: center; gap: 8px;
}
.campaign-list-table { width: 100%; border-collapse: collapse; }
.campaign-list-table th,
.campaign-list-table td { padding: 10px 14px; text-align: left; font-size: 0.85rem; border-bottom: 1px solid var(--border-light); }
.campaign-list-table th { font-weight: 700; color: var(--text-secondary); font-size: 0.78rem; text-transform: uppercase; }
.sent-badge { background: rgba(34,197,94,.15); color: #22c55e; border-radius: 20px; padding: 2px 9px; font-size: 0.72rem; font-weight: 700; }
.failed-badge { background: rgba(239,68,68,.15); color: #ef4444; border-radius: 20px; padding: 2px 9px; font-size: 0.72rem; font-weight: 700; }
.draft-badge { background: rgba(148,163,184,.15); color: #94a3b8; border-radius: 20px; padding: 2px 9px; font-size: 0.72rem; font-weight: 700; }

.empty-state { text-align: center; padding: 60px 20px; color: var(--text-secondary); border: 1px dashed var(--border-light); border-radius: 16px; }
.empty-state .material-icons-round { font-size: 3rem; display: block; margin-bottom: 12px; opacity: .4; }
</style>

<!-- Page Header -->
<div class="admin-page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <h1 style="margin:0; font-size:1.6rem; font-weight:800;">
            <span class="material-icons-round" style="vertical-align:middle; color:var(--accent);">hub</span>
            Hub de Comunicação
        </h1>
        <p style="margin:4px 0 0; color:var(--text-secondary); font-size:0.9rem;">Leads, comentários e campanhas — tudo em um lugar.</p>
    </div>
    <a href="<?= base_url('c/' . $tenantSlug) ?>" target="_blank"
       style="display:flex; align-items:center; gap:6px; padding:8px 16px; border-radius:10px; background:var(--bg-elevated); border:1px solid var(--border-light); color:var(--text-secondary); text-decoration:none; font-size:0.85rem; font-weight:600;">
        <span class="material-icons-round" style="font-size:18px;">open_in_new</span>
        Ver Hub Público
    </a>
</div>

<!-- Stats Row -->
<div class="hub-stats">
    <div class="hub-stat-card">
        <div class="hub-stat-icon" style="background:rgba(0,204,255,0.12);">
            <span class="material-icons-round" style="color:#00ccff;">person_add</span>
        </div>
        <div>
            <div class="hub-stat-value" style="color:#00ccff;"><?= $stats['new_leads'] ?></div>
            <div class="hub-stat-label">Novos Leads</div>
        </div>
    </div>
    <div class="hub-stat-card">
        <div class="hub-stat-icon" style="background:rgba(251,191,36,0.12);">
            <span class="material-icons-round" style="color:#f59e0b;">mark_chat_unread</span>
        </div>
        <div>
            <div class="hub-stat-value" style="color:#f59e0b;"><?= $stats['pending_comments'] ?></div>
            <div class="hub-stat-label">Comentários Pendentes</div>
        </div>
    </div>
    <div class="hub-stat-card">
        <div class="hub-stat-icon" style="background:rgba(0,224,122,0.12);">
            <span class="material-icons-round" style="color:#00e07a;">how_to_reg</span>
        </div>
        <div>
            <div class="hub-stat-value" style="color:#00e07a;"><?= $stats['converted_leads'] ?></div>
            <div class="hub-stat-label">Leads Convertidos</div>
        </div>
    </div>
    <div class="hub-stat-card">
        <div class="hub-stat-icon" style="background:rgba(139,92,246,0.12);">
            <span class="material-icons-round" style="color:#8b5cf6;">campaign</span>
        </div>
        <div>
            <div class="hub-stat-value" style="color:#8b5cf6;"><?= $stats['total_campaigns'] ?></div>
            <div class="hub-stat-label">Campanhas</div>
        </div>
    </div>
</div>

<!-- Main Tabs -->
<div class="hub-tabs">
    <a href="<?= $baseUrl ?>?tab=leads" class="hub-tab <?= $tab === 'leads' ? 'active' : '' ?>">
        <span class="material-icons-round" style="font-size:18px;">person_add</span>
        Leads
        <?php if ($stats['new_leads'] > 0): ?>
        <span class="badge alert"><?= $stats['new_leads'] ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= $baseUrl ?>?tab=comentarios" class="hub-tab <?= $tab === 'comentarios' ? 'active' : '' ?>">
        <span class="material-icons-round" style="font-size:18px;">comment</span>
        Comentários
        <?php if ($stats['pending_comments'] > 0): ?>
        <span class="badge alert"><?= $stats['pending_comments'] ?></span>
        <?php endif; ?>
    </a>
    <a href="<?= $baseUrl ?>?tab=campanhas" class="hub-tab <?= $tab === 'campanhas' ? 'active' : '' ?>">
        <span class="material-icons-round" style="font-size:18px;">campaign</span>
        Campanhas
    </a>
    <a href="<?= $baseUrl ?>?tab=galeria" class="hub-tab <?= $tab === 'galeria' ? 'active' : '' ?>">
        <span class="material-icons-round" style="font-size:18px;">collections</span>
        Galeria
        <?php if ($stats['highlighted_media'] > 0): ?>
        <span class="badge"><?= $stats['highlighted_media'] ?></span>
        <?php endif; ?>
    </a>
</div>

<?php /* ══ TAB: LEADS ══════════════════════════════════════════════ */ ?>
<?php if ($tab === 'leads'): ?>

<div class="lead-filter-tabs">
    <?php
    $leadLabels = ['new' => 'Novos', 'contacting' => 'Em Contato', 'converted' => 'Convertidos', 'dismissed' => 'Descartados'];
    $currentLeadFilter = $_GET['lead_status'] ?? 'new';
    foreach ($leadLabels as $key => $label):
    ?>
    <a href="<?= $baseUrl ?>?tab=leads&lead_status=<?= $key ?>" class="lead-filter-tab <?= $currentLeadFilter === $key ? 'active' : '' ?>">
        <?= $label ?> <span style="opacity:.7;">(<?= $leadCounts[$key] ?>)</span>
    </a>
    <?php endforeach; ?>
</div>

<?php
$filteredLeads = array_filter($leads, fn($l) => $l['status'] === $currentLeadFilter);
$filteredLeads = array_values($filteredLeads);
?>

<?php if (empty($filteredLeads)): ?>
<div class="empty-state">
    <span class="material-icons-round">person_search</span>
    Nenhum lead com status "<?= htmlspecialchars($leadLabels[$currentLeadFilter] ?? $currentLeadFilter) ?>".
    <?php if ($currentLeadFilter === 'new'): ?>
    <p style="margin-top:8px;font-size:0.85rem;">Quando visitantes clicarem em "Quero Participar" no Hub Público, aparecerão aqui.</p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="lead-grid">
<?php foreach ($filteredLeads as $lead): ?>
<div class="lead-card" id="lead-<?= $lead['id'] ?>">
    <div class="lead-card__name">
        <span class="material-icons-round">person</span>
        <?= htmlspecialchars($lead['name']) ?>
        <span class="lead-status-pill <?= $lead['status'] ?>" style="margin-left:auto;">
            <?= $leadLabels[$lead['status']] ?? $lead['status'] ?>
        </span>
    </div>
    <div class="lead-card__meta">
        <?php if ($lead['phone']): ?>
        <span>
            <span class="material-icons-round">phone</span>
            <?= htmlspecialchars($lead['phone']) ?>
        </span>
        <?php endif; ?>
        <?php if ($lead['email']): ?>
        <span>
            <span class="material-icons-round">email</span>
            <?= htmlspecialchars($lead['email']) ?>
        </span>
        <?php endif; ?>
        <span>
            <span class="material-icons-round">schedule</span>
            <?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?>
        </span>
    </div>
    <?php if ($lead['message']): ?>
    <div class="lead-card__msg">"<?= htmlspecialchars($lead['message']) ?>"</div>
    <?php endif; ?>
    <div class="lead-card__actions">
        <?php if ($lead['phone']): ?>
        <?php $waMsg = urlencode("Olá {$lead['name']}! Vi que você tem interesse em conhecer o nosso clube Desbravador. Posso te ajudar com mais informações?"); ?>
        <a href="https://wa.me/<?= preg_replace('/\D/', '', $lead['phone']) ?>?text=<?= $waMsg ?>"
           target="_blank" class="btn-wa" onclick="markContacting(<?= $lead['id'] ?>)">
            <span class="material-icons-round" style="font-size:16px;">chat</span> WhatsApp
        </a>
        <?php endif; ?>
        <?php if ($lead['email']): ?>
        <a href="mailto:<?= htmlspecialchars($lead['email']) ?>?subject=Clube+Desbravador+–+Informações&body=Olá+<?= urlencode($lead['name']) ?>%2C%0A%0AObrigado+pelo+interesse...%0A"
           class="btn-email" onclick="markContacting(<?= $lead['id'] ?>)">
            <span class="material-icons-round" style="font-size:16px;">mail</span> E-mail
        </a>
        <?php endif; ?>
        <?php if ($lead['status'] !== 'converted'): ?>
        <button class="btn-status" onclick="updateLeadStatus(<?= $lead['id'] ?>, 'converted')">
            <span class="material-icons-round">how_to_reg</span> Convertido
        </button>
        <?php endif; ?>
        <?php if ($lead['status'] !== 'dismissed'): ?>
        <button class="btn-status" onclick="updateLeadStatus(<?= $lead['id'] ?>, 'dismissed')" style="color:#ef4444;border-color:rgba(239,68,68,0.3);">
            <span class="material-icons-round">block</span> Descartar
        </button>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php /* ══ TAB: COMENTÁRIOS ═════════════════════════════════════════ */ ?>
<?php elseif ($tab === 'comentarios'): ?>

<div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
<?php
$cTabLabels = ['pending' => 'Pendentes', 'approved' => 'Aprovados', 'rejected' => 'Rejeitados'];
foreach ($cTabLabels as $key => $label):
    $isActive = $commentStatusFilter === $key;
?>
<a href="<?= $baseUrl ?>?tab=comentarios&comment_status=<?= $key ?>"
   style="padding:6px 16px; border-radius:20px; font-weight:700; font-size:0.82rem; text-decoration:none;
          background:<?= $isActive ? 'var(--accent)' : 'var(--bg-elevated)' ?>;
          color:<?= $isActive ? '#000' : 'var(--text-secondary)' ?>;
          border:1px solid <?= $isActive ? 'var(--accent)' : 'var(--border-light)' ?>;">
    <?= $label ?> (<?= $commentCounts[$key] ?? 0 ?>)
</a>
<?php endforeach; ?>
</div>

<?php if (empty($comments)): ?>
<div class="empty-state">
    <span class="material-icons-round">inbox</span>
    Nenhum comentário com status "<?= htmlspecialchars($commentStatusFilter) ?>".
</div>
<?php else: ?>
<?php foreach ($comments as $c): ?>
<div class="comment-card" id="comment-<?= $c['id'] ?>">
    <div class="comment-header">
        <div>
            <span class="comment-author"><?= htmlspecialchars($c['author_name']) ?></span>
            <span class="comment-meta"> • <?= date('d/m/Y H:i', strtotime($c['created_at'])) ?></span>
        </div>
        <span class="status-pill <?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span>
    </div>
    <div class="comment-body"><?= nl2br(htmlspecialchars($c['content'])) ?></div>
    <div class="comment-source">Mídia: <strong><?= htmlspecialchars($c['source_type']) ?> #<?= (int)$c['source_id'] ?></strong></div>
    <?php if ($c['status'] === 'pending'): ?>
    <div class="comment-actions">
        <button class="btn-approve" onclick="moderateComment(<?= $c['id'] ?>, 'approved', this)">
            <span class="material-icons-round" style="font-size:16px;">check</span> Aprovar
        </button>
        <button class="btn-reject" onclick="moderateComment(<?= $c['id'] ?>, 'rejected', this)">
            <span class="material-icons-round" style="font-size:16px;">close</span> Rejeitar
        </button>
    </div>
    <?php elseif ($c['status'] === 'approved'): ?>
    <div class="comment-actions">
        <button class="btn-reject" onclick="moderateComment(<?= $c['id'] ?>, 'rejected', this)">
            <span class="material-icons-round" style="font-size:16px;">close</span> Rejeitar
        </button>
    </div>
    <?php else: ?>
    <div class="comment-actions">
        <button class="btn-approve" onclick="moderateComment(<?= $c['id'] ?>, 'approved', this)">
            <span class="material-icons-round" style="font-size:16px;">check</span> Aprovar
        </button>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php /* ══ TAB: CAMPANHAS ══════════════════════════════════════════ */ ?>
<?php elseif ($tab === 'campanhas'): ?>

<div class="campaign-form-card">
    <h3>
        <span class="material-icons-round" style="color:#8b5cf6;">send</span>
        Nova Campanha
    </h3>
    <form id="campaignForm">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
            <div class="form-group" style="margin:0;">
                <label>Título</label>
                <input type="text" name="title" class="form-control" required placeholder="Ex: Evento de Abertura do Ano">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group" style="margin:0;">
                    <label>Tipo</label>
                    <select name="type" class="form-control" style="background:var(--bg-dashboard);border:1px solid var(--border-light);color:var(--text-primary);padding:12px;border-radius:8px;width:100%;">
                        <option value="push">Push</option>
                        <option value="email">E-mail</option>
                        <option value="blast">Push + E-mail</option>
                    </select>
                </div>
                <div class="form-group" style="margin:0;">
                    <label>Público</label>
                    <select name="target_group" class="form-control" style="background:var(--bg-dashboard);border:1px solid var(--border-light);color:var(--text-primary);padding:12px;border-radius:8px;width:100%;">
                        <option value="members">Membros</option>
                        <option value="all">Todos</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group" style="margin-bottom:16px;">
            <label>Mensagem</label>
            <textarea name="content" class="form-control" rows="3" required placeholder="Escreva a mensagem da campanha..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="display:flex;align-items:center;gap:8px;">
            <span class="material-icons-round">send</span> Enviar Campanha
        </button>
    </form>
</div>

<div class="dashboard-card">
    <header class="dashboard-card-header">
        <span class="material-icons-round" style="color:#8b5cf6;">history</span>
        <h3>Histórico de Campanhas</h3>
    </header>
    <div class="dashboard-card-body" style="padding:0;">
        <?php if (empty($campaigns)): ?>
        <div class="empty-state" style="border:none;">
            <span class="material-icons-round">campaign</span>
            Nenhuma campanha enviada ainda.
        </div>
        <?php else: ?>
        <table class="campaign-list-table">
            <thead>
                <tr>
                    <th>Título</th><th>Tipo</th><th>Público</th><th>Enviados</th><th>Status</th><th>Data</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($campaigns as $c): ?>
            <tr>
                <td style="font-weight:600;"><?= htmlspecialchars($c['title']) ?></td>
                <td><?= strtoupper($c['type']) ?></td>
                <td><?= ucfirst($c['target_group']) ?></td>
                <td><?= number_format($c['sent_count']) ?></td>
                <td>
                    <?php if ($c['status'] === 'sent'): ?>
                    <span class="sent-badge">Enviado</span>
                    <?php elseif ($c['status'] === 'failed'): ?>
                    <span class="failed-badge">Falhou</span>
                    <?php else: ?>
                    <span class="draft-badge">Rascunho</span>
                    <?php endif; ?>
                </td>
                <td><?= $c['sent_at'] ? date('d/m/Y H:i', strtotime($c['sent_at'])) : date('d/m/Y', strtotime($c['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php /* ══ TAB: GALERIA ════════════════════════════════════════════ */ ?>
<?php else: ?>

<div class="gallery-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px;">
    <?php if (empty($highlightedMedia)): ?>
    <div class="empty-state" style="grid-column:1/-1;">
        <span class="material-icons-round">auto_awesome_motion</span>
        Nenhuma mídia em destaque no Hub Público.
        <p style="margin-top:8px; font-size:0.85rem;">Mídias destacadas durante as avaliações aparecerão aqui.</p>
    </div>
    <?php else: ?>
    <?php foreach ($highlightedMedia as $media): ?>
    <div class="media-highlight-card" id="media-<?= $media['id'] ?>" 
         style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:18px; overflow:hidden; display:flex; flex-direction:column; transition:transform .2s;">
        
        <!-- Media Preview -->
        <div style="aspect-ratio:16/9; background:#000; position:relative; overflow:hidden;">
            <?php if ($media['thumbnail_url']): ?>
                <img src="<?= htmlspecialchars($media['thumbnail_url']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,0.2);">
                    <span class="material-icons-round" style="font-size:40px;">play_circle_outline</span>
                </div>
            <?php endif; ?>
            <div style="position:absolute; top:10px; right:10px; background:rgba(0,0,0,0.6); color:#fff; padding:4px 8px; border-radius:6px; font-size:0.65rem; font-weight:800; text-transform:uppercase;">
                <?= htmlspecialchars($media['source_type'] === 'step' ? 'Missão' : 'Atividade') ?>
            </div>
        </div>

        <div style="padding:15px; flex:1; display:flex; flex-direction:column; gap:8px;">
            <div style="font-weight:800; font-size:0.9rem; line-height:1.3;"><?= htmlspecialchars($media['source_title']) ?></div>
            <div style="font-size:0.75rem; color:var(--text-secondary); display:flex; align-items:center; gap:5px;">
                <span class="material-icons-round" style="font-size:14px;">person</span>
                <?= htmlspecialchars($media['user_name']) ?>
            </div>
            
            <div style="margin-top:auto; padding-top:12px; border-top:1px solid var(--border-light); display:flex; gap:8px;">
                <a href="<?= htmlspecialchars($media['media_url']) ?>" target="_blank" 
                   style="flex:1; display:flex; align-items:center; justify-content:center; gap:5px; padding:8px; border-radius:10px; background:var(--bg-elevated); border:1px solid var(--border-light); color:var(--text-secondary); text-decoration:none; font-size:0.75rem; font-weight:700;">
                    <span class="material-icons-round" style="font-size:16px;">visibility</span> Ver Link
                </a>
                <button onclick="unhighlightFromHub(<?= $media['id'] ?>, this)" 
                        style="flex:1; display:flex; align-items:center; justify-content:center; gap:5px; padding:8px; border-radius:10px; background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); color:#ef4444; border:none; cursor:pointer; font-size:0.75rem; font-weight:700;">
                    <span class="material-icons-round" style="font-size:16px;">star_outline</span> Remover
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php endif; ?>

<script>
const HUB_BASE = '<?= base_url($tenantSlug . '/admin/comunicacao') ?>';

// ── Lead actions ──────────────────────────────────────────────────────────────
function markContacting(id) {
    fetch(`${HUB_BASE}/leads/${id}/update`, {
        method: 'POST',
        body: new URLSearchParams({ status: 'contacting', channel: 'whatsapp' })
    });
    const pill = document.querySelector(`#lead-${id} .lead-status-pill`);
    if (pill) { pill.className = 'lead-status-pill contacting'; pill.textContent = 'Em Contato'; }
}

async function updateLeadStatus(id, status) {
    const card = document.getElementById('lead-' + id);
    const res = await fetch(`${HUB_BASE}/leads/${id}/update`, {
        method: 'POST',
        body: new URLSearchParams({ status })
    });
    const data = await res.json();
    if (data.success) {
        card.style.transition = 'opacity .3s';
        card.style.opacity = '0';
        setTimeout(() => card.remove(), 320);
    }
}

// ── Comment moderation ────────────────────────────────────────────────────────
async function moderateComment(id, action, btn) {
    btn.disabled = true; btn.style.opacity = '.6';
    const fd = new FormData();
    fd.append('action', action);
    const res  = await fetch(`${HUB_BASE}/comentarios/${id}/update`, { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
        const card = document.getElementById('comment-' + id);
        card.style.transition = 'opacity .3s, transform .3s';
        card.style.opacity    = '0';
        card.style.transform  = 'translateX(20px)';
        setTimeout(() => card.remove(), 320);
    } else {
        alert('Erro: ' + (data.error || 'Falha'));
        btn.disabled = false; btn.style.opacity = '1';
    }
}

// ── Campaign form ─────────────────────────────────────────────────────────────
const campaignForm = document.getElementById('campaignForm');
if (campaignForm) {
    campaignForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = campaignForm.querySelector('[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round" style="animation:spin 1s linear infinite;">sync</span> Enviando...';
        const fd = new FormData(campaignForm);
        try {
            const res  = await fetch(`${HUB_BASE}/campanhas/enviar`, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.success) {
                alert('✅ ' + data.message);
                campaignForm.reset();
                setTimeout(() => location.reload(), 800);
            } else {
                alert('❌ ' + (data.error || 'Erro ao enviar'));
            }
        } catch(err) {
            alert('Erro de conexão.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="material-icons-round">send</span> Enviar Campanha';
        }
    });
}
// ── Gallery Management ───────────────────────────────────────────────────────
async function unhighlightFromHub(id, btn) {
    if (!confirm('Deseja remover esta mídia do Hub Público?')) return;
    
    btn.disabled = true; btn.style.opacity = '.6';
    try {
        const res  = await fetch(`${HUB_BASE}/galeria/${id}/unhighlight`, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            const card = document.getElementById('media-' + id);
            card.style.transition = 'opacity .3s, transform .3s';
            card.style.opacity    = '0';
            card.style.transform  = 'scale(0.9)';
            setTimeout(() => card.remove(), 320);
        } else {
            alert('Erro: ' + (data.error || 'Falha'));
            btn.disabled = false; btn.style.opacity = '1';
        }
    } catch(err) {
        alert('Erro de conexão.');
        btn.disabled = false; btn.style.opacity = '1';
    }
}
</script>
<style>
@keyframes spin { 100% { transform: rotate(360deg); } }
</style>
