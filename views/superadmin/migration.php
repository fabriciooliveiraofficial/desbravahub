<?php
/**
 * @var array $user
 * @var string $pageTitle
 */
?>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white mb-2">Migração de Banco de Dados</h1>
            <p class="text-sa-text_muted">Exporte o ambiente atual ou importe dados de outro ambiente sincronizando as tabelas e linhas globais nativamente via PDO.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Export Section -->
        <div class="sa-card relative overflow-hidden">
            <div class="absolute top-0 right-0 w-32 h-32 bg-sa-primary/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
            
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-sa-primary/20 text-sa-primary flex items-center justify-center">
                    <span class="material-symbols-rounded">cloud_download</span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">Exportar Ambiente Atual</h2>
                    <p class="text-sm text-sa-text_muted">Gera um arquivo de backup (.sql) com toda estrutura e dados.</p>
                </div>
            </div>

            <div class="bg-black/20 rounded-xl p-4 border border-white/5 mb-6">
                <ul class="text-sm text-sa-text_muted space-y-2">
                    <li class="flex items-center gap-2"><span class="material-symbols-rounded text-xs text-green-400">check_circle</span> Formato Universal (MySQL compatível)</li>
                    <li class="flex items-center gap-2"><span class="material-symbols-rounded text-xs text-green-400">check_circle</span> Estrutura das Tabelas inclusa</li>
                    <li class="flex items-center gap-2"><span class="material-symbols-rounded text-xs text-green-400">check_circle</span> Inserção em Lote (Bulk Insert Otimizado)</li>
                </ul>
            </div>

            <a href="/super-admin/migracao/exportar" 
               class="w-full flex justify-center items-center gap-2 px-6 py-4 bg-sa-primary hover:bg-sa-primary_hover text-white rounded-xl font-medium transition-all shadow-lg active:scale-95">
                <span class="material-symbols-rounded">download</span>
                Baixar Dump (.sql)
            </a>
        </div>

        <!-- Import Section -->
        <div class="sa-card relative overflow-hidden border-red-500/20">
            <div class="absolute top-0 right-0 w-32 h-32 bg-red-500/10 rounded-full blur-3xl -mr-16 -mt-16"></div>
            
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-xl bg-red-500/20 text-red-500 flex items-center justify-center">
                    <span class="material-symbols-rounded">warning</span>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-white">Importar (Substituir BD)</h2>
                    <p class="text-sm text-red-400">Atenção: Ações irreversíveis. Todos os dados atuais serão sobrepostos.</p>
                </div>
            </div>

            <form id="importForm" class="space-y-6">
                <div class="bg-black/20 rounded-xl p-4 border border-red-500/10">
                    <p class="text-sm text-sa-text_muted mb-4">
                        Faça o upload do arquivo <code>.sql</code> gerado pela ferramenta de exportação. 
                        A importação suspenderá chaves estrangeiras, excluirá tabelas conflitantes e fará o clone completo.
                    </p>
                    
                    <div class="relative border-2 border-dashed border-white/10 rounded-xl p-6 hover:border-sa-primary/50 transition-colors text-center cursor-pointer" id="dropzone">
                        <input type="file" id="dump_file" name="dump_file" accept=".sql" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <span class="material-symbols-rounded text-4xl text-sa-text_muted mb-2">upload_file</span>
                        <div class="text-sm font-medium text-white mb-1">Clique ou arraste o arquivo .sql</div>
                        <div class="text-xs text-sa-text_muted" id="filename-display">Tamanho máximo recomendado: 50MB</div>
                    </div>
                </div>

                <div id="importError" class="hidden bg-red-500/10 border border-red-500/20 rounded-xl p-4 text-red-400 text-sm flex items-start gap-3">
                    <span class="material-symbols-rounded text-xl shrink-0">error</span>
                    <div id="importErrorText"></div>
                </div>

                <div id="importSuccess" class="hidden bg-green-500/10 border border-green-500/20 rounded-xl p-4 text-green-400 text-sm flex items-start gap-3">
                    <span class="material-symbols-rounded text-xl shrink-0">check_circle</span>
                    <div id="importSuccessText"></div>
                </div>

                <button type="submit" id="btnImport"
                        class="w-full flex justify-center items-center gap-2 px-6 py-4 bg-red-500/10 hover:bg-red-500 border border-red-500/20 text-white rounded-xl font-medium transition-all shadow-lg active:scale-95">
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
