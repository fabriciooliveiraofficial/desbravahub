<?php
/**
 * Admin — Public Hub Comment Moderation
 */
$tenantSlug = $tenant['slug'] ?? '';
$baseUrl    = base_url($tenantSlug . '/admin/comentarios-publicos');
?>

<style>
.comment-tabs { display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
.comment-tab {
    padding:8px 20px; border-radius:20px; border:1px solid var(--border);
    background:var(--bg-elevated); color:var(--text-secondary);
    text-decoration:none; font-weight:600; font-size:0.85rem;
    display:flex; align-items:center; gap:6px; transition:all .2s;
}
.comment-tab.active, .comment-tab:hover {
    background:var(--accent); color:#000; border-color:var(--accent);
}
.comment-tab .badge {
    background:rgba(0,0,0,0.2); color:inherit; font-size:0.75rem;
    padding:1px 7px; border-radius:10px; font-weight:700;
}
.comment-card {
    background:var(--bg-surface); border:1px solid var(--border);
    border-radius:16px; padding:20px; margin-bottom:16px;
    display:flex; flex-direction:column; gap:12px;
}
.comment-header { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px; }
.comment-author { font-weight:700; font-size:1rem; }
.comment-meta { font-size:0.8rem; color:var(--text-secondary); }
.comment-body { font-size:0.95rem; color:var(--text-primary); line-height:1.5; }
.comment-source { font-size:0.8rem; color:var(--text-secondary); }
.comment-actions { display:flex; gap:8px; }
.btn-approve {
    padding:6px 16px; border-radius:8px; border:none; cursor:pointer;
    background:#22c55e; color:#fff; font-weight:700; font-size:0.85rem;
    display:flex; align-items:center; gap:6px; transition:opacity .2s;
}
.btn-reject {
    padding:6px 16px; border-radius:8px; border:none; cursor:pointer;
    background:#ef4444; color:#fff; font-weight:700; font-size:0.85rem;
    display:flex; align-items:center; gap:6px; transition:opacity .2s;
}
.btn-approve:hover, .btn-reject:hover { opacity:0.85; }
.status-pill {
    padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700;
}
.status-pill.pending  { background:rgba(251,191,36,.15); color:#f59e0b; }
.status-pill.approved { background:rgba(34,197,94,.15); color:#22c55e; }
.status-pill.rejected { background:rgba(239,68,68,.15); color:#ef4444; }
.empty-state {
    text-align:center; padding:60px; color:var(--text-secondary);
    border:1px dashed var(--border); border-radius:20px;
}
</style>

<div class="admin-page-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
    <div>
        <h1 style="margin:0; font-size:1.6rem; font-weight:800;">
            <span class="material-icons-round" style="vertical-align:middle; color:var(--accent);">comment</span>
            Comentários do Hub Público
        </h1>
        <p style="margin:4px 0 0; color:var(--text-secondary); font-size:0.9rem;">Moderate comments submitted via the public club profile.</p>
    </div>
    <a href="<?= base_url('c/' . $tenantSlug) ?>" target="_blank"
       style="display:flex; align-items:center; gap:6px; padding:8px 16px; border-radius:10px; background:var(--bg-elevated); border:1px solid var(--border); color:var(--text-secondary); text-decoration:none; font-size:0.85rem; font-weight:600;">
        <span class="material-icons-round" style="font-size:18px;">open_in_new</span>
        Ver Hub Público
    </a>
</div>

<div class="comment-tabs">
    <?php
    $tabLabels = ['pending' => 'Pendentes', 'approved' => 'Aprovados', 'rejected' => 'Rejeitados'];
    foreach ($tabLabels as $key => $label):
        $isActive = $statusFilter === $key;
    ?>
    <a href="<?= $baseUrl ?>?status=<?= $key ?>" class="comment-tab <?= $isActive ? 'active' : '' ?>">
        <?= $label ?>
        <span class="badge"><?= $counts[$key] ?? 0 ?></span>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($comments)): ?>
    <div class="empty-state">
        <span class="material-icons-round" style="font-size:3rem; margin-bottom:12px; display:block; opacity:.4;">inbox</span>
        Nenhum comentário com status "<?= htmlspecialchars($statusFilter) ?>".
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

        <div class="comment-source">
            Mídia: <strong><?= htmlspecialchars($c['source_type']) ?> #<?= (int)$c['source_id'] ?></strong>
        </div>

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
        <?php elseif ($c['status'] === 'rejected'): ?>
        <div class="comment-actions">
            <button class="btn-approve" onclick="moderateComment(<?= $c['id'] ?>, 'approved', this)">
                <span class="material-icons-round" style="font-size:16px;">check</span> Aprovar
            </button>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
async function moderateComment(id, action, btn) {
    btn.disabled = true;
    btn.style.opacity = '0.6';
    try {
        const fd = new FormData();
        fd.append('action', action);
        const res = await fetch(`<?= base_url($tenantSlug . '/admin/comentarios-publicos/') ?>${id}/update`, {
            method: 'POST', body: fd
        });
        const data = await res.json();
        if (data.success) {
            const card = document.getElementById('comment-' + id);
            card.style.transition = 'opacity .3s, transform .3s';
            card.style.opacity    = '0';
            card.style.transform  = 'translateX(20px)';
            setTimeout(() => card.remove(), 320);
        } else {
            alert('Erro: ' + (data.error || 'Falha desconhecida'));
            btn.disabled = false; btn.style.opacity = '1';
        }
    } catch(e) {
        alert('Erro de rede.');
        btn.disabled = false; btn.style.opacity = '1';
    }
}
</script>
