<?php
/**
 * @var array $user
 * @var string $pageTitle
 */
?>
<div class="space-y-8 animate-fade-in">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">Migração de Banco de Dados</h1>
            <p class="text-sa-text-muted">Gerencie a sincronização de dados entre ambientes de forma nativa e segura.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Export Section -->
        <div class="sa-card relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-32 h-32 bg-sa-primary/5 rounded-full blur-3xl -mr-16 -mt-16 group-hover:bg-sa-primary/10 transition-colors"></div>
            
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 rounded-2xl bg-sa-primary/10 text-sa-primary flex items-center justify-center shadow-inner">
                    <span class="material-symbols-rounded text-3xl">cloud_download</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">Exportar Ambiente</h2>
                    <p class="text-sm text-sa-text-muted">Gera um snapshot completo (.sql) com toda estrutura e dados.</p>
                </div>
            </div>

            <div class="bg-sa-bg/50 rounded-2xl p-6 border border-sa-border mb-8">
                <ul class="text-sm text-sa-text-muted space-y-4">
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-emerald-400 text-lg">check_circle</span>
                        <span>Formato Universal SQL (Compatível com MySQL 8)</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-emerald-400 text-lg">check_circle</span>
                        <span>Limpeza Preventiva (DROP TABLE automática)</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="material-symbols-rounded text-emerald-400 text-lg">check_circle</span>
                        <span>Inserção em Lotes Otimizada (Bulk Insert)</span>
                    </li>
                </ul>
            </div>

            <a href="/super-admin/migracao/exportar" 
               class="sa-btn sa-btn-primary w-full py-4 text-lg">
                <span class="material-symbols-rounded">download</span>
                Baixar Dump (.sql)
            </a>
        </div>

        <!-- Import Section -->
        <div class="sa-card relative overflow-hidden border-red-500/20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-full blur-3xl -mr-16 -mt-16"></div>
            
            <div class="flex items-center gap-4 mb-8">
                <div class="w-14 h-14 rounded-2xl bg-red-500/10 text-red-500 flex items-center justify-center shadow-inner">
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
