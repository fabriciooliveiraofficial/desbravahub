<?php
/**
 * Gerenciamento de Usuários - HUD v5.0 Titanium Clarity
 */
?>

<div class="sa-container sa-animate-fade">
    <!-- Header Strategy -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-8 mb-10 pb-8 border-b border-slate-100">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="w-1.5 h-6 bg-sa-secondary rounded-full"></span>
                <p class="text-sa-secondary text-[11px] font-black uppercase tracking-widest">Controle de Identidade</p>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Diretório Master Access</h1>
        </div>
        <div class="flex flex-wrap items-center gap-4">
            <div class="relative group">
                <span class="material-symbols-rounded absolute left-4 top-1/2 -translate-y-1/2 text-sa-text-dim text-lg">search</span>
                <input type="text" placeholder="Localizar operador..." class="sa-btn sa-btn-outline pl-12 bg-white border-sa-border focus:border-sa-primary outline-none transition-all text-xs font-semibold" style="width: 250px; height: 50px;">
            </div>
            <button class="sa-btn sa-btn-primary h-[50px] px-8 shadow-lg shadow-blue-500/20">
                <span class="material-symbols-rounded">person_add</span>
                Novo Administrador
            </button>
        </div>
    </div>

    <!-- User Directory Interface -->
    <div class="sa-card p-0 overflow-hidden shadow-xl border-slate-200">
        <div class="overflow-x-auto">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Assinatura Digital</th>
                        <th>Perfil do Operador</th>
                        <th>Nível de Permissão</th>
                        <th>Status Regional</th>
                        <th class="text-right">Ações de Controle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-slate-50/80 transition-all group">
                            <td class="px-10 py-6">
                                <span class="text-[11px] font-bold font-mono text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                                    USR-<?= str_pad($u['id'], 4, '0', STR_PAD_LEFT) ?>
                                </span>
                            </td>
                            <td class="px-10 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <?php if (!empty($u['avatar_url'])): ?>
                                            <img src="<?= htmlspecialchars($u['avatar_url']) ?>" alt="Avatar" class="w-12 h-12 rounded-xl object-cover border-2 border-white shadow-md">
                                        <?php else: ?>
                                            <div class="w-12 h-12 rounded-xl bg-blue-50 text-sa-primary flex items-center justify-center font-black text-sm border border-blue-100">
                                                <?= substr($u['name'], 0, 1) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-sa-success border-2 border-white"></div>
                                    </div>
                                    <div>
                                        <div class="text-base font-extrabold text-slate-900 leading-none mb-1"><?= htmlspecialchars($u['name']) ?></div>
                                        <div class="text-[11px] text-sa-text-muted font-semibold"><?= htmlspecialchars($u['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-10 py-6">
                                <div class="flex items-center gap-2">
                                    <?php if ($u['is_master']): ?>
                                        <span class="material-symbols-rounded text-sa-secondary text-base">verified</span>
                                        <span class="text-xs font-black text-sa-secondary uppercase tracking-[0.05em]">Root Master</span>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Colaborador Regional</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-10 py-6">
                                <span class="sa-badge <?= $u['is_master'] ? 'sa-badge-primary' : 'sa-badge-success' ?>">
                                    Ativo
                                </span>
                            </td>
                            <td class="px-10 py-6 text-right">
                                <div class="flex items-center justify-end gap-3 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <button class="w-10 h-10 rounded-lg border border-sa-border flex items-center justify-center text-sa-text-dim hover:text-sa-primary hover:bg-white hover:shadow-md hover:border-sa-primary transition-all">
                                        <span class="material-symbols-rounded text-xl">edit</span>
                                    </button>
                                    <button class="w-10 h-10 rounded-lg border border-sa-border flex items-center justify-center text-sa-text-dim hover:text-sa-error hover:bg-white hover:shadow-md hover:border-sa-error transition-all">
                                        <span class="material-symbols-rounded text-xl">delete_sweep</span>
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
