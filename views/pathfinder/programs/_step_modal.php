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

<style>
    /* Scoped Reset */
    .step-modal-scope * {
        box-sizing: border-box;
    }

    .step-description {
        color: #94a3b8; /* Slate-400 */
        margin-bottom: 24px;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .question-item {
        margin-bottom: 20px;
        padding: 24px; /* Increased Padding */
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 16px;
    }

    .question-label {
        font-weight: 500; /* Reduced from 600 */
        margin-bottom: 12px;
        display: block;
        font-size: 0.95rem; /* Reduced from 1rem */
        line-height: 1.6;
        color: #f1f5f9; /* Slate-100 */
        letter-spacing: 0.01em;
    }

    .question-input {
        width: 100%;
        padding: 12px 16px;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        color: #e2e8f0;
        font-size: 0.95rem;
        transition: all 0.2s;
        font-family: inherit;
    }

    .question-input:focus {
        outline: none;
        border-color: var(--accent-cyan);
        background: rgba(0, 0, 0, 0.3);
        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.1);
    }

    textarea.question-input {
        min-height: 120px;
        resize: vertical;
        line-height: 1.5;
    }

    .file-preview {
        margin-top: 12px;
        padding: 12px;
        background: rgba(6, 182, 212, 0.1);
        border-radius: 8px;
        font-size: 0.9rem;
        border: 1px solid rgba(6, 182, 212, 0.2);
        display: flex;
        align-items: center;
        gap: 8px;
        color: #22d3ee;
    }

    .status-alert {
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .status-submitted {
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.2);
        color: #fbbf24;
    }

    .status-approved {
        background: rgba(16, 185, 129, 0.1);
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: #34d399;
    }

    .status-draft {
        background: rgba(148, 163, 184, 0.1);
        border: 1px solid rgba(148, 163, 184, 0.2);
        color: #94a3b8;
    }

    .feedback-box {
        background: rgba(0, 0, 0, 0.2);
        padding: 12px;
        border-radius: 8px;
        margin-top: 8px;
        font-size: 0.9rem;
    }

    .btn-submit {
        width: 100%;
        padding: 14px; /* Reduced from 16px */
        background: linear-gradient(135deg, var(--accent-cyan), #2563eb);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        margin-top: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
    }

    .btn-submit:active {
        transform: translateY(0);
    }

    .btn-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 12px;
    }

    @media (max-width: 480px) {
        .modal-actions {
            flex-direction: column;
        }
    }
</style>

<div class="step-modal-wrapper step-modal-scope" style="padding: 2px 24px 32px 24px;">

    <?php if ($status === 'draft'): ?>
        <div class="status-alert status-draft">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round">edit_note</span>
                <strong>Rascunho</strong>
            </div>
            <p style="margin: 0; font-size: 0.9rem; opacity: 0.8;">Sua resposta está salva, mas ainda não foi enviada para avaliação.</p>
        </div>
    <?php elseif ($status === 'submitted'): ?>
        <div class="status-alert status-submitted">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round">hourglass_top</span>
                <strong>Aguardando aprovação</strong>
            </div>
            <p style="margin: 0; font-size: 0.9rem; opacity: 0.8;">Sua resposta foi enviada e está sendo analisada.</p>
        </div>
    <?php elseif ($status === 'approved'): ?>
        <div class="status-alert status-approved">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round">check_circle</span>
                <strong>Aprovado!</strong>
            </div>
            <p style="margin: 0; font-size: 0.9rem; opacity: 0.8;">Este requisito foi concluído com sucesso.</p>
        </div>
    <?php elseif ($status === 'rejected'): ?>
        <div class="status-alert status-rejected">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span class="material-icons-round">error</span>
                <strong>Revisão necessária</strong>
            </div>
            <?php if ($feedback): ?>
                <div class="feedback-box">
                    <strong>Feedback:</strong> <?= htmlspecialchars($feedback) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($step['description'])): ?>
        <div class="step-description">
            <?= nl2br(htmlspecialchars($step['description'])) ?>
        </div>
    <?php endif; ?>

    <form onsubmit="event.preventDefault(); submitStepForm(<?= $step['id'] ?>, this);" enctype="multipart/form-data">

        <?php if (empty($questions)): ?>
            <!-- Outdoor or manual step - just text/file/url submission -->
            <div class="question-item">
                <label class="question-label">📝 Sua resposta</label>
                <textarea name="response_text" class="question-input"
                    placeholder="Descreva como você completou este requisito..."><?= htmlspecialchars($existingText) ?></textarea>
            </div>

            <div class="question-item">
                <label class="question-label">🔗 Link (opcional)</label>
                <input type="url" name="response_url" class="question-input" placeholder="https://..."
                    value="<?= htmlspecialchars($existingUrl) ?>">
            </div>

            <div class="question-item">
                <label class="question-label">📎 Arquivo de prova (opcional)</label>
                <input type="file" name="response_file" class="question-input" style="padding: 10px;">
                <?php if ($existingFile): ?>
                    <div class="file-preview">
                        📄 Arquivo atual: <a href="<?= $existingFile ?>" target="_blank" style="color: inherit; text-decoration: underline;">Ver arquivo</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Questions -->
            <?php foreach ($questions as $q): ?>
                <div class="question-item">
                    <label class="question-label"><?= htmlspecialchars($q['question_text']) ?></label>

                    <?php 
                    // Extract existing answer for this specific question if it's JSON
                    $currentAnswer = '';
                    $decoded = json_decode($existingText, true);
                    if (is_array($decoded) && isset($decoded[$q['id']])) {
                        $currentAnswer = $decoded[$q['id']];
                    } elseif (!is_array($decoded) && count($questions) === 1) {
                        // Fallback for legacy single-text answers
                        $currentAnswer = $existingText;
                    }
                    ?>

                    <?php if ($q['type'] === 'text'): ?>
                        <textarea name="answers[<?= $q['id'] ?>]" class="question-input"
                            placeholder="Sua resposta..."><?= htmlspecialchars($currentAnswer) ?></textarea>

                    <?php elseif ($q['type'] === 'single_choice'): ?>
                        <?php
                        $options = json_decode($q['options'] ?? '[]', true) ?? [];
                        foreach ($options as $optIdx => $opt):
                            ?>
                            <label
                                style="display: block; margin: 8px 0; cursor: pointer; padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid transparent; transition: border 0.2s;">
                                <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $optIdx ?>" <?= $currentAnswer == $optIdx ? 'checked' : '' ?> style="margin-right: 10px;">
                                <?= htmlspecialchars($opt) ?>
                            </label>
                        <?php endforeach; ?>

                    <?php elseif ($q['type'] === 'multiple_choice'): ?>
                        <?php
                        $options = json_decode($q['options'] ?? '[]', true) ?? [];
                        $selectedAnswers = is_array($currentAnswer) ? $currentAnswer : [];
                        foreach ($options as $optIdx => $opt):
                            $isChecked = in_array($optIdx, $selectedAnswers);
                            ?>
                            <label
                                style="display: block; margin: 8px 0; cursor: pointer; padding: 12px; background: rgba(0,0,0,0.2); border-radius: 8px; border: 1px solid transparent; transition: border 0.2s;">
                                <input type="checkbox" name="answers[<?= $q['id'] ?>][]" value="<?= $optIdx ?>" <?= $isChecked ? 'checked' : '' ?>
                                    style="margin-right: 10px;">
                                <?= htmlspecialchars($opt) ?>
                            </label>
                        <?php endforeach; ?>

                    <?php elseif ($q['type'] === 'true_false'): ?>
                        <div style="display: flex; gap: 16px;">
                            <label
                                style="flex: 1; cursor: pointer; padding: 16px; background: rgba(0,255,136,0.1); border-radius: 12px; text-align: center; border: 2px solid <?= $currentAnswer === '0' ? 'var(--accent-green)' : 'transparent' ?>; transition: all 0.2s;">
                                <input type="radio" name="answers[<?= $q['id'] ?>]" value="0" <?= $currentAnswer === '0' ? 'checked' : '' ?>
                                    style="display: none;">
                                <span style="font-size: 1.5rem; display: block; margin-bottom: 4px;">✅</span>
                                Verdadeiro
                            </label>
                            <label
                                style="flex: 1; cursor: pointer; padding: 16px; background: rgba(255,100,100,0.1); border-radius: 12px; text-align: center; border: 2px solid <?= $currentAnswer === '1' ? 'var(--accent-danger)' : 'transparent' ?>; transition: all 0.2s;">
                                <input type="radio" name="answers[<?= $q['id'] ?>]" value="1" <?= $currentAnswer === '1' ? 'checked' : '' ?>
                                    style="display: none;">
                                <span style="font-size: 1.5rem; display: block; margin-bottom: 4px;">❌</span>
                                Falso
                            </label>
                        </div>

                    <?php elseif ($q['type'] === 'file_upload'): ?>
                        <input type="file" name="response_file" class="question-input" style="padding: 10px;">
                        <?php if ($status === 'submitted' || $status === 'approved'): ?>
                             <!-- Link to file if stored separately, logic needed in controller if we want per-question files -->
                             <p class="text-xs text-muted">Upload de arquivo (Gerenciado individualmente)</p>
                        <?php endif; ?>

                    <?php elseif ($q['type'] === 'url'): ?>
                        <input type="url" name="answers[<?= $q['id'] ?>]" class="question-input" placeholder="https://..."
                            value="<?= htmlspecialchars($currentAnswer) ?>">

                    <?php elseif ($q['type'] === 'manual'): ?>
                        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 8px;">
                            Este item requer aprovação manual. Descreva o que você fez:
                        </p>
                        <textarea name="answers[<?= $q['id'] ?>]" class="question-input"
                            placeholder="Descreva como você completou este requisito..."><?= htmlspecialchars($currentAnswer) ?></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <input type="hidden" name="is_multi_question" value="1">
        <?php endif; ?>

        <?php if ($status !== 'approved'): ?>
            <div class="modal-actions">
                <button type="button" class="btn-submit" onclick="submitStepForm(<?= $step['id'] ?>, this.form, 'draft')" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); flex: 1;">
                    <span class="material-icons-round" style="font-size: 1.2rem;">save</span>
                    Salvar Rascunho
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>