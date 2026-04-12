<?php
/**
 * @var array $user
 * @var string $pageTitle
 * Sincronização de Dados Global - HUD v4.0 Master Control
 */
?>
<div class="animate-in" style="animation-delay: 0.1s;">
    <!-- Cabeçalho -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white mb-1">Módulo de Migração Estrutural</h1>
        <p class="text-sa-text-muted text-xs uppercase font-bold tracking-[0.2em]">Sincronização Binária de Ambientes Clusterizados</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Coluna de Exportação (Snapshot) -->
        <div class="sa-card bg-sa-surface-solid/40 border-l-4 border-l-sa-primary relative overflow-hidden flex flex-col">
            <div class="absolute top-0 right-0 w-48 h-48 bg-sa-primary/10 rounded-full blur-[80px] -mr-24 -mt-24 pointer-events-none"></div>
            
            <div class="flex items-center gap-4 mb-8 relative z-10">
                <div class="w-12 h-12 rounded-xl bg-sa-primary/20 text-sa-primary flex items-center justify-center border border-sa-primary/30">
                    <span class="material-symbols-rounded text-2xl font-bold">cloud_download</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white tracking-tight">Gerar Snapshot de Segurança</h2>
                    <p class="text-[10px] text-sa-text-muted uppercase font-extrabold tracking-widest">Compilação Integral .SQL</p>
                </div>
            </div>

            <div class="bg-sa-bg/60 rounded-xl p-5 border border-sa-border mb-8 flex-1 relative z-10">
                <div class="grid grid-cols-1 gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-sa-primary shadow-[0_0_8px_var(--sa-primary)] animate-pulse"></div>
                        <span class="text-xs text-white font-medium">Backup de Estrutura e Inserções Binárias</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full bg-sa-primary shadow-[0_0_8px_var(--sa-primary)] animate-pulse"></div>
                        <span class="text-xs text-white font-medium">Auto-limpeza (DROP TABLE) embutida</span>
                    </div>
                    <div class="flex items-center gap-3 opacity-60">
                        <div class="w-2 h-2 rounded-full bg-sa-primary"></div>
                        <span class="text-xs text-white">Otimização de Buffers para MySQL 8.0+</span>
                    </div>
                </div>
            </div>

            <a href="/super-admin/migracao/exportar" class="sa-btn sa-btn-primary w-full py-5 text-sm font-bold tracking-[0.1em] relative z-10 shadow-[0_0_20px_rgba(0,242,255,0.1)]">
                <span class="material-symbols-rounded text-lg">download</span>
                INICIAR DUMP ESTRUTURAL
            </a>
        </div>

                    <span class="material-symbols-rounded text-3xl">warning</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Importar (Substituir BD)</h2>
                    <p class="text-sm text-red-400 font-medium italic">Esta ação apagará todos os dados atuais localmente.</p>
                </div>
            </div>

            <form id="importForm" class="space-y-6">
                <div class="bg-sa-bg/50 rounded-2xl p-6 border border-red-500/10 mb-2">
                    <p class="text-sm text-sa-text-muted mb-6">
                        Faça o upload do arquivo <code>.sql</code> gerado pela ferramenta. 
                        A importação suspenderá chaves estrangeiras e fará o clone completo.
                    </p>
                    
                    <div class="relative border-2 border-dashed border-white/10 rounded-2xl p-8 hover:border-sa-primary/50 transition-all text-center cursor-pointer group" id="dropzone">
                        <input type="file" id="dump_file" name="dump_file" accept=".sql" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="w-16 h-16 rounded-full bg-sa-surface-light text-sa-text-muted flex items-center justify-center mx-auto mb-4 group-hover:bg-sa-primary group-hover:text-white transition-all">
                            <span class="material-symbols-rounded text-3xl">upload_file</span>
                        </div>
                        <div class="text-base font-bold text-white mb-2" id="filename-display_title">Clique ou arraste o arquivo .sql</div>
                        <div class="text-xs text-sa-text-muted" id="filename-display">Tamanho máximo recomendado: 50MB</div>
                    </div>
                </div>

                <div id="importError" class="hidden animate-fade-in bg-red-500/10 border border-red-500/20 rounded-2xl p-5 text-red-400 text-sm flex items-start gap-4">
                    <span class="material-symbols-rounded text-2xl shrink-0">error</span>
                    <div id="importErrorText" class="font-medium"></div>
                </div>

                <div id="importSuccess" class="hidden animate-fade-in bg-emerald-500/10 border border-emerald-500/20 rounded-2xl p-5 text-emerald-400 text-sm flex items-start gap-4">
                    <span class="material-symbols-rounded text-2xl shrink-0">check_circle</span>
                    <div id="importSuccessText" class="font-medium"></div>
                </div>

                <button type="submit" id="btnImport"
                        class="sa-btn sa-btn-danger w-full py-4 text-lg">
                    <span class="material-symbols-rounded">upload</span>
                    Iniciar Importação e Sobrescrever Dados
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('dump_file');
    const filenameDisplay = document.getElementById('filename-display');
    const importForm = document.getElementById('importForm');
    const btnImport = document.getElementById('btnImport');
    const errorDiv = document.getElementById('importError');
    const errorText = document.getElementById('importErrorText');
    const successDiv = document.getElementById('importSuccess');
    const successText = document.getElementById('importSuccessText');

    // Display selected filename
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            filenameDisplay.textContent = e.target.files[0].name;
            filenameDisplay.classList.add('text-sa-primary');
        } else {
            filenameDisplay.textContent = 'Tamanho máximo recomendado: 50MB';
            filenameDisplay.classList.remove('text-sa-primary');
        }
    });

    // Handle form submission
    importForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (fileInput.files.length === 0) {
            errorText.textContent = 'Por favor, selecione um arquivo .sql para importar.';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (!confirm('ATENÇÃO: Você tem certeza que deseja SUBSTITUIR todo o banco de dados atual? Esta ação não pode ser desfeita.')) {
            return;
        }

        // Reset UI
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');
        const originalBtnText = btnImport.innerHTML;
        btnImport.innerHTML = '<span class="material-symbols-rounded animate-spin">refresh</span> Processando... Pode demorar alguns instantes.';
        btnImport.disabled = true;
        btnImport.classList.add('opacity-50', 'cursor-not-allowed');

        const formData = new FormData(importForm);

        try {
            const response = await fetch('/super-admin/migracao/importar', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successText.textContent = data.message || 'Importação finalizada com sucesso!';
                successDiv.classList.remove('hidden');
                importForm.reset();
                filenameDisplay.textContent = 'Tamanho máximo recomendado: 50MB';
                filenameDisplay.classList.remove('text-sa-primary');
            } else {
                throw new Error(data.error || 'Erro desconhecido ao importar banco de dados.');
            }
        } catch (error) {
            errorText.textContent = error.message;
            errorDiv.classList.remove('hidden');
        } finally {
            // Restore button
            btnImport.innerHTML = originalBtnText;
            btnImport.disabled = false;
            btnImport.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });
});
</script>
