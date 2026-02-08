<?php
/**
 * Step Modal Partial
 * 
 * Renders the content inside a step modal.
 * Variables: $step, $questions, $response
 */
$existingText = $response['response_text'] ?? '';
$existingUrl = $response['response_url'] ?? '';
$existingFile = $response['response_file'] ?? '';
$status = $response['status'] ?? 'not_started';
$feedback = $response['feedback'] ?? '';
?>

<div>
    <div class="status-line" style="width: 4px; left: 0;"></div>
    
    <!-- Modal Header -->
    <div class="step-modal-header">
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <span class="hud-badge" style="color: var(--accent-cyan); font-size: 0.6rem; border-color: rgba(0,217,255,0.3); background: rgba(0,217,255,0.05);">TREINAMENTO OPERACIONAL</span>
                <?php if ($step['is_required'] ?? false): ?>
                    <span style="font-size: 0.6rem; font-weight: 900; color: #f87171; letter-spacing: 0.1em; text-transform: uppercase;">[REQUISITO MANDATÓRIO]</span>
                <?php endif; ?>
            </div>
            <h2 style="font-size: 1.4rem; margin: 0; color: #fff; line-height: 1.2; font-weight: 800; letter-spacing: -0.02em;"><?= htmlspecialchars($step['title']) ?></h2>
        </div>
        <button type="button" onclick="closeModal()" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; cursor: pointer; padding: 6px; border-radius: 8px; transition: all 0.2s;">
            <i class="material-icons-round" style="font-size: 1.2rem;">close</i>
        </button>
    </div>

    <!-- Modal Body -->
    <div id="modal-content-area" class="modal-content-scroll">
        
        <?php if (!empty($step['description'])): ?>
            <div class="tech-plate" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.05); padding: 20px; margin-bottom: 30px; border-radius: 12px; position: relative;">
                <div style="font-size: 0.65rem; color: var(--accent-cyan); text-transform: uppercase; font-weight: 900; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; letter-spacing: 0.1em;">
                    <i class="material-icons-round" style="font-size: 1rem;">terminal</i> Briefing da Missão
                </div>
                <div style="color: #94a3b8; font-size: 0.95rem; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($step['description'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Status Displays -->
        <?php if ($status === 'approved'): ?>
            <div class="tech-plate vibrant-green" style="padding: 18px; margin-bottom: 30px; display: flex; align-items: center; gap: 16px; border-radius: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons-round" style="color: #22c55e; font-size: 2rem; filter: drop-shadow(0 0 8px #22c55e);">verified</i>
                </div>
                <div>
                    <div style="font-weight: 900; color: #fff; font-size: 0.95rem; letter-spacing: 0.05em;">SINCRONIZAÇÃO CONCLUÍDA</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">Os dados foram validados e aprovados pelo QG.</div>
                </div>
            </div>
        <?php elseif ($status === 'submitted'): ?>
            <div class="tech-plate vibrant-orange" style="padding: 18px; margin-bottom: 30px; display: flex; align-items: center; gap: 16px; border-radius: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(249, 115, 22, 0.1); border: 1px solid rgba(249, 115, 22, 0.2); display: flex; align-items: center; justify-content: center;">
                    <i class="material-icons-round" style="color: #f97316; font-size: 2rem; filter: drop-shadow(0 0 8px #f97316);">hourglass_empty</i>
                </div>
                <div>
                    <div style="font-weight: 900; color: #fff; font-size: 0.95rem; letter-spacing: 0.05em;">DADOS EM ANÁLISE</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">Aguardando processamento do monitoramento central.</div>
                </div>
            </div>
        <?php elseif ($status === 'rejected'): ?>
            <div class="tech-plate vibrant-red" style="padding: 18px; margin-bottom: 30px; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 10px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; justify-content: center;">
                        <i class="material-icons-round" style="color: #ef4444; font-size: 2rem; filter: drop-shadow(0 0 8px #ef4444);">gpp_maybe</i>
                    </div>
                    <div>
                        <div style="font-weight: 900; color: #fff; font-size: 0.95rem; letter-spacing: 0.05em;">REVISÃO SOLICITADA</div>
                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.6);">Amostra de dados inconsistente com os padrões.</div>
                    </div>
                </div>
                <?php if ($feedback): ?>
                    <div style="background: rgba(0,0,0,0.3); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 12px; font-size: 0.9rem; color: #fecaca; line-height: 1.4;">
                        <strong style="color: #ef4444; font-size: 0.7rem; text-transform: uppercase; display: block; margin-bottom: 4px;">Mensagem do Instrutor:</strong>
                        <?= htmlspecialchars($feedback) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form onsubmit="event.preventDefault(); submitStepForm(<?= $step['id'] ?>, this);" enctype="multipart/form-data" 
              style="<?= ($status === 'approved' || $status === 'submitted') ? 'opacity: 0.7; pointer-events: none;' : '' ?>">
            
            <?php if (empty($questions)): ?>
                <div style="margin-bottom: 24px;">
                    <label class="hud-stat-label" style="display: block; margin-bottom: 12px; color: #fff;">RELATÓRIO DE EXECUÇÃO</label>
                    <textarea name="response_text" class="hud-input" style="min-height: 120px; resize: none;"
                        placeholder="Descreva detalhadamente como o requisito foi cumprido..."><?= htmlspecialchars($existingText) ?></textarea>
                </div>

                <div style="margin-bottom: 24px;">
                    <label class="hud-stat-label" style="display: block; margin-bottom: 12px; color: #fff;">EVIDÊNCIA EXTERNA (Opcional)</label>
                    <div style="position: relative;">
                        <i class="material-icons-round" style="position: absolute; left: 16px; top: 14px; color: var(--hud-text-dim); font-size: 1.2rem;">link</i>
                        <input type="url" name="response_url" class="hud-input" style="padding-left: 48px;" placeholder="https://youtube.com/watch?v=..."
                            value="<?= htmlspecialchars($existingUrl) ?>">
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <label class="hud-stat-label" style="display: block; margin-bottom: 12px; color: #fff;">UPLOAD DE ARQUIVO</label>
                    <div class="file-upload-zone" style="background: rgba(0,0,0,0.4); border: 1px dashed rgba(255,255,255,0.2); border-radius: 12px; padding: 24px; text-align: center; cursor: pointer; transition: all 0.2s;">
                        <input type="file" name="response_file" onchange="this.parentElement.querySelector('.file-status').innerText = this.files[0].name" style="display: none;" id="file-input-modal">
                        <label for="file-input-modal" style="cursor: pointer; display: block;">
                            <i class="material-icons-round" style="font-size: 2.5rem; color: var(--accent-cyan); opacity: 0.5; margin-bottom: 8px;">cloud_upload</i>
                            <div class="file-status" style="font-size: 0.9rem; color: var(--hud-text-dim);">Clique para anexar foto ou PDF de comprovação</div>
                        </label>
                    </div>
                    <?php if ($existingFile): ?>
                        <a href="<?= $existingFile ?>" target="_blank" class="hud-badge" style="display: inline-flex; align-items: center; gap: 8px; margin-top: 12px; text-decoration: none; color: var(--accent-cyan); border-color: rgba(0,217,255,0.3);">
                            <i class="material-icons-round" style="font-size: 1rem;">visibility</i> VISUALIZAR ARQUIVO ATUAL
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Questions -->
                <?php foreach ($questions as $q): ?>
                    <div style="margin-bottom: 30px; position: relative; padding-left: 16px; border-left: 1px solid rgba(255,255,255,0.1);">
                        <label class="hud-stat-label" style="display: block; margin-bottom: 14px; color: #fff; font-size: 0.85rem; line-height: 1.4; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 8px;">
                            <?= htmlspecialchars($q['question_text']) ?>
                        </label>

                        <?php 
                        $currentAnswer = '';
                        $decoded = json_decode($existingText, true);
                        if (is_array($decoded) && isset($decoded[$q['id']])) {
                            $currentAnswer = $decoded[$q['id']];
                        } elseif (!is_array($decoded) && count($questions) === 1) {
                            $currentAnswer = $existingText;
                        }
                        ?>

                        <?php if ($q['type'] === 'text'): ?>
                            <textarea name="answers[<?= $q['id'] ?>]" class="hud-input" style="min-height: 100px; resize: none;"
                                placeholder="Insira sua resposta..."><?= htmlspecialchars($currentAnswer) ?></textarea>

                        <?php elseif ($q['type'] === 'single_choice' || $q['type'] === 'multiple_choice'): ?>
                            <?php
                            $options = json_decode($q['options'] ?? '[]', true) ?? [];
                            $selectedAnswers = is_array($currentAnswer) ? $currentAnswer : [$currentAnswer];
                            ?>
                            <div style="display: grid; gap: 10px;">
                                <?php foreach ($options as $optIdx => $opt): 
                                    $checked = in_array($optIdx, $selectedAnswers);
                                ?>
                                    <label class="radio-tech-card-wrapper" style="cursor: pointer;">
                                        <input type="<?= $q['type'] === 'single_choice' ? 'radio' : 'checkbox' ?>" 
                                               name="answers[<?= $q['id'] ?>]<?= $q['type'] === 'multiple_choice' ? '[]' : '' ?>" 
                                               value="<?= $optIdx ?>" <?= $checked ? 'checked' : '' ?> style="display: none;">
                                        <div class="radio-tech-card">
                                            <div class="radio-check"></div>
                                            <span><?= htmlspecialchars($opt) ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                        <?php elseif ($q['type'] === 'true_false'): ?>
                            <div style="display: flex; gap: 12px;">
                                <label class="radio-tech-card-wrapper" style="flex: 1; cursor: pointer;">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="0" <?= $currentAnswer === '0' ? 'checked' : '' ?> style="display: none;">
                                    <div class="radio-tech-card" style="justify-content: center; height: 80px; flex-direction: column;">
                                        <i class="material-icons-round" style="font-size: 1.8rem; color: #22c55e;">check_circle</i>
                                        <span style="margin-top: 4px;">Verdadeiro</span>
                                    </div>
                                </label>
                                <label class="radio-tech-card-wrapper" style="flex: 1; cursor: pointer;">
                                    <input type="radio" name="answers[<?= $q['id'] ?>]" value="1" <?= $currentAnswer === '1' ? 'checked' : '' ?> style="display: none;">
                                    <div class="radio-tech-card" style="justify-content: center; height: 80px; flex-direction: column;">
                                        <i class="material-icons-round" style="font-size: 1.8rem; color: #ef4444;">cancel</i>
                                        <span style="margin-top: 4px;">Falso</span>
                                    </div>
                                </label>
                            </div>

                        <?php elseif ($q['type'] === 'file_upload'): ?>
                             <div class="file-upload-zone" style="background: rgba(0,0,0,0.3); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer;">
                                <input type="file" name="response_file" onchange="this.parentElement.querySelector('.file-status').innerText = this.files[0].name" style="display: none;" id="file-q-<?= $q['id'] ?>">
                                <label for="file-q-<?= $q['id'] ?>" style="cursor: pointer; display: block;">
                                    <i class="material-icons-round" style="font-size: 1.5rem; color: var(--accent-cyan); opacity: 0.5;">attach_file</i>
                                    <div class="file-status" style="font-size: 0.8rem; color: var(--hud-text-dim);">Anexar Documento</div>
                                </label>
                            </div>

                        <?php elseif ($q['type'] === 'url'): ?>
                            <div style="position: relative;">
                                <i class="material-icons-round" style="position: absolute; left: 14px; top: 12px; color: var(--hud-text-dim); font-size: 1.1rem;">link</i>
                                <input type="url" name="answers[<?= $q['id'] ?>]" class="hud-input" placeholder="https://..." style="padding-left: 42px;"
                                    value="<?= htmlspecialchars($currentAnswer) ?>">

                        <?php elseif ($q['type'] === 'manual'): ?>
                            <textarea name="answers[<?= $q['id'] ?>]" class="hud-input" style="min-height: 80px; resize: none;"
                                placeholder="Relatório de atividade prática..."><?= htmlspecialchars($currentAnswer) ?></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <input type="hidden" name="is_multi_question" value="1">
            <?php endif; ?>

            <div class="modal-actions-footer">
                <button type="button" class="hud-btn secondary modal-btn-cancel" onclick="closeModal()">CANCELAR</button>
                <?php if ($status !== 'approved' && $status !== 'submitted'): ?>
                    <button type="button" class="hud-btn secondary modal-btn-save" onclick="submitStepForm(<?= $step['id'] ?>, this.form, 'draft')">
                        <i class="material-icons-round">save</i> <span class="btn-text">SALVAR</span>
                    </button>
                    <button type="submit" class="hud-btn primary modal-btn-submit">
                        <i class="material-icons-round">rocket_launch</i> <span class="btn-text">ENVIAR</span>
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<style>
    .step-modal-header {
        padding: 24px;
        background: rgba(0,0,0,0.3);
        border-bottom: 1px solid rgba(255,255,255,0.05);
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }
    @media (max-width: 480px) {
        .step-modal-header {
            padding: 16px;
        }
    }
    .modal-actions-footer {
        display: flex;
        gap: 16px;
        margin-top: 24px;
        background: rgba(0,0,0,0.2);
        margin: 24px -24px -24px; /* Default margin to stretch to edges */
        padding: 24px;
        border-top: 1px solid rgba(255,255,255,0.05);
    }
    .hud-btn {
        min-height: 48px; /* Touch target fix */
        display: flex; /* Flex alignment */
        align-items: center;
        justify-content: center;
        font-weight: 600;
        letter-spacing: 0.05em;
    }
    .modal-btn-cancel { flex: 1; }
    .modal-btn-save { flex: 1.2; }
    .modal-btn-submit { flex: 1.5; box-shadow: 0 4px 15px rgba(0, 217, 255, 0.2); }

    @media (max-width: 480px) {
        .modal-actions-footer {
            flex-wrap: wrap;
            gap: 12px;
            margin: 24px -16px -16px; /* Match mobile padding */
            padding: 16px;
        }
        .modal-btn-cancel {
            order: 3;
            flex: 100% !important;
            margin-top: 4px;
        }
        .modal-btn-save, .modal-btn-submit {
            flex: 1 1 40% !important;
            font-size: 0.8rem;
            padding: 0 12px;
        }
        .btn-text {
            font-size: 0.8rem;
        }
    }

    .radio-tech-card-wrapper input:checked + .radio-tech-card {
        background: rgba(0, 217, 255, 0.1);
        border-color: var(--accent-cyan);
        box-shadow: 0 0 20px rgba(0, 217, 255, 0.1);
    }
    .radio-tech-card-wrapper input:checked + .radio-tech-card .radio-check {
        background: var(--accent-cyan);
        box-shadow: 0 0 8px var(--accent-cyan);
        border-color: var(--accent-cyan);
    }
    .radio-tech-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px;
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 12px;
        transition: all 0.2s;
        color: #fff;
        font-size: 0.95rem;
        font-weight: 600;
    }
    .radio-check {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.2);
    }
    .radio-tech-card:hover {
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.15);
    }
    .file-upload-zone:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        border-color: var(--accent-cyan) !important;
    }
    #modal-content-area::-webkit-scrollbar { width: 6px; }
    #modal-content-area::-webkit-scrollbar-track { background: rgba(0,0,0,0.1); }
    #modal-content-area::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    .modal-content-scroll {
        padding: 24px;
        max-height: 65vh;
        overflow-y: auto;
    }
    @media (max-width: 480px) {
        .modal-content-scroll {
            padding: 16px;
            max-height: 70vh;
        }
    }
</style>
