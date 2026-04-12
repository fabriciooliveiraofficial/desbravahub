<?php
/**
 * Gerenciamento de Clubes - HUD v5.0 Titanium Clarity
 */
?>

<div class="sa-container sa-animate-fade">
    <!-- Header: Dynamic Context -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-10 pb-8 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-1.5 h-6 bg-sa-primary rounded-full"></span>
                <p class="text-sa-primary text-[11px] font-black uppercase tracking-widest">Controle de Rede</p>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Rede de Franquias</h1>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="relative group">
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-sa-text-dim text-lg transition-colors group-focus-within:text-sa-primary">search</span>
                <input type="text" placeholder="Filtrar clusters..." class="sa-btn sa-btn-outline pl-12 bg-white border-sa-border focus:border-sa-primary outline-none transition-all text-xs font-semibold hover:shadow-md" style="width: 280px; height: 50px;">
            </div>
            <button class="sa-btn sa-btn-primary h-[50px] px-8 shadow-lg shadow-blue-500/20">
                <span class="material-symbols-rounded">add_business</span>
                Nova Unidade
            </button>
        </div>
    </div>

    <!-- Main Data Table -->
    <div class="sa-card p-0 overflow-hidden shadow-xl border-slate-200">
        <div class="overflow-x-auto">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Identificador #</th>
                        <th>Franquia / Registro</th>
                        <th>Vetor de Acesso</th>
                        <th>Status Operacional</th>
                        <th class="text-right">Gerenciar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($clubs as $club): ?>
                        <tr class="hover:bg-slate-50 transition-all group">
                            <td class="px-10 py-6">
                                <span class="text-[11px] font-bold font-mono text-slate-500 bg-slate-100 px-3 py-1.5 rounded-md border border-slate-200">
                                    ID-<?= str_pad($club['id'], 3, '0', STR_PAD_LEFT) ?>
                                </span>
                            </td>
                            <td class="px-10 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-sa-primary border border-blue-100 group-hover:bg-sa-primary group-hover:text-white transition-all">
                                        <span class="material-symbols-rounded text-2xl">account_balance</span>
                                    </div>
                                    <div>
                                        <div class="text-base font-bold text-slate-900 mb-0.5"><?= htmlspecialchars($club['name']) ?></div>
                                        <div class="flex items-center gap-2">
                                            <div class="w-1.5 h-1.5 rounded-full bg-sa-success"></div>
                                            <span class="text-[10px] text-sa-text-muted font-bold uppercase tracking-widest"><?= $club['member_count'] ?> Membros Operacionais</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-10 py-6">
                                <span class="text-sm font-semibold text-slate-700 font-mono tracking-tight">/<?= htmlspecialchars($club['slug']) ?></span>
                            </td>
                            <td class="px-10 py-6">
                                <span class="sa-badge sa-badge-primary">
                                    <?= htmlspecialchars($club['status']) ?>
                                </span>
                            </td>
                            <td class="px-10 py-6 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button class="w-10 h-10 rounded-lg border border-sa-border flex items-center justify-center text-sa-text-dim hover:text-sa-primary hover:bg-white hover:shadow-md hover:border-sa-primary transition-all">
                                        <span class="material-symbols-rounded text-xl">settings</span>
                                    </button>
                                    <button class="w-10 h-10 rounded-lg border border-sa-border flex items-center justify-center text-sa-text-dim hover:text-sa-primary hover:bg-white hover:shadow-md hover:border-sa-primary transition-all">
                                        <span class="material-symbols-rounded text-xl">visibility</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
