<?php
/**
 * Super Admin Dashboard View
 */
?>

<!-- Statistics -->
<div class="sa-stat-grid">
    <div class="sa-stat-card">
        <div class="sa-stat-title">Clubes Ativos / Total</div>
        <div class="sa-stat-value"><?= $kpis['active_clubs'] ?> <span style="font-size: 1.5rem; color: #64748b;">/ <?= $kpis['total_clubs'] ?></span></div>
        <span class="material-symbols-rounded" style="position:absolute; right:16px; bottom:16px; font-size:48px; color:rgba(255,255,255,0.05);">storefront</span>
    </div>
    
    <div class="sa-stat-card">
        <div class="sa-stat-title">Usuários Cadastrados</div>
        <div class="sa-stat-value"><?= number_format($kpis['total_users'], 0, ',', '.') ?></div>
        <span class="material-symbols-rounded" style="position:absolute; right:16px; bottom:16px; font-size:48px; color:rgba(255,255,255,0.05);">groups</span>
    </div>
</div>

<div class="sa-card">
    <h3 style="color: white; font-family: 'Outfit'; margin-bottom: 16px;">Controles Rápidos</h3>
    <p style="color: #94a3b8; margin-bottom: 24px;">Esta é a visão de alto nível do ecossistema. Use o menu lateral para gerenciar as franquias ou acesse a Inteligência Artificial no Scraper.</p>
</div>
