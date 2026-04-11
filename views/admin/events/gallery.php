<?php
/**
 * Admin Event Gallery View
 */
?>

<div class="header-container" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h2 style="margin: 0; font-size: 1.5rem; color: var(--text-primary);"><?= htmlspecialchars($event['title']) ?> - Galeria</h2>
        <p style="margin: 0.5rem 0 0; color: var(--text-secondary);">Gerencie as fotos e memórias deste evento.</p>
    </div>
    <div class="header-actions" style="display: flex; gap: 1rem;">
        <a href="<?= base_url($tenant['slug'] . '/admin/eventos') ?>" class="btn btn-secondary">
            <span class="material-icons-round">arrow_back</span> Voltar
        </a>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('upload-modal').style.display = 'flex'">
            <span class="material-icons-round">add_photo_alternate</span> Adicionar Foto
        </a>
    </div>
</div>

<div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1.5rem;">
    <?php if (empty($gallery)): ?>
        <div class="tech-plate" style="grid-column: 1 / -1; padding: 60px; text-align: center; background: var(--bg-surface); opacity: 0.7;">
            <span class="material-icons-round" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;">collections</span>
            <p style="color: var(--text-secondary); margin: 0;">Nenhuma foto adicionada ainda.</p>
        </div>
    <?php else: ?>
        <?php foreach ($gallery as $item): ?>
            <div class="tech-plate group" style="position: relative; overflow: hidden; padding: 0; border-radius: 12px; background: var(--bg-surface);">
                <img src="<?= htmlspecialchars($item['image_url']) ?>" style="width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block;">
                
                <div style="padding: 12px;">
                    <p style="margin: 0; font-size: 0.85rem; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= htmlspecialchars($item['caption'] ?: 'Sem legenda') ?>
                    </p>
                </div>

                <div class="overlay" style="position: absolute; inset: 0; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; opacity: 0; transition: all 0.2s;">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeImage(<?= $item['id'] ?>)">
                        <span class="material-icons-round">delete</span> Excluir
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Upload Modal -->
<div id="upload-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div class="dashboard-card" style="width: 90%; max-width: 500px; padding: 24px; border-top: 4px solid var(--accent-color);">
        <h3 style="margin: 0 0 1.5rem; color: var(--text-primary);">Adicionar Foto</h3>
        
        <form id="add-image-form">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">URL da Imagem</label>
                <input type="url" name="image_url" class="form-control" required placeholder="https://exemplo.com/foto.jpg" style="background: var(--bg-secondary); border: 1px solid var(--border-light); color: var(--text-primary); padding: 12px; width: 100%;">
            </div>
            
            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Legenda (Opcional)</label>
                <input type="text" name="caption" class="form-control" placeholder="Descreva este momento..." style="background: var(--bg-secondary); border: 1px solid var(--border-light); color: var(--text-primary); padding: 12px; width: 100%;">
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('upload-modal').style.display='none'">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar na Galeria</button>
            </div>
        </form>
    </div>
</div>

<style>
.dashboard-grid .tech-plate:hover .overlay {
    opacity: 1;
}
.dashboard-grid .tech-plate:hover img {
    transform: scale(1.05);
}
.tech-plate img {
    transition: transform 0.3s ease;
}
</style>

<script>
document.getElementById('add-image-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerText;
    btn.disabled = true;
    btn.innerText = 'Salvando...';

    const formData = new FormData(this);
    try {
        const response = await fetch('<?= base_url($tenant['slug'] . '/admin/eventos/' . $event['id'] . '/galeria') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.error);
            btn.disabled = false;
            btn.innerText = originalText;
        }
    } catch (err) {
        alert('Erro ao salvar imagem.');
        btn.disabled = false;
        btn.innerText = originalText;
    }
});

async function removeImage(id) {
    if (!confirm('Excluir esta foto da galeria?')) return;
    
    try {
        const response = await fetch('<?= base_url($tenant['slug'] . '/admin/eventos/galeria/') ?>' + id + '/excluir', {
            method: 'POST',
            headers: { 'Accept': 'application/json' }
        });
        const result = await response.json();
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.error);
        }
    } catch (err) {
        alert('Erro ao excluir imagem.');
    }
}
</script>
