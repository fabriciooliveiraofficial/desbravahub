<?php
/**
 * Super Admin Dashboard View
 */
?>

<div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Dashboard Global</h1>
            <p class="text-sa-text-muted">Visão geral de todos os clubes e usuários do ecossistema DesbravaHub.</p>
        </div>
        <div class="flex gap-3">
            <a href="/super-admin/clubs" class="sa-btn sa-btn-outline">
                <span class="material-symbols-rounded">storefront</span>
                Ver Clubes
            </a>
            <a href="/super-admin/users" class="sa-btn sa-btn-primary">
                <span class="material-symbols-rounded">person_add</span>
                Novo Usuário
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="sa-card relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <span class="material-symbols-rounded text-6xl">storefront</span>
            </div>
            <div class="sa-stat-label mb-1">Clubes Ativos / Total</div>
            <div class="sa-stat-value"><?= $kpis['active_clubs'] ?> <span class="text-sa-text-muted text-xl">/ <?= $kpis['total_clubs'] ?></span></div>
            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-emerald-400">
                <span class="material-symbols-rounded text-sm">check_circle</span>
                <span>Infraestrutura OK</span>
            </div>
        </div>

        <div class="sa-card relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <span class="material-symbols-rounded text-6xl">group</span>
            </div>
            <div class="sa-stat-label mb-1">Usuários Cadastrados</div>
            <div class="sa-stat-value"><?= number_format($kpis['total_users'], 0, ',', '.') ?></div>
            <div class="mt-4 flex items-center gap-2 text-xs font-semibold text-sa-primary">
                <span class="material-symbols-rounded text-sm">verified</span>
                <span>Contas Ativas</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="sa-card">
        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-2">
            <span class="material-symbols-rounded text-sa-primary">rocket_launch</span>
            Controles Rápidos
        </h3>
        <p class="text-sa-text-muted mb-6">Esta é a visão de alto nível do ecossistema. Use o menu lateral para gerenciar as franquias ou acesse a Inteligência Artificial no Scraper.</p>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="/super-admin/scraper" class="p-6 rounded-2xl bg-sa-bg/50 border border-sa-border hover:border-sa-primary transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-sa-primary/10 text-sa-primary flex items-center justify-center group-hover:bg-sa-primary group-hover:text-white transition-all">
                        <span class="material-symbols-rounded">smart_toy</span>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-white block">Super Scraper (IA)</span>
                        <span class="text-sm text-sa-text-muted">Prospecção de novos clubes</span>
                    </div>
                </div>
            </a>
            
            <a href="/super-admin/migracao" class="p-6 rounded-2xl bg-sa-bg/50 border border-sa-border hover:border-red-500/50 transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-500 flex items-center justify-center group-hover:bg-red-500 group-hover:text-white transition-all">
                        <span class="material-symbols-rounded">database</span>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-white block">Migração DB</span>
                        <span class="text-sm text-sa-text-muted">Backup e Recuperação</span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
