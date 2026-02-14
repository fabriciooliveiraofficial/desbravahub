<?php
/**
 * Proofs - Relatórios de Missão
 * DESIGN: Deep Glass HUD v3.0 (Content Only)
 */

// Stats are calculated in Controller (program-based) and passed as $stats
?>

<div class="hud-wrapper">
    <header class="hud-header">
        <div>
            <h1 class="hud-title">Registro de Provas</h1>
            <div class="hud-subtitle">Status dos Relatórios de Missão</div>
        </div>
    </header>

    <!-- Stats -->
    <div class="hud-stats">
        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-warning)"><?= $stats['pending'] ?></div>
                <div class="hud-stat-label">Em Análise</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-warning)">hourglass_empty</i>
        </div>
        
        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-green)"><?= $stats['approved'] ?></div>
                <div class="hud-stat-label">Aprovados</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-green)">check_circle</i>
        </div>

        <div class="hud-stat-card">
            <div>
                <div class="hud-stat-value" style="color: var(--accent-danger)"><?= $stats['rejected'] ?></div>
                <div class="hud-stat-label">Recusados</div>
            </div>
            <i class="material-icons-round hud-stat-icon" style="color: var(--accent-danger)">cancel</i>
        </div>
    </div>

    <?php if (empty($proofs)): ?>
        <div class="empty-state-hud">
            <span class="material-icons-round empty-icon-hud">folder_off</span>
            <h3 class="hud-section-title">Sem registros</h3>
            <p class="hud-subtitle">Nenhum relatório de missão foi enviado ainda.</p>
        </div>
    <?php else: ?>
        <section class="hud-section">
            <div class="hud-section-header">
                <h2 class="hud-section-title">Histórico de Envios</h2>
            </div>
            
            <div class="hud-grid" style="grid-template-columns: 1fr;">
                <?php foreach ($proofs as $index => $proof):
                    $date = new DateTime($proof['submitted_at']);
                    $statusMap = [
                        'pending' => ['label' => 'AGUARDANDO', 'color' => 'var(--accent-warning)', 'class' => 'type-pending'],
                        'approved' => ['label' => 'CONFIRMADO', 'color' => 'var(--accent-green)', 'class' => 'type-completed'],
                        'rejected' => ['label' => 'RECUSADO', 'color' => 'var(--accent-danger)', 'class' => 'type-pending'], // Red line fallback
                    ];
                    $st = $statusMap[$proof['status']] ?? $statusMap['pending'];
                ?>
                    <div class="tech-plate <?= $st['class'] ?>" style="animation-delay: <?= $index * 0.1 ?>s">
                        <div class="status-line" style="background: <?= $st['color'] ?>"></div>
                        
                        <div class="plate-header">
                            <div class="plate-content">
                                <div class="plate-category">
                                    <span class="material-icons-round" style="font-size: 0.9rem; vertical-align: text-bottom; margin-right: 4px;">calendar_today</span>
                                    <?= $date->format('d/m H:i') ?>
                                </div>
                                <h3 class="plate-title"><?= htmlspecialchars($proof['activity_title']) ?></h3>
                            </div>
                            <span class="hud-badge" style="color: <?= $st['color'] ?>; border-color: <?= $st['color'] ?>">
                                <?= $st['label'] ?>
                            </span>
                        </div>

                        <div class="plate-data">
                            <div class="data-point">
                                <span class="data-label">Tipo de Prova</span>
                                <span class="data-value" style="font-size: 0.75rem; text-transform: uppercase;">
                                    <?= $proof['type'] === 'url' ? 'LINK EXTERNO' : ($proof['type'] === 'text' ? 'RESPOSTA DE TEXTO' : 'ARQUIVO DIGITAL') ?>
                                </span>
                            </div>
                            
                            <div class="data-point">
                                <span class="data-label">Conteúdo</span>
                                <?php if ($proof['type'] === 'url'): ?>
                                    <a href="<?= htmlspecialchars($proof['content']) ?>" target="_blank" style="color: var(--accent-cyan); text-decoration: none; font-weight: 700; font-size: 0.75rem;">
                                        ABRIR LINK <i class="fas fa-external-link-alt" style="font-size: 0.6rem;"></i>
                                    </a>
                                <?php elseif ($proof['type'] === 'text'): ?>
                                    <div style="background: rgba(255,255,255,0.05); padding: 8px 12px; border-radius: 8px; font-size: 0.85rem; color: #e2e8f0; font-style: italic;">
                                        "<?= htmlspecialchars(mb_strimwidth($proof['content'], 0, 80, '...')) ?>"
                                    </div>
                                <?php else: ?>
                                    <a href="<?= base_url('storage/' . $proof['content']) ?>" target="_blank" style="color: var(--accent-cyan); text-decoration: none; font-weight: 700; font-size: 0.75rem;">
                                        BAIXAR ARQUIVO <i class="fas fa-download" style="font-size: 0.6rem;"></i>
                                    </a>
                                <?php endif; ?>
                            </div>

                            <?php if ($proof['feedback']): ?>
                                <div class="data-point" style="grid-column: span 2; border-top: 1px dashed var(--hud-glass-border); margin-top: 8px; padding-top: 8px;">
                                    <span class="data-label" style="color: var(--accent-warning);">MESSAGE FROM HQ:</span>
                                    <span class="data-value" style="font-style: italic; opacity: 0.8; font-weight: 400;">
                                        <?php 
                                        if (str_starts_with($proof['feedback'], '[ITEM_EVAL]')) {
                                            $jsonStr = substr($proof['feedback'], 11); // Remove [ITEM_EVAL]
                                            $evalData = json_decode($jsonStr, true);
                                            
                                            if ($evalData) {
                                                // Show main evidence feedback if rejected
                                                if (isset($evalData['items']['main_evidence']['status']) && $evalData['items']['main_evidence']['status'] === 'rejected') {
                                                    echo '<span style="color: #ef4444;">' . htmlspecialchars($evalData['items']['main_evidence']['feedback']) . '</span>';
                                                } 
                                                // Show overall feedback is present
                                                elseif (!empty($evalData['overall'])) {
                                                    echo htmlspecialchars($evalData['overall']);
                                                }
                                                // Show generic message if multiple items rejected
                                                else {
                                                    $rejectedCount = 0;
                                                    foreach ($evalData['items'] as $item) {
                                                        if (isset($item['status']) && $item['status'] === 'rejected') {
                                                            $rejectedCount++;
                                                        }
                                                    }
                                                    if ($rejectedCount > 0) {
                                                        echo "Existem $rejectedCount itens que precisam de revisão. Verifique os detalhes.";
                                                    } else {
                                                        echo "Avaliação completa.";
                                                    }
                                                }
                                            } else {
                                                // Fallback if JSON decode fails
                                                echo htmlspecialchars(substr($proof['feedback'], 0, 50) . '...'); 
                                            }
                                        } else {
                                            echo htmlspecialchars($proof['feedback']);
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</div>