<?php
/**
 * Unified Proof Review (Activity or Program)
 * Sophisticated LMS Design v3.0
 */

$isProgram = isset($progress);
$title = $isProgram ? $progress['program_name'] : $proof['item_title'];
$userName = $isProgram ? $progress['user_name'] : $proof['user_name'];
$userAvatar = $isProgram ? $progress['avatar_url'] : null;
$unitName = $isProgram ? $progress['unit_name'] : 'Desbravador';

// Select current step if and when it's a program
$currentStep = $isProgram ? null : $proof;
if ($isProgram) {
    $requestedId = (int)($_GET['step_id'] ?? 0);
    if (!$requestedId && !empty($steps)) $requestedId = $steps[0]['id'];
    foreach ($steps as $s) {
        if ($s['id'] == $requestedId) { $currentStep = $s; break; }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisar: <?= htmlspecialchars((string)($title ?? '')) ?></title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    
    <!-- CSS Dependencies -->
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/evaluations.css') ?>">

    <style>
        :root {
            --bg-main: #F4F7FA;
            --accent-primary: #06b6d4;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --bg-card: #ffffff;
            --shadow-premium: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
        }

        body {
            background: var(--bg-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-dark);
            margin: 0;
            overflow: hidden;
            height: 100vh;
        }

        .review-layout {
            display: grid;
            grid-template-columns: 320px 1fr;
            height: 100vh;
        }

        /* Sidebar - LMS Progression Style */
        .review-sidebar {
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 50;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
        }

        .student-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--bg-main);
            padding: 8px 12px;
            border-radius: 12px;
            margin-top: 12px;
        }

        .student-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--accent-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
            overflow: hidden;
        }

        .student-info h4 {
            font-size: 0.85rem;
            margin: 0;
            font-weight: 700;
        }

        .student-info p {
            font-size: 0.7rem;
            margin: 0;
            color: var(--text-muted);
        }

        .step-list {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }

        .step-item {
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid transparent;
            text-decoration: none;
            color: inherit;
        }

        .step-item:hover {
            background: #f8fafc;
        }

        .step-item.active {
            background: rgba(6, 182, 212, 0.05);
            border-color: rgba(6, 182, 212, 0.2);
        }

        .step-number {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background: #f1f5f9;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .step-item.active .step-number {
            background: var(--accent-primary);
            color: white;
        }

        .step-info { flex: 1; }
        .step-info h5 { margin: 0; font-size: 0.85rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; }
        
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #cbd5e1;
        }

        .status-dot.submitted { background: #06b6d4; box-shadow: 0 0 10px rgba(6, 182, 212, 0.4); }
        .status-dot.approved { background: #10b981; }
        .status-dot.rejected { background: #ef4444; }

        /* Workspace Area */
        .workspace {
            flex: 1;
            overflow-y: auto;
            padding: 60px 40px 100px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            scroll-behavior: smooth;
        }

        .review-card {
            width: 100%;
            max-width: 860px;
            background: var(--bg-card);
            border-radius: 28px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-premium);
            padding: 48px;
            position: relative;
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            margin-bottom: 40px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 32px;
        }

        .badge-type {
            display: inline-flex;
            padding: 6px 14px;
            background: rgba(6, 182, 212, 0.08);
            color: var(--accent-primary);
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 16px;
        }

        .req-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 0 0 12px 0;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .req-desc {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
        }

        /* Intelligent Q&A Renderer */
        .qa-container {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .qa-block {
            background: #f8fafc;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid var(--border-color);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .qa-block:hover {
            border-color: var(--accent-primary);
            background: #ffffff;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.02);
            transform: scale(1.005);
        }

        .question-text {
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--accent-primary);
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0.8;
        }

        .answer-text {
            font-size: 1.15rem;
            font-weight: 600;
            color: var(--text-dark);
            white-space: pre-wrap;
            line-height: 1.4;
        }

        /* Item Evaluation UI */
        .item-eval-toolbar {
            display: flex; gap: 8px; margin-top: 16px; padding-top: 16px;
            border-top: 1px solid var(--border-color); align-items: center;
        }
        .eval-btn {
            height: 32px; padding: 0 12px; border-radius: 8px; font-size: 0.7rem;
            font-weight: 700; display: flex; align-items: center; gap: 6px;
            cursor: pointer; border: 1px solid var(--border-color); background: var(--bg-card);
            color: var(--text-muted); transition: all 0.2s;
        }
        .eval-btn:hover { background: var(--bg-main); }
        .eval-btn.active-approve { 
            background: rgba(16, 185, 129, 0.1); border-color: #10b981; color: #059669;
        }
        .eval-btn.active-reject { 
            background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #b91c1c;
        }
        .feedback-input {
            width: 100%; margin-top: 12px; padding: 12px; border-radius: 12px;
            background: var(--bg-main); border: 1px solid var(--border-color);
            font-size: 0.85rem; color: var(--text-dark); display: none;
        }
        .feedback-input.active { display: block; }

        /* Evidence & Media Player */
        .evidence-section {
            margin-top: 40px;
            padding-top: 40px;
            border-top: 2px solid #f1f5f9;
        }

        .section-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 20px;
            display: block;
        }

        /* Small Inline Video */
        .inline-video-wrapper {
            width: 100%;
            max-width: 440px;
            aspect-ratio: 16 / 9;
            border-radius: 18px;
            overflow: hidden;
            margin-top: 12px;
            border: 3px solid #fff;
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
            background: #000;
        }
        .inline-video-wrapper iframe { width: 100%; height: 100%; border: none; }

        /* Enhanced Main Video */
        .main-video-frame {
            width: 100%;
            aspect-ratio: 16 / 9;
            border-radius: 28px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 30px 60px -12px rgba(0,0,0,0.2), 0 18px 36px -18px rgba(0,0,0,0.2);
            border: 6px solid white;
            position: relative;
        }
        .main-video-frame iframe { width: 100%; height: 100%; border: none; }
        
        .video-overlay-info {
            position: absolute; top: 20px; right: 20px;
            background: rgba(0,0,0,0.6); backdrop-filter: blur(10px);
            padding: 8px 16px; border-radius: 12px; color: white;
            display: flex; align-items: center; gap: 10px;
            font-size: 0.75rem; font-weight: 700; pointer-events: none;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .image-preview-container {
            width: 100%; border-radius: 28px; overflow: hidden;
            border: 6px solid white; box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            position: relative; cursor: zoom-in;
        }
        .image-preview-container img { width: 100%; height: auto; display: block; }
        .zoom-hint {
            position: absolute; bottom: 20px; right: 20px;
            background: rgba(255,255,255,0.9); padding: 8px 16px;
            border-radius: 12px; font-size: 0.7rem; font-weight: 800;
            color: var(--text-dark); text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .media-frame {
            width: 100%;
            border-radius: 24px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 16 / 9;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 4px solid white;
        }

        .media-frame iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .yt-embed-compact, .social-embed-compact {
            margin-top: 12px; border-radius: 12px; overflow: hidden;
            border: 1px solid var(--border-color); background: #000;
        }
        .yt-embed-compact iframe {
            width: 100%; aspect-ratio: 16/9; display: block; border: 0;
        }
        .social-embed-compact blockquote {
            margin: 0 !important; width: 100% !important;
        }

        /* Social Embeds - Custom Sizing */
        .social-embed {
            max-width: 480px;
            margin: 0 auto;
            border-radius: 24px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: var(--shadow-premium);
        }

        /* Embedded Card Actions */
        .card-actions {
            margin-top: 48px;
            padding-top: 32px;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 16px;
        }

        .action-btn {
            height: 52px;
            padding: 0 32px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            border: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }

        .action-btn-approve {
            background: var(--accent-primary);
            color: white;
            box-shadow: 0 8px 16px rgba(6, 182, 212, 0.15);
        }
        .action-btn-approve:hover {
            background: #0891b2;
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(6, 182, 212, 0.25);
        }

        .action-btn-approve.btn-warning {
            background: #f59e0b;
            box-shadow: 0 8px 16px rgba(245, 158, 11, 0.15);
        }
        .action-btn-approve.btn-warning:hover {
            background: #d97706;
            box-shadow: 0 12px 20px rgba(245, 158, 11, 0.25);
        }

        .action-btn-reject {
            background: #f8fafc;
            color: var(--text-dark);
            border: 1px solid var(--border-color);
        }
        .action-btn-reject:hover {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
            transform: translateY(-2px);
        }

        .action-btn-secondary {
            background: #f1f5f9;
            color: var(--text-muted);
            padding: 0 24px;
            font-size: 0.85rem;
        }
        .action-btn-secondary:hover {
            background: #e2e8f0;
            color: var(--text-dark);
            transform: translateY(-2px);
        }

        /* Update Sidebar Action Footer */
        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid var(--border-color);
            background: #ffffff;
        }

        /* Icon Styles */
        iconify-icon { font-size: 1.2rem; }
    </style>
</head>
<body>

<div class="review-layout">
    <!-- Sidebar -->
    <aside class="review-sidebar">
        <div class="sidebar-header">
            <h3 style="font-family:'Outfit'; font-weight:800; font-size:1.15rem; margin:0; letter-spacing:-0.03em; color:var(--text-dark);">Central de Avaliação</h3>
            
            <div class="student-chip">
                <div class="student-avatar">
                    <?php if ($userAvatar): ?>
                        <img src="<?= htmlspecialchars((string)$userAvatar) ?>" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                        <?= substr((string)($userName ?? ''), 0, 1) ?>
                    <?php endif; ?>
                </div>
                <div class="student-info">
                    <h4><?= htmlspecialchars((string)($userName ?? '')) ?></h4>
                    <p><?= htmlspecialchars((string)($unitName ?? '')) ?></p>
                </div>
            </div>
        </div>

        <div class="step-list">
            <?php if ($isProgram && isset($steps)): ?>
                <?php foreach ($steps as $s): 
                    $isActive = ($s['id'] == ($params['step_id'] ?? $steps[0]['id']));
                    $statusClass = $s['response_status'] ?? 'pending';
                ?>
                    <a class="step-item <?= $isActive ? 'active' : '' ?>" href="<?= base_url($tenant['slug'] . "/admin/aprovacoes/{$progress['id']}/review?step_id={$s['id']}") ?>" hx-boost="true">
                        <div class="step-number"><?= $s['sort_order'] ?></div>
                        <div class="step-info">
                            <h5><?= htmlspecialchars((string)($s['title'] ?? '')) ?></h5>
                        </div>
                        <div class="status-dot <?= $statusClass ?>"></div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="step-item active">
                    <div class="step-number">1</div>
                    <div class="step-info">
                        <h5><?= htmlspecialchars((string)($title ?? '')) ?></h5>
                    </div>
                    <div class="status-dot submitted"></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar Actions Footer -->
        <div class="sidebar-footer">
            <div class="dock-info" style="background: var(--bg-main); padding: 12px; border-radius: 12px;">
                <span style="font-size: 0.6rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; display: block;">Status Atual</span>
                <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-top: 4px;">
                    <?= ucfirst(htmlspecialchars((string)($currentStep['response_status'] ?? 'Pendente'))) ?>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Workspace -->
    <main class="workspace" id="main-content">
        <?php if ($currentStep): ?>
        <div class="review-card">
            <div class="card-header">
                <span class="badge-type"><?= $isProgram ? 'Requisito do Programa' : 'Prova de Especialidade' ?></span>
                <h1 class="req-title"><?= htmlspecialchars((string)($currentStep['title'] ?? $title ?? '')) ?></h1>
                <?php if (isset($currentStep['description'])): ?>
                    <p class="req-desc"><?= htmlspecialchars((string)$currentStep['description']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Intelligent Content Renderer -->
            <div class="qa-container">
                <?php if (!empty($currentStep['structured_content'])): ?>
                    <?php foreach ($currentStep['structured_content'] as $idx => $qa): ?>
                        <div class="qa-block">
                            <div class="question-text">
                                <iconify-icon icon="solar:question-square-bold-duotone"></iconify-icon>
                                <?= htmlspecialchars((string)($qa['question'] ?? '')) ?>
                            </div>
                            <div class="answer-text">
                                <?php 
                                $ans = $qa['answer'];
                                $youtubeMatch = preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', (string)$ans, $yMatch);
                                $instagramMatch = preg_match('/instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/', (string)$ans, $iMatch);
                                $tiktokMatch = preg_match('/tiktok\.com\/.*\/video\/([0-9]+)/', (string)$ans, $tMatch);
                                ?>
                                <?php if ($youtubeMatch): ?>
                                    <div class="yt-embed-compact">
                                        <iframe src="https://www.youtube.com/embed/<?= $yMatch[1] ?>" allowfullscreen></iframe>
                                    </div>
                                <?php elseif ($instagramMatch): ?>
                                    <div class="social-embed-compact">
                                        <blockquote class="instagram-media" data-instgrm-permalink="<?= htmlspecialchars((string)$ans) ?>" data-instgrm-version="14" style="width:100%; border:0; border-radius:12px; margin:0; padding:0;"></blockquote>
                                        <script async src="//www.instagram.com/embed.js"></script>
                                    </div>
                                <?php elseif ($tiktokMatch): ?>
                                    <div class="social-embed-compact">
                                        <blockquote class="tiktok-embed" cite="<?= htmlspecialchars((string)$ans) ?>" data-video-id="<?= $tMatch[1] ?>" style="width:100%; margin:0; padding:0;"> <section> </section> </blockquote> 
                                        <script async src="https://www.tiktok.com/embed.js"></script>
                                    </div>
                                <?php else: ?>
                                    <?= htmlspecialchars((string)($ans ?? '')) ?>
                                <?php endif; ?>
                            </div>

                            <?php if (($currentStep['response_status'] ?? '') === 'submitted'): ?>
                                <div class="item-eval-toolbar" data-item-id="<?= $idx ?>">
                                    <button class="eval-btn btn-eval-approve" onclick="setItemStatus(<?= $idx ?>, 'approved')">
                                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                        Aprovar Item
                                    </button>
                                    <button class="eval-btn btn-eval-reject" onclick="setItemStatus(<?= $idx ?>, 'rejected')">
                                        <iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon>
                                        Solicitar Ajuste
                                    </button>
                                    <span class="eval-status-label" style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; margin-left: auto; opacity: 0.5;">Aguardando Avaliação</span>
                                </div>
                                <textarea class="feedback-input" placeholder="O que o desbravador precisa corrigir neste item específico?" oninput="updateItemFeedback(<?= $idx ?>, this.value)"></textarea>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php if (!empty($currentStep['response_text'])): ?>
                        <div class="qa-block" style="border-left: 4px solid var(--accent-primary);">
                            <div class="question-text">Resposta Consolidada</div>
                            <div class="answer-text">
                                <?php 
                                $ans = $currentStep['response_text'];
                                $youtubeMatch = preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', (string)$ans, $yMatch);
                                $instagramMatch = preg_match('/instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/', (string)$ans, $iMatch);
                                $tiktokMatch = preg_match('/tiktok\.com\/.*\/video\/([0-9]+)/', (string)$ans, $tMatch);
                                ?>
                                <?php if ($youtubeMatch): ?>
                                    <div class="yt-embed-compact">
                                        <iframe src="https://www.youtube.com/embed/<?= $yMatch[1] ?>" allowfullscreen></iframe>
                                    </div>
                                <?php elseif ($instagramMatch): ?>
                                    <div class="social-embed-compact">
                                        <blockquote class="instagram-media" data-instgrm-permalink="<?= htmlspecialchars((string)$ans) ?>" data-instgrm-version="14" style="width:100%; border:0; border-radius:12px; margin:0; padding:0;"></blockquote>
                                        <script async src="//www.instagram.com/embed.js"></script>
                                    </div>
                                <?php elseif ($tiktokMatch): ?>
                                    <div class="social-embed-compact">
                                        <blockquote class="tiktok-embed" cite="<?= htmlspecialchars((string)$ans) ?>" data-video-id="<?= $tMatch[1] ?>" style="width:100%; margin:0; padding:0;"> <section> </section> </blockquote> 
                                        <script async src="https://www.tiktok.com/embed.js"></script>
                                    </div>
                                <?php else: ?>
                                    <?= htmlspecialchars((string)($ans ?? '')) ?>
                                <?php endif; ?>
                            </div>

                            <?php if (($currentStep['response_status'] ?? '') === 'submitted'): ?>
                                <div class="item-eval-toolbar" data-item-id="consolidated">
                                    <button class="eval-btn btn-eval-approve" onclick="setItemStatus('consolidated', 'approved')">
                                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                        Aprovar Resposta
                                    </button>
                                    <button class="eval-btn btn-eval-reject" onclick="setItemStatus('consolidated', 'rejected')">
                                        <iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon>
                                        Solicitar Ajuste
                                    </button>
                                    <span class="eval-status-label" style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; margin-left: auto; opacity: 0.5;">Aguardando Avaliação</span>
                                </div>
                                <textarea class="feedback-input" placeholder="O que o desbravador precisa corrigir na resposta textual?" oninput="updateItemFeedback('consolidated', this.value)"></textarea>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding: 40px; color: var(--text-muted); font-style: italic;">
                            Nenhuma resposta textual enviada. Confira os anexos abaixo.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Evidence Section (Media) -->
            <?php 
                $responseFile = $currentStep['response_file'] ?? ($proof['content'] ?? null);
                $responseUrl = $currentStep['response_url'] ?? null;
                $isUrl = ($currentStep['type'] ?? 'upload') === 'url' || filter_var($responseUrl, FILTER_VALIDATE_URL);
            ?>

            <?php if ($responseFile || $responseUrl): ?>
                <div class="evidence-section qa-block" style="border-top: 2px solid #f1f5f9; padding-top: 30px;">
                    <span class="section-label">Evidência Principal</span>
                    
                    <div class="evidence-content" style="margin-bottom: 20px;">
                        <?php if ($isUrl): ?>
                            <?php 
                                $url = $responseUrl ?: $responseFile;
                                $youtubeId = null;
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $match)) {
                                    $youtubeId = $match[1];
                                }
                                
                                $isTikTok = strpos($url, 'tiktok.com') !== false;
                                $isInstagram = strpos($url, 'instagram.com') !== false;
                            ?>

                            <?php if ($youtubeId): ?>
                                <div class="main-video-frame">
                                    <iframe src="https://www.youtube.com/embed/<?= $youtubeId ?>" allowfullscreen></iframe>
                                    <div class="video-overlay-info">
                                        <iconify-icon icon="logos:youtube-icon"></iconify-icon>
                                        <span>Conteúdo Principal</span>
                                    </div>
                                </div>
                            <?php elseif ($isTikTok): ?>
                                <div class="social-embed">
                                    <?php 
                                        $parts = explode('/', rtrim($url, '/'));
                                        $videoId = end($parts);
                                    ?>
                                    <blockquote class="tiktok-embed" cite="<?= htmlspecialchars((string)($url ?? '')) ?>" data-video-id="<?= htmlspecialchars((string)($videoId ?? '')) ?>" style="max-width: 605px;min-width: 325px;"> <section> </section> </blockquote> 
                                    <script async src="https://www.tiktok.com/embed.js"></script>
                                </div>
                            <?php elseif ($isInstagram): ?>
                                <div class="social-embed">
                                    <blockquote class="instagram-media" data-instgrm-captioned data-instgrm-permalink="<?= htmlspecialchars((string)($url ?? '')) ?>" data-instgrm-version="14" style=" background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%;"></blockquote>
                                    <script async src="//www.instagram.com/embed.js"></script>
                                </div>
                            <?php else: ?>
                                <div class="attachment-link-card" style="display:flex; align-items:center; gap:20px; background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
                                    <iconify-icon icon="solar:link-circle-bold-duotone" style="font-size:32px; color:var(--accent-primary)"></iconify-icon>
                                    <div style="flex:1">
                                        <div style="font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Link Externo</div>
                                        <a href="<?= htmlspecialchars((string)($url ?? '')) ?>" target="_blank" style="color:var(--accent-primary); font-weight:700; text-decoration:none; display:block; margin-top:4px; max-width:100%; overflow:hidden; text-overflow:ellipsis;"><?= htmlspecialchars((string)($url ?? '')) ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <!-- Image/File Display -->
                            <?php 
                                $ext = strtolower(pathinfo((string)$responseFile, PATHINFO_EXTENSION));
                                $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'svg', 'webp']);
                            ?>
                            <?php if ($isImg): ?>
                                <div class="image-preview-container">
                                    <img src="<?= base_url($responseFile) ?>">
                                    <div class="zoom-hint">Evidência Anexada</div>
                                </div>
                            <?php else: ?>
                                <div class="attachment-link-card" style="display:flex; align-items:center; gap:20px; background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color);">
                                    <iconify-icon icon="solar:document-bold-duotone" style="font-size:32px; color:var(--accent-primary)"></iconify-icon>
                                    <div style="flex:1">
                                        <div style="font-size:0.7rem; font-weight:800; color:var(--text-muted); text-transform:uppercase;">Arquivo de Comprovação</div>
                                        <a href="<?= base_url($responseFile) ?>" target="_blank" style="color:var(--accent-primary); font-weight:700; text-decoration:none; display:block; margin-top:4px;"><?= htmlspecialchars(basename($responseFile)) ?></a>
                                    </div>
                                    <a href="<?= base_url($responseFile) ?>" download class="btn-dock btn-secondary" style="height:40px; padding:0 15px;">
                                        <iconify-icon icon="solar:download-bold-duotone"></iconify-icon>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if (($currentStep['response_status'] ?? '') === 'submitted'): ?>
                        <div class="item-eval-toolbar" data-item-id="main_evidence">
                            <button class="eval-btn btn-eval-approve" onclick="setItemStatus('main_evidence', 'approved')">
                                <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                Aprovar Evidência
                            </button>
                            <button class="eval-btn btn-eval-reject" onclick="setItemStatus('main_evidence', 'rejected')">
                                <iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon>
                                Solicitar Ajuste
                            </button>
                            <span class="eval-status-label" style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; margin-left: auto; opacity: 0.5;">Aguardando Avaliação</span>
                        </div>
                        <textarea class="feedback-input" placeholder="O que o desbravador precisa corrigir nesta evidência ou arquivo?" oninput="updateItemFeedback('main_evidence', this.value)"></textarea>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Consolidated Card Actions -->
            <div class="card-actions">
                <button class="action-btn action-btn-secondary" onclick="location.href = '<?= base_url($tenant['slug'] . '/admin/aprovacoes') ?>'">
                    <iconify-icon icon="solar:exit-bold-duotone"></iconify-icon>
                    Sair da Revisão
                </button>

                <div style="flex: 1"></div>

                <?php if (($currentStep['response_status'] ?? '') === 'submitted' || ($currentStep['response_status'] ?? '') === 'pending'): ?>
                    <button class="action-btn action-btn-reject" onclick="processBatchReview('rejected')" id="batchBtnReject">
                        <iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon>
                        Rejeitar Tudo
                    </button>

                    <button class="action-btn action-btn-approve" onclick="processBatchReview('approved')" id="batchBtnApprove">
                        <iconify-icon icon="solar:verified-check-bold-duotone"></iconify-icon>
                        Aprovar Tudo
                    </button>
                <?php else: ?>
                    <div style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 12px 24px; border-radius: 12px; font-weight: 700; font-size: 0.85rem; display: flex; align-items: center; gap: 10px;">
                        <iconify-icon icon="solar:verified-check-bold-duotone" style="font-size: 1.4rem;"></iconify-icon>
                        ESTE PASSO FOI AVALIADO COMO: <?= strtoupper(htmlspecialchars((string)($currentStep['response_status'] ?? ''))) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let reviewState = {
        item_evaluations: {},
        overall_feedback: ''
    };

    function setItemStatus(idx, status) {
        const row = document.querySelector(`.item-eval-toolbar[data-item-id="${idx}"]`);
        const feedbackInput = row.nextElementSibling;
        
        row.querySelectorAll('.eval-btn').forEach(b => {
            b.classList.remove('active-approve', 'active-reject');
        });

        if (status === 'approved') {
            row.querySelector('.btn-eval-approve').classList.add('active-approve');
            feedbackInput.classList.remove('active');
            reviewState.item_evaluations[idx] = { status: 'approved', feedback: '' };
        } else {
            row.querySelector('.btn-eval-reject').classList.add('active-reject');
            feedbackInput.classList.add('active');
            reviewState.item_evaluations[idx] = { status: 'rejected', feedback: feedbackInput.value };
        }
        
        row.querySelector('.eval-status-label').innerText = status === 'approved' ? 'Aprovado' : 'Ajuste Necessário';
        row.querySelector('.eval-status-label').style.opacity = '1';
        
        checkBatchGating();
    }

    function updateItemFeedback(idx, val) {
        if (reviewState.item_evaluations[idx]) {
            reviewState.item_evaluations[idx].feedback = val;
        }
    }

    function checkBatchGating() {
        const totalItems = document.querySelectorAll('.item-eval-toolbar').length;
        const evaluatedCount = Object.keys(reviewState.item_evaluations).length;
        
        const btnApprove = document.getElementById('batchBtnApprove');
        const btnReject = document.getElementById('batchBtnReject');
        if (!btnApprove) return;

        const hasRejection = Object.values(reviewState.item_evaluations).some(e => e.status === 'rejected');
        
        if (hasRejection) {
            btnApprove.innerHTML = '<iconify-icon icon="solar:shield-warning-bold-duotone"></iconify-icon> Confirmar Ajustes';
            btnApprove.classList.add('btn-warning');
            if (btnReject) btnReject.style.display = 'none';
        } else {
            btnApprove.innerHTML = '<iconify-icon icon="solar:verified-check-bold-duotone"></iconify-icon> Aprovar Tudo';
            btnApprove.classList.remove('btn-warning');
            if (btnReject) btnReject.style.display = 'flex';
        }
    }

    async function processBatchReview(type) {
        // Auto-fill evaluations if "Aprovar Tudo" is clicked directly
        if (type === 'approved') {
            document.querySelectorAll('.item-eval-toolbar').forEach(toolbar => {
                const itemId = toolbar.getAttribute('data-item-id');
                if (!reviewState.item_evaluations[itemId]) {
                    reviewState.item_evaluations[itemId] = { status: 'approved', feedback: '' };
                }
            });
        } else if (type === 'rejected') {
            // If "Rejeitar Tudo" is clicked and no items are evaluated, prompt for reason or just reject all
            const evaluatedCount = Object.keys(reviewState.item_evaluations).length;
            if (evaluatedCount === 0) {
                document.querySelectorAll('.item-eval-toolbar').forEach(toolbar => {
                    const itemId = toolbar.getAttribute('data-item-id');
                    reviewState.item_evaluations[itemId] = { status: 'rejected', feedback: 'Passo rejeitado integralmente.' };
                });
            }
        }

        const hasRejection = Object.values(reviewState.item_evaluations).some(e => e.status === 'rejected');
        const action = hasRejection ? 'reject' : 'approve';
        const responseId = '<?= $currentStep['response_id'] ?? '' ?>';
        const tenantSlug = '<?= $tenant['slug'] ?>';

        if (!responseId) {
            Swal.fire('Erro', 'ID da resposta não encontrado. Tente atualizar a página.', 'error');
            return;
        }
        
        const result = await Swal.fire({
            title: hasRejection ? 'Confirmar Ajustes?' : 'Aprovar tudo?',
            text: hasRejection ? 'O desbravador precisará corrigir os itens marcados.' : 'Isso validará todos os requisitos deste passo.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sim, confirmar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                // Using path from root to ensure absolute reliability across different access environments
                const targetUrl = `/${tenantSlug}/admin/aprovacoes/${responseId}/${action}`;
                console.log('Submitting to:', targetUrl);

                const response = await fetch(targetUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        feedback: '', 
                        item_evaluations: reviewState.item_evaluations
                    })
                });
                
                if (!response.ok) {
                    const errorText = await response.text();
                    let errorMsg = `Erro ${response.status}`;
                    try {
                        const errorJson = JSON.parse(errorText);
                        errorMsg = errorJson.error || errorMsg;
                    } catch(e) {
                        // Server returned non-JSON (likely a PHP error)
                        errorMsg = `Erro no servidor (${response.status}). Verifique os logs.`;
                    }
                    throw new Error(errorMsg);
                }

                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch(e) {
                    console.error('Invalid JSON response:', responseText.substring(0, 200));
                    throw new Error('Resposta inválida do servidor. A ação pode ter sido processada — recarregue a página.');
                }

                if (data.success) {
                    Swal.fire('Sucesso!', data.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Erro', data.error || 'Erro desconhecido', 'error');
                }
            } catch (e) {
                console.error('Submission Error:', e);
                Swal.fire('Falha na Comunicação', e.message, 'error');
            }
        }
    }
</script>

</body>
</html>
