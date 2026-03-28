<?php
/**
 * Communication Hub - Re-aligned with Dashboard Design
 */
?>

<div class="stats-grid">
    <!-- New Leads (Purple) -->
    <div class="stat-card purple">
        <div class="stat-card-bg-icon purple">
            <span class="material-icons-round">person_add</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">person_add</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['new_leads'] ?></span>
            <span class="stat-label">Novos Leads</span>
        </div>
    </div>

    <!-- Pending Comments (Amber) -->
    <div class="stat-card amber">
        <div class="stat-card-bg-icon amber">
            <span class="material-icons-round">mark_chat_unread</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">mark_chat_unread</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['pending_comments'] ?></span>
            <span class="stat-label">Comentários Pendentes</span>
        </div>
    </div>

    <!-- Converted Leads (Green) -->
    <div class="stat-card green">
        <div class="stat-card-bg-icon green">
            <span class="material-icons-round">verified</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">verified</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['converted_leads'] ?></span>
            <span class="stat-label">Leads Convertidos</span>
        </div>
    </div>

    <!-- Total Campaigns (Pink) -->
    <div class="stat-card pink">
        <div class="stat-card-bg-icon pink">
            <span class="material-icons-round">campaign</span>
        </div>
        <div class="stat-icon">
            <span class="material-icons-round">campaign</span>
        </div>
        <div class="stat-content">
            <span class="stat-value"><?= $stats['total_campaigns'] ?></span>
            <span class="stat-label">Campanhas Enviadas</span>
        </div>
    </div>
</div>

<!-- Main Navigation Tabs (Dashboard Styled) -->
<div class="dashboard-card" style="margin-bottom: 2rem;">
    <div class="dashboard-card-body" style="padding: 1rem;">
        <nav class="hub-nav" style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php 
                $tabs = [
                    'leads'     => ['icon' => 'group', 'label' => 'Leads'],
                    'comentarios'  => ['icon' => 'forum', 'label' => 'Moderação'],
                    'campanhas' => ['icon' => 'send', 'label' => 'Campanhas'],
                    'galeria'   => ['icon' => 'photo_library', 'label' => 'Galeria'],
                ];
                foreach ($tabs as $id => $t):
                    $active = ($tab === $id);
            ?>
                <a href="?tab=<?= $id ?>" class="btn <?= $active ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
                    <span class="material-icons-round" style="font-size: 18px;"><?= $t['icon'] ?></span>
                    <?= $t['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</div>

<div class="dashboard-grid" id="hub-content-area" style="grid-template-columns: 1fr;">
    <?php if ($tab === 'leads'): ?>
        <!-- LEADS SECTION -->
        <section class="dashboard-card">
            <div class="dashboard-card-header">
                <span class="material-icons-round" style="color: var(--accent-purple);">people</span>
                <h3>Gerenciamento de Leads</h3>
                <div style="margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap;">
                     <?php 
                     $leadStatusFilter = $_GET['lead_status'] ?? 'new';
                     foreach(['new' => 'Novos', 'contacting' => 'Em Contato', 'converted' => 'Convertidos', 'dismissed' => 'Arquivados'] as $sf => $sl): ?>
                        <a href="?tab=leads&lead_status=<?= $sf ?>" class="btn btn-sm <?= ($leadStatusFilter === $sf) ? 'btn-primary' : 'btn-secondary' ?>" style="font-size: 11px; padding: 4px 10px;">
                            <?= $sl ?> (<?= $leadCounts[$sf] ?>)
                        </a>
                     <?php endforeach; ?>
                </div>
            </div>
            
            <div class="dashboard-card-body">
                <?php 
                $filteredLeads = array_filter($leads, fn($l) => $l['status'] === $leadStatusFilter);
                if (empty($filteredLeads)): ?>
                    <div style="text-align: center; padding: 3rem; opacity: 0.5;">
                        <span class="material-icons-round" style="font-size: 48px;">person_search</span>
                        <p>Nenhum lead encontrado nesta categoria.</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                        <?php foreach($filteredLeads as $lead): ?>
                            <div class="dashboard-card" id="lead-<?= $lead['id'] ?>" style="box-shadow: none; border: 1px solid var(--border-color); background: var(--bg-hover); transform: none; animation: none;">
                                <div class="dashboard-card-body">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                        <div>
                                            <h4 style="margin: 0; font-size: 1rem; color: var(--text-dark);"><?= htmlspecialchars($lead['name']) ?></h4>
                                            <span style="font-size: 0.75rem; opacity: 0.6; display: block; margin-top: 2px;">
                                                <?= date('d/m/Y H:i', strtotime($lead['created_at'])) ?>
                                            </span>
                                        </div>
                                        <span class="badge badge-<?= ($lead['status'] === 'new') ? 'info' : (($lead['status'] === 'converted') ? 'success' : 'warning') ?>" style="text-transform: capitalize;">
                                            <?= $lead['status'] ?>
                                        </span>
                                    </div>

                                    <?php if ($lead['message']): ?>
                                        <div style="font-size: 0.85rem; background: var(--bg-card); padding: 10px; border-radius: 8px; border-left: 3px solid var(--primary); margin-bottom: 15px; font-style: italic;">
                                            "<?= htmlspecialchars($lead['message']) ?>"
                                        </div>
                                    <?php endif; ?>

                                    <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
                                            <span class="material-icons-round" style="font-size: 16px; color: var(--primary);">phone</span>
                                            <a href="tel:<?= $lead['phone'] ?>" style="color: inherit; text-decoration: none;"><?= htmlspecialchars($lead['phone'] ?? 'N/A') ?></a>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem;">
                                            <span class="material-icons-round" style="font-size: 16px; color: var(--primary);">email</span>
                                            <span style="overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($lead['email'] ?? 'N/A') ?></span>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 8px; margin-top: auto;">
                                        <?php if ($lead['phone']): ?>
                                            <?php $waMsg = urlencode("Olá " . explode(' ', $lead['name'])[0] . "! Recebemos seu interesse em participar do Clube de Desbravadores. Vamos conversar?"); ?>
                                            <a href="https://wa.me/<?= preg_replace('/\D/', '', $lead['phone']) ?>?text=<?= $waMsg ?>" target="_blank" onclick="markContacting(<?= $lead['id'] ?>)" class="btn btn-success btn-sm" style="flex: 1;">
                                                <i class="fab fa-whatsapp"></i> WhatsApp
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($lead['status'] !== 'converted'): ?>
                                            <button onclick="updateLeadStatus(<?= $lead['id'] ?>, 'converted')" class="btn btn-primary btn-sm" style="flex: 1;">
                                                <span class="material-icons-round" style="font-size: 16px;">verified</span> Converter
                                            </button>
                                        <?php endif; ?>

                                        <button onclick="updateLeadStatus(<?= $lead['id'] ?>, 'dismissed')" class="btn btn-secondary btn-sm" title="Arquivar">
                                            <span class="material-icons-round" style="font-size: 16px;">archive</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    <?php elseif ($tab === 'comentarios'): ?>
        <!-- COMMENTS SECTION -->
        <section class="dashboard-card">
            <div class="dashboard-card-header">
                <span class="material-icons-round" style="color: var(--accent-amber);">forum</span>
                <h3>Moderação de Comentários</h3>
                <div style="margin-left: auto; display: flex; gap: 8px;">
                    <a href="?tab=comentarios&comment_status=pending" class="btn btn-sm <?= ($commentStatusFilter === 'pending') ? 'btn-primary' : 'btn-secondary' ?>">Pendentes (<?= $commentCounts['pending'] ?>)</a>
                    <a href="?tab=comentarios&comment_status=approved" class="btn btn-sm <?= ($commentStatusFilter === 'approved') ? 'btn-primary' : 'btn-secondary' ?>">Aprovados (<?= $commentCounts['approved'] ?>)</a>
                </div>
            </div>
            
            <div class="dashboard-card-body">
                <?php if (empty($comments)): ?>
                    <div style="text-align: center; padding: 3rem; opacity: 0.5;">
                        <span class="material-icons-round" style="font-size: 48px;">chat_bubble_outline</span>
                        <p>Nenhum comentário nesta fila.</p>
                    </div>
                <?php else: ?>
                    <div class="summary-list">
                        <?php foreach($comments as $comment): ?>
                            <div class="summary-item" id="comment-<?= $comment['id'] ?>" style="padding: 1.25rem 0; align-items: flex-start; gap: 1.5rem;">
                                <div style="flex: 1;">
                                    <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 8px;">
                                        <div style="width: 36px; height: 36px; border-radius: 10px; background: var(--bg-dark); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 14px; color: var(--primary); border: 1px solid var(--border-color);">
                                            <?= strtoupper(substr($comment['author_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-dark); font-size: 0.95rem;"><?= htmlspecialchars($comment['author_name']) ?></div>
                                            <div style="font-size: 0.75rem; opacity: 0.6;"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></div>
                                        </div>
                                    </div>
                                    <p style="margin: 0; font-size: 0.95rem; line-height: 1.6; color: var(--text-main); background: var(--bg-hover); padding: 12px; border-radius: 12px; border: 1px solid var(--border-color);">
                                        "<?= htmlspecialchars($comment['content']) ?>"
                                    </p>
                                    <div style="margin-top: 8px; font-size: 0.7rem; color: var(--text-muted); opacity: 0.8; padding-left: 4px;">
                                        Origem: <?= htmlspecialchars($comment['source_type']) ?> #<?= $comment['source_id'] ?>
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <?php if ($comment['status'] === 'pending'): ?>
                                        <button onclick="moderateComment(<?= $comment['id'] ?>, 'approved')" class="btn btn-success btn-sm">
                                            <span class="material-icons-round" style="font-size: 16px;">check</span> Aprovar
                                        </button>
                                        <button onclick="moderateComment(<?= $comment['id'] ?>, 'rejected')" class="btn btn-secondary btn-sm">
                                            <span class="material-icons-round" style="font-size: 16px;">close</span> Rejeitar
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-<?= ($comment['status'] === 'approved') ? 'success' : 'danger' ?>">
                                            <?= $comment['status'] === 'approved' ? 'Aprovado' : 'Rejeitado' ?>
                                        </span>
                                        <?php if ($comment['status'] === 'approved'): ?>
                                            <button onclick="moderateComment(<?= $comment['id'] ?>, 'rejected')" class="btn btn-secondary btn-sm" style="margin-top: 4px; font-size: 10px;">Reverter</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

    <?php elseif ($tab === 'campanhas'): ?>
        <!-- CAMPAIGNS SECTION -->
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.5rem;">
            <!-- New Campaign -->
            <section class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="material-icons-round" style="color: var(--accent-emerald);">campaign</span>
                    <h3>Nova Campanha</h3>
                </div>
                <div class="dashboard-card-body">
                    <form id="campaignForm" onsubmit="sendCampaign(event)">
                        <div class="form-group">
                            <label>Título da Campanha</label>
                            <input type="text" name="title" class="form-control" placeholder="Título que aparecerá na notificação" required>
                        </div>
                        <div class="form-group">
                            <label>Conteúdo</label>
                            <textarea name="content" class="form-control" style="min-height: 120px;" placeholder="Mensagem principal..." required></textarea>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div class="form-group">
                                <label>Canal</label>
                                <select name="type" class="form-control">
                                    <option value="push">Push Notification</option>
                                    <option value="email">E-mail</option>
                                    <option value="blast" selected>Multicanal (Ambos)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Público</label>
                                <select name="target_group" class="form-control">
                                    <option value="members">Membros Ativos</option>
                                    <option value="leads">Apenas Leads</option>
                                    <option value="all">Públio Geral</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                            <span class="material-icons-round">rocket_launch</span> Disparar Campanha
                        </button>
                    </form>
                </div>
            </section>

            <!-- History -->
            <section class="dashboard-card">
                <div class="dashboard-card-header">
                    <span class="material-icons-round" style="color: var(--accent-pink);">history</span>
                    <h3>Histórico de Campanhas</h3>
                </div>
                <div class="dashboard-card-body" style="padding: 0;">
                    <div class="table-container" style="border: none; border-radius: 0; box-shadow: none;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Criação</th>
                                    <th>Campanha</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Envios</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($campaigns)): ?>
                                    <tr><td colspan="4" style="text-align:center; padding: 2rem; opacity: 0.5;">Nenhuma campanha anterior.</td></tr>
                                <?php endif; ?>
                                <?php foreach($campaigns as $camp): ?>
                                    <tr>
                                        <td>
                                            <div style="font-size: 0.85rem; font-weight: 600;"><?= date('d M', strtotime($camp['created_at'])) ?></div>
                                            <div style="font-size: 0.7rem; opacity: 0.6;"><?= date('H:i', strtotime($camp['created_at'])) ?></div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-dark);"><?= htmlspecialchars($camp['title']) ?></div>
                                            <div style="font-size: 0.7rem; opacity: 0.6;">Tipo: <?= strtoupper($camp['type']) ?> • Por: <?= htmlspecialchars($camp['creator_name'] ?? 'Sistema') ?></div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= ($camp['status'] === 'sent') ? 'success' : (($camp['status'] === 'failed') ? 'danger' : 'warning') ?>">
                                                <?= $camp['status'] ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <span style="font-weight: 700; font-size: 1.1rem; color: var(--primary);"><?= $camp['sent_count'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>

    <?php elseif ($tab === 'galeria'): ?>
        <!-- GALLERY SECTION -->
        <section class="dashboard-card">
            <div class="dashboard-card-header">
                <span class="material-icons-round" style="color: var(--accent-emerald);">photo_library</span>
                <h3>Destaques Públicos (Galeria)</h3>
                <div style="margin-left: auto;">
                    <span class="badge badge-info"><?= count($highlightedMedia) ?> Itens Ativos</span>
                </div>
            </div>
            <div class="dashboard-card-body">
                <div style="background: var(--bg-hover); padding: 1rem; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 2rem; display: flex; align-items: center; gap: 10px;">
                    <span class="material-icons-round" style="color: var(--primary);">info</span>
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted);">
                        Estes itens são exibidos na página pública do clube. Mídias são adicionadas aqui a partir da revisão de provas (Botão "Destacar").
                    </p>
                </div>

                <?php if (empty($highlightedMedia)): ?>
                    <div style="text-align: center; padding: 4rem; opacity: 0.3;">
                        <span class="material-icons-round" style="font-size: 64px;">filter_none</span>
                        <p>Nenhuma mídia destacada no momento.</p>
                    </div>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                        <?php foreach($highlightedMedia as $media): 
                            $rawUrl = $media['media_url'];
                            $isJson = ($rawUrl && ($rawUrl[0] === '[' || $rawUrl[0] === '{'));
                            $displayUrl = $isJson ? json_decode($rawUrl, true)[0] ?? '' : $rawUrl;
                        ?>
                            <div class="specialty-card" style="padding: 0; overflow: hidden; height: 100%;" id="media-<?= $media['id'] ?>">
                                <div style="height: 180px; position: relative; background: #000;">
                                    <img src="<?= $displayUrl ?>" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.src='/assets/images/placeholder-media.png'">
                                    <div style="position: absolute; top: 10px; right: 10px; display: flex; gap: 5px;">
                                        <a href="<?= $displayUrl ?>" target="_blank" class="btn btn-secondary btn-sm" style="padding: 6px; border-radius: 50%; min-width: 32px; height: 32px; background: rgba(255,255,255,0.9); border: none;">
                                            <span class="material-icons-round" style="font-size: 18px; color: var(--text-dark);">visibility</span>
                                        </a>
                                        <button onclick="unhighlightMedia(<?= $media['id'] ?>)" class="btn btn-danger btn-sm" style="padding: 6px; border-radius: 50%; min-width: 32px; height: 32px; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                                            <span class="material-icons-round" style="font-size: 18px;">close</span>
                                        </button>
                                    </div>
                                    <div style="position: absolute; bottom: 10px; left: 10px;">
                                        <span class="badge" style="background: var(--primary); color: white; border: none; padding: 4px 10px;"><?= strtoupper($media['source_type']) ?></span>
                                    </div>
                                </div>
                                <div style="padding: 1.25rem;">
                                    <h5 style="margin: 0 0 8px 0; font-size: 0.95rem; font-weight: 700; color: var(--text-dark); line-height: 1.5; height: 2.8em; overflow: hidden;"><?= htmlspecialchars($media['source_title']) ?></h5>
                                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 12px; font-size: 0.8rem; color: var(--text-muted); padding-top: 12px; border-top: 1px solid var(--border-color);">
                                        <div style="width: 24px; height: 24px; border-radius: 50%; background: var(--bg-dark); display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700;">
                                            <?= strtoupper(substr($media['user_name'] ?? 'M', 0, 1)) ?>
                                        </div>
                                        <span><?= htmlspecialchars($media['user_name'] ?? 'Membro') ?></span>
                                        <span style="margin-left: auto; opacity: 0.6; font-size: 0.7rem;"><?= date('d/m/Y', strtotime($media['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
/**
 * Hub Actions
 */
const HUB_ENDPOINT = '<?= base_url($tenant['slug'] . '/admin/comunicacao') ?>';

async function markContacting(id) {
    // Silently mark as contacting when starting WA chat
    try {
        const formData = new FormData();
        formData.append('status', 'contacting');
        formData.append('channel', 'whatsapp');
        await fetch(`${HUB_ENDPOINT}/leads/${id}/update`, { method: 'POST', body: formData });
        
        // Update UI locally
        const card = document.getElementById('lead-' + id);
        if (card) {
            const badge = card.querySelector('.badge');
            if (badge) {
                badge.className = 'badge badge-warning';
                badge.textContent = 'contacting';
            }
        }
    } catch (err) { console.error('Auto-contact log failed', err); }
}

async function updateLeadStatus(id, status) {
    if (status === 'dismissed' && !confirm('Arquivar este lead permanentemente?')) return;
    
    try {
        const formData = new FormData();
        formData.append('status', status);
        
        const response = await fetch(`${HUB_ENDPOINT}/leads/${id}/update`, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            window.toast?.success('Lead atualizado com sucesso!');
            const card = document.getElementById('lead-' + id);
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.95)';
            setTimeout(() => card.remove(), 400);
        } else {
            alert(data.error || 'Erro ao processar');
        }
    } catch (err) { console.error(err); }
}

async function moderateComment(id, action) {
    try {
        const formData = new FormData();
        formData.append('action', action);
        
        const response = await fetch(`${HUB_ENDPOINT}/comentarios/${id}/update`, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            window.toast?.success(action === 'approved' ? 'Comentário publicado!' : 'Comentário removido.');
            const item = document.getElementById('comment-' + id);
            item.style.transition = 'all 0.4s ease';
            item.style.opacity = '0';
            item.style.transform = 'translateX(20px)';
            setTimeout(() => item.remove(), 400);
        } else {
            alert(data.error || 'Erro na moderação');
        }
    } catch (err) { console.error(err); }
}

async function sendCampaign(event) {
    event.preventDefault();
    const form = event.target;
    const btn = form.querySelector('button[type="submit"]');
    const originalContent = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<span class="material-icons-round rotating" style="font-size: 18px;">sync</span> Enviando...';
        
        const formData = new FormData(form);
        const response = await fetch(`${HUB_ENDPOINT}/campanhas/enviar`, { method: 'POST', body: formData });
        const data = await response.json();
        
        if (data.success) {
            window.toast?.success(data.message);
            form.reset();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            alert(data.error || 'Erro ao enviar campanha');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    } catch (err) {
        console.error(err);
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}

async function unhighlightMedia(id) {
    if (!confirm('Remover este item da galeria pública?')) return;
    
    try {
        const response = await fetch(`${HUB_ENDPOINT}/galeria/${id}/unhighlight`, { method: 'POST' });
        const data = await response.json();
        if (data.success) {
            window.toast?.success('Item removido da galeria.');
            const card = document.getElementById('media-' + id);
            card.style.transition = 'all 0.4s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => card.remove(), 400);
        }
    } catch (err) { console.error(err); }
}
</script>

<style>
.rotating { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* Fix stats grid for this page context */
.stats-grid { margin-top: 0.5rem; }

/* Custom pill hover for nav */
.hub-nav .btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
    border: 1px solid var(--border-color);
}
.hub-nav .btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.hub-nav .btn.btn-primary {
    box-shadow: var(--shadow-cyan);
}
</style>
