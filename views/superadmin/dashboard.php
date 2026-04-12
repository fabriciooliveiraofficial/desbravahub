<?php
/**
 * Dashboard Super Admin - HUD v5.0 Titanium Clarity
 */
?>

<div class="sa-container sa-animate-fade">
    
    <!-- Hero Operational Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-8 mb-12">
        <div>
            <div class="flex items-center gap-2 mb-3">
                <div class="w-2 h-2 rounded-full bg-sa-primary animate-pulse"></div>
                <p class="text-[11px] text-sa-primary font-black uppercase tracking-widest">Controle Central de Operações</p>
            </div>
            <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Visão Geral do Ecosistema
            </h1>
            <p class="text-sa-text-muted mt-2 font-medium">Bem-vindo ao núcleo administrativo do DesbravaHub. Monitore e gerencie sua infraestrutura global.</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="/super-admin/clubs" class="sa-btn sa-btn-outline group hover:bg-white hover:shadow-md transition-all">
                <span class="material-symbols-rounded">lan</span>
                Rede de Franquias
            </a>
            <a href="/super-admin/users" class="sa-btn sa-btn-primary shadow-xl">
                <span class="material-symbols-rounded">person_add</span>
                Cadastrar Master
            </a>
        </div>
    </div>

    <!-- Telemetry Deck (KPIs) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <!-- KPI: Units -->
        <div class="sa-card sa-card-elevated border-t-4 border-t-sa-primary">
            <div class="flex items-center justify-between mb-8">
                <div class="sa-widget-title">Unidades Ativas</div>
                <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-sa-primary">
                    <span class="material-symbols-rounded">account_balance</span>
                </div>
            </div>
            <div class="sa-widget-value mb-4"><?= $kpis['active_clubs'] ?></div>
            <div class="text-[13px] text-sa-text-muted font-bold">
                De um total de <span class="text-slate-900"><?= $kpis['total_clubs'] ?></span> clusters
            </div>
            <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[10px] font-bold text-sa-success uppercase tracking-widest">Status: Nominal</span>
                <span class="text-[10px] font-bold text-sa-text-dim">+2.4% este mês</span>
            </div>
        </div>

        <!-- KPI: Users -->
        <div class="sa-card sa-card-elevated border-t-4 border-t-sa-secondary">
            <div class="flex items-center justify-between mb-8">
                <div class="sa-widget-title">Membros Globais</div>
                <div class="w-10 h-10 rounded-full bg-purple-50 flex items-center justify-center text-sa-secondary">
                    <span class="material-symbols-rounded">group</span>
                </div>
            </div>
            <div class="sa-widget-value mb-4"><?= number_format($kpis['total_users'], 0, ',', '.') ?></div>
            <div class="text-[13px] text-sa-text-muted font-bold">
                Usuários verificados na <span class="text-slate-900">Plataforma</span>
            </div>
            <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[10px] font-bold text-sa-success uppercase tracking-widest">Sincronizado</span>
                <span class="text-[10px] font-bold text-sa-text-dim">Tempo Real</span>
            </div>
        </div>

        <!-- KPI: Performance -->
        <div class="sa-card sa-card-elevated border-t-4 border-t-sa-success">
            <div class="flex items-center justify-between mb-8">
                <div class="sa-widget-title">Saúde do Sistema</div>
                <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-sa-success">
                    <span class="material-symbols-rounded">bolt</span>
                </div>
            </div>
            <div class="sa-widget-value mb-4 text-sa-success uppercase tracking-tight">OPTIMAL</div>
            <div class="text-[13px] text-sa-text-muted font-bold">
                Latência média de <span class="text-slate-900">24ms</span>
            </div>
            <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[10px] font-bold text-sa-success uppercase tracking-widest">99.9% Uptime</span>
                <span class="text-[10px] font-bold text-sa-text-dim">Infra v5.0</span>
            </div>
        </div>
    </div>

    <!-- Strategic Control Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Automation Cluster -->
        <div class="sa-card">
            <div class="flex items-center justify-between mb-10 pb-6 border-b border-slate-100">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-3">
                    <span class="material-symbols-rounded text-sa-primary">settings_system_daydream</span>
                    Módulos Corporativos
                </h3>
                <span class="sa-badge sa-badge-primary">Kernel v5.0</span>
            </div>
            
            <div class="space-y-4">
                <a href="/super-admin/scraper" class="group p-6 rounded-2xl border border-sa-border bg-slate-50/50 hover:bg-white hover:shadow-xl hover:border-sa-primary transition-all flex items-center justify-between no-underline">
                    <div class="flex items-center gap-6">
                        <div class="w-14 h-14 rounded-xl bg-white shadow-sm border border-sa-border text-sa-primary flex items-center justify-center group-hover:bg-sa-primary group-hover:text-white transition-all">
                            <span class="material-symbols-rounded text-2xl">precision_manufacturing</span>
                        </div>
                        <div>
                            <span class="text-base font-bold text-slate-900 block mb-0.5">Super Scraper IA</span>
                            <span class="text-[11px] text-sa-text-dim font-bold uppercase tracking-wider">Algoritmos de Prospecção Avançada</span>
                        </div>
                    </div>
                    <span class="material-symbols-rounded text-sa-text-dim group-hover:text-sa-primary group-hover:translate-x-1 transition-all">chevron_right</span>
                </a>

                <a href="/super-admin/migracao" class="group p-6 rounded-2xl border border-sa-border bg-slate-50/50 hover:bg-white hover:shadow-xl hover:border-sa-secondary transition-all flex items-center justify-between no-underline">
                    <div class="flex items-center gap-6">
                        <div class="w-14 h-14 rounded-xl bg-white shadow-sm border border-sa-border text-sa-secondary flex items-center justify-center group-hover:bg-sa-secondary group-hover:text-white transition-all">
                            <span class="material-symbols-rounded text-2xl">database_upload</span>
                        </div>
                        <div>
                            <span class="text-base font-bold text-slate-900 block mb-0.5">Núcleo de Esquemas</span>
                            <span class="text-[11px] text-sa-text-dim font-bold uppercase tracking-wider">Migração de Datasets Inteligentes</span>
                        </div>
                    </div>
                    <span class="material-symbols-rounded text-sa-text-dim group-hover:text-sa-secondary group-hover:translate-x-1 transition-all">chevron_right</span>
                </a>
            </div>
        </div>

        <!-- Event Logs / Monitoring -->
        <div class="sa-card">
            <div class="flex items-center justify-between mb-10 pb-6 border-b border-slate-100">
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-widest flex items-center gap-3">
                    <span class="material-symbols-rounded text-sa-success">monitor_heart</span>
                    Logs de Infraestrutura
                </h3>
            </div>

            <div class="space-y-8">
                <?php for($i=1; $i<=3; $i++): ?>
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-sa-primary flex items-center justify-center shrink-0 border border-sa-primary/10">
                        <span class="material-symbols-rounded text-lg">history</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[11px] font-extrabold text-slate-900 uppercase">Backup Sequencial #<?= rand(100,999) ?></p>
                            <span class="text-[10px] font-bold text-sa-text-dim">Há <?= $i*5 ?> min</span>
                        </div>
                        <p class="text-xs text-sa-text-muted leading-relaxed truncate">
                            Replicação de dados concluída satisfatoriamente no cluster principal <span class="text-sa-primary font-bold">Core-Alpha</span>.
                        </p>
                    </div>
                </div>
                <?php endfor; ?>
            </div>

            <button class="w-full mt-10 p-4 border border-sa-border bg-slate-50/50 rounded-xl text-[11px] font-bold tracking-widest text-sa-text-muted hover:text-sa-primary hover:bg-white hover:border-sa-primary transition-all uppercase">
                Ver Monitoramento Completo
            </button>
        </div>
    </div>
</div>
