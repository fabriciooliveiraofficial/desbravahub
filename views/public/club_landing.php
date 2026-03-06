<?php
/**
 * Public Landing Page - /c/[slug]
 * Event Hub v2 - Hybrid X/Insta
 */
use App\Core\App;

$stats = [];
try {
    $stats = [
        'members' => db_fetch_column("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND status = 'active'", [$profile['tenant_id']]) ?? 0,
        'activities' => db_fetch_column("SELECT COUNT(*) FROM activities WHERE tenant_id = ? AND status = 'active'", [$profile['tenant_id']]) ?? 0,
    ];
} catch (Exception $e) {
    $stats = ['members' => 0, 'activities' => 0];
}

$defaultVibe = $profile['layout_vibe'] ?? 'light';
?>
<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

<style>
/* CSS Variables for Dynamic Theme */
:root {
    /* Layout Constants */
    --max-w-feed: 600px;
    --max-w-grid: 900px;
    --header-h: 80px;

    /* Theme Transitions */
    --t-color: color 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    --t-bg: background-color 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    --t-border: border-color 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

/* Light Theme: Desert Scouting */
[data-theme='light'] {
    --bg-main: #FDFCF8;
    --bg-surface: #FFFFFF;
    --bg-elevated: #F3F1EC;
    --text-primary: #2D3748;
    --text-secondary: #718096;
    --border: #E2E8F0;
    --accent: #D97706; /* Amber/Scout */
    --accent-hover: #b45309;
    --accent-glow: rgba(217, 119, 6, 0.15);
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
    --shadow-md: 0 10px 20px -5px rgba(217, 119, 6, 0.1);
}

/* Dark Theme: Deep Night HUD */
[data-theme='dark'] {
    --bg-main: #0B0F19;
    --bg-surface: #111827;
    --bg-elevated: #1E293B;
    --text-primary: #F8FAFC;
    --text-secondary: #94A3B8;
    --border: #1E293B;
    --accent: #38BDF8; /* Cyan Neon */
    --accent-hover: #0284c7;
    --accent-glow: rgba(56, 189, 248, 0.15);
    --shadow-sm: 0 4px 6px rgba(0,0,0,0.3);
    --shadow-md: 0 10px 20px -5px rgba(0,0,0,0.5);
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    background-color: var(--bg-main);
    color: var(--text-primary);
    font-family: 'Plus Jakarta Sans', sans-serif;
    transition: var(--t-bg), var(--t-color);
    overflow-x: hidden;
}

/* Theme Switcher 3D Effect */
.theme-toggle-wrapper {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1000;
    perspective: 1000px;
}

.theme-toggle {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    color: var(--text-primary);
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 10px 20px rgba(0,0,0,0.1), inset 0 -3px 6px rgba(0,0,0,0.1);
    transform-style: preserve-3d;
    transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s;
}

.theme-toggle:hover {
    transform: scale(1.1) rotateY(15deg);
    box-shadow: 0 15px 25px rgba(0,0,0,0.15), inset 0 -3px 6px rgba(0,0,0,0.1);
    border-color: var(--accent);
}

.theme-toggle.flipping {
    animation: flip3D 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes flip3D {
    0% { transform: scale(1) rotateY(0deg); }
    50% { transform: scale(1.2) rotateY(180deg); }
    100% { transform: scale(1) rotateY(360deg); }
}

.theme-toggle iconify-icon {
    font-size: 1.8rem;
    transform: translateZ(20px);
}

/* Parallax Hero */
.hero {
    position: relative;
    min-height: 70vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 100px 20px 40px;
    text-align: center;
    overflow: hidden;
    border-bottom: 1px solid var(--border);
}

.hero-bg-layer {
    position: absolute;
    top: -20%;
    left: -10%;
    right: -10%;
    bottom: -20%;
    background: radial-gradient(circle at center, var(--accent-glow) 0%, transparent 60%);
    z-index: 0;
    transform: translateZ(-1px) scale(1.5);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 10;
    max-width: 800px;
    width: 100%;
    transform: translateZ(0); /* Force HW act */
}

.club-logo {
    width: 120px;
    height: 120px;
    border-radius: 32px;
    object-fit: cover;
    border: 4px solid var(--bg-surface);
    box-shadow: var(--shadow-md);
    margin: 0 auto 24px;
    transition: transform 0.3s ease;
}
.club-logo:hover {
    transform: translateY(-5px);
}

.club-title {
    font-family: 'Outfit', sans-serif;
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    line-height: 1.1;
    margin: 0 0 16px 0;
    background: linear-gradient(135deg, var(--text-primary) 30%, var(--text-secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.04em;
}

.club-motto {
    font-size: 1.1rem;
    color: var(--accent);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 24px;
}

.club-desc {
    font-size: 1.1rem;
    color: var(--text-secondary);
    line-height: 1.6;
    max-width: 600px;
    margin: 0 auto 32px;
}

.ideals-bar {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
    margin-top: 24px;
}

.ideal-pill {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: default;
    transition: all 0.2s ease;
}

.ideal-pill:hover {
    background: var(--bg-surface);
    border-color: var(--accent);
    color: var(--accent);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.ideal-pill iconify-icon {
    font-size: 1.2rem;
    color: var(--text-secondary);
}
.ideal-pill:hover iconify-icon {
    color: var(--accent);
}

/* Hybrid Layout */
.layout-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 40px 20px;
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

/* X-Style Feed (Left/Top) */
.feed-column {
    flex: 1;
    max-width: var(--max-w-feed);
    width: 100%;
    margin: 0 auto; 
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.feed-header {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.feed-post {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    transition: var(--t-bg), var(--t-border);
    box-shadow: var(--shadow-sm);
}

.feed-post:hover {
    border-color: var(--accent);
}

.post-author {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.post-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    background: var(--bg-elevated);
    border: 2px solid var(--border);
}

.post-meta h4 {
    margin: 0;
    font-weight: 700;
    font-size: 1.05rem;
    display: flex;
    align-items: center;
    gap: 6px;
}
.post-meta .verified { font-size: 1rem; color: var(--accent); }
.post-meta p {
    margin: 2px 0 0 0;
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.post-content {
    font-size: 1rem;
    line-height: 1.5;
    margin-bottom: 16px;
    color: var(--text-primary);
}

.event-highlight {
    background: var(--bg-elevated);
    border: 1px solid var(--accent);
    border-radius: 16px;
    padding: 16px;
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.event-name { font-weight: 800; font-size: 1.1rem; color: var(--accent); }
.event-detail { font-size: 0.9rem; color: var(--text-secondary); display:flex; align-items:center; gap:6px;}

.post-actions {
    display: flex;
    gap: 24px;
    margin-top: 20px;
    border-top: 1px solid var(--border);
    padding-top: 16px;
}
.post-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: none;
    border: none;
    color: var(--text-secondary);
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
    font-family: inherit;
    padding: 8px;
    border-radius: 8px;
}
.post-btn:hover { background: var(--bg-elevated); color: var(--accent); }
.post-btn.liked { color: #f43f5e; }
.post-btn.liked iconify-icon { color: #f43f5e; }


/* Instagram-Style Grid (Right/Bottom) */
.grid-column {
    flex: 1.5;
    max-width: var(--max-w-grid);
    width: 100%;
}

.grid-header {
    font-family: 'Outfit', sans-serif;
    font-size: 1.5rem;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 32px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.insta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 16px;
}

.grid-item {
    aspect-ratio: 1 / 1;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    background: var(--bg-surface);
    border: 1px solid var(--border);
    cursor: pointer;
    group: relative;
    box-shadow: var(--shadow-sm);
}

.grid-item img, .grid-item video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.grid-item:hover img {
    transform: scale(1.05);
}

/* Media Overlay on Hover */
.grid-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.4);
    opacity: 0;
    transition: opacity 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 3rem;
    border-radius: 16px;
    z-index: 10;
}
.grid-item:hover .grid-overlay {
    opacity: 1;
}

.grid-meta {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    padding: 20px 16px 16px;
    background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
    color: white;
    z-index: 5;
    display: flex;
    flex-direction: column;
}
.grid-meta-title { font-weight: 700; font-size: 0.95rem; margin-bottom: 4px; 
    text-overflow: ellipsis; white-space: nowrap; overflow: hidden;}
.grid-meta-author { font-size: 0.8rem; opacity: 0.9; }

/* Type Icon (Video/Image) */
.media-type-icon {
    position: absolute;
    top: 12px; right: 12px;
    color: white;
    font-size: 1.2rem;
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));
    z-index: 5;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    .layout-container {
        flex-direction: column-reverse; /* Put Feed below Grid usually, or keep original? Lets put Feed top, Grid bottom */
        flex-direction: column;
    }
    .feed-column, .grid-column {
        max-width: 100%;
    }
}
@media (max-width: 640px) {
    .theme-toggle {
        top: auto;
        bottom: 24px;
        right: 24px;
    }
    .hero { padding: 80px 16px 32px; }
    .insta-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
    .grid-item { border-radius: 12px; }
    .grid-overlay { border-radius: 12px; }
}
</style>

<div class="theme-toggle-wrapper">
    <div class="theme-toggle" id="themeToggle" onclick="toggleTheme()">
        <iconify-icon icon="solar:compass-bold-duotone" id="themeIcon"></iconify-icon>
    </div>
</div>

<main>
    <section class="hero">
        <div class="hero-bg-layer" id="parallaxBg"></div>
        <div class="hero-content">
            <?php if (!empty($profile['logo_url'])): ?>
                <img src="<?= htmlspecialchars($profile['logo_url']) ?>" alt="Logo" class="club-logo">
            <?php else: ?>
                <div class="club-logo" style="display:flex; align-items:center; justify-content:center; font-size:4rem; background:var(--bg-elevated);">⛺</div>
            <?php endif; ?>

            <h1 class="club-title"><?= htmlspecialchars($profile['display_name']) ?></h1>
            
            <?php if (!empty($profile['club_motto'])): ?>
                <div class="club-motto">"<?= htmlspecialchars($profile['club_motto']) ?>"</div>
            <?php endif; ?>

            <p class="club-desc"><?= nl2br(htmlspecialchars($profile['welcome_message'])) ?></p>

            <div class="ideals-bar">
                <?php if (!empty($profile['club_vow'])): ?>
                <div class="ideal-pill" title="<?= htmlspecialchars($profile['club_vow']) ?>">
                    <iconify-icon icon="solar:hand-heart-bold-duotone"></iconify-icon>
                    Voto
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['club_law'])): ?>
                <div class="ideal-pill" title="<?= htmlspecialchars($profile['club_law']) ?>">
                    <iconify-icon icon="solar:book-bookmark-bold-duotone"></iconify-icon>
                    Lei
                </div>
                <?php endif; ?>
                <div class="ideal-pill">
                    <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                    <?= number_format($stats['members']) ?> Membros
                </div>
            </div>
        </div>
    </section>

    <div class="layout-container">
        <!-- Feed Column (X-Style) -->
        <div class="feed-column">
            <h2 class="feed-header">
                <iconify-icon icon="solar:feed-bold-duotone" style="color:var(--accent);"></iconify-icon>
                Avisos & Eventos
            </h2>

            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <?php 
                        $date = new DateTime($event['start_datetime']); 
                        $isFull = $event['max_participants'] > 0 && ($event['enrolled_count'] ?? 0) >= $event['max_participants'];
                    ?>
                    <article class="feed-post">
                        <div class="post-author">
                            <?php if (!empty($profile['logo_url'])): ?>
                                <img src="<?= htmlspecialchars($profile['logo_url']) ?>" alt="Logo" class="post-avatar">
                            <?php else: ?>
                                <div class="post-avatar" style="display:flex;align-items:center;justify-content:center;">⛺</div>
                            <?php endif; ?>
                            <div class="post-meta">
                                <h4><?= htmlspecialchars($profile['display_name']) ?> <iconify-icon class="verified" icon="solar:check-circle-bold-duotone"></iconify-icon></h4>
                                <p>Admin do Clube • <?= $date->format('d/m/Y') ?></p>
                            </div>
                        </div>

                        <div class="post-content">
                            Esteja preparado! Temos um novo evento imperdível agendado para o nosso clube.
                            
                            <div class="event-highlight">
                                <div class="event-name"><?= htmlspecialchars($event['title']) ?></div>
                                <div class="event-detail">
                                    <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
                                    <?= $date->format('d/m/Y \à\s H:i') ?>
                                </div>
                                <?php if ($event['location']): ?>
                                <div class="event-detail">
                                    <iconify-icon icon="solar:map-point-bold-duotone"></iconify-icon>
                                    <?= htmlspecialchars($event['location']) ?>
                                </div>
                                <?php endif; ?>
                                <div class="event-detail">
                                    <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                                    <?= $event['is_paid'] ? 'R$ ' . number_format($event['price'], 2, ',', '.') : 'Gratuito' ?>
                                </div>
                            </div>
                        </div>

                        <div class="post-actions">
                            <a href="<?= base_url('c/' . $profile['slug'] . '/evento/' . $event['slug']) ?>" style="text-decoration:none;">
                                <button class="post-btn" style="color:var(--accent); background:var(--accent-glow);">
                                    <iconify-icon icon="solar:ticket-sale-bold-duotone"></iconify-icon>
                                    <?= $isFull ? 'Lista de Espera' : 'Garantir Vaga' ?>
                                </button>
                            </a>
                            
                            <?php 
                                $sessionId = $_COOKIE['hub_session_id'] ?? '';
                                $eventLikeCount = db_fetch_column("SELECT COUNT(*) FROM public_interactions WHERE tenant_id = ? AND source_type = 'event' AND source_id = ? AND interaction_type = 'like'", [$profile['tenant_id'], $event['id']]);
                                $eventHasLiked = $sessionId ? db_fetch_one("SELECT id FROM public_interactions WHERE tenant_id = ? AND source_type = 'event' AND source_id = ? AND session_hash = ? AND interaction_type = 'like'", [$profile['tenant_id'], $event['id'], $sessionId]) : false;
                                $likedClassEvent = $eventHasLiked ? 'liked' : '';
                                $heartIconEvent = $eventHasLiked ? 'solar:heart-bold' : 'solar:heart-linear';
                            ?>
                            <button class="post-btn <?= $likedClassEvent ?>" id="btnLike_event_<?= $event['id'] ?>" onclick="handleLike('event', <?= $event['id'] ?>)">
                                <iconify-icon icon="<?= $heartIconEvent ?>" style="font-size: 1.2rem;"></iconify-icon>
                                <span id="countLike_event_<?= $event['id'] ?>"><?= $eventLikeCount ?></span>
                            </button>

                            <button class="post-btn share-btn" onclick="shareUrl('<?= base_url('c/' . $profile['slug'] . '/evento/' . $event['slug']) ?>')">
                                <iconify-icon icon="solar:share-bold-duotone"></iconify-icon>
                                Compartilhar
                            </button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 40px; color: var(--text-secondary); border: 1px dashed var(--border); border-radius: 20px;">
                    <iconify-icon icon="solar:sleeping-bold-duotone" style="font-size:3rem; margin-bottom:16px; opacity:0.5;"></iconify-icon><br>
                    Nenhum evento no momento.
                </div>
            <?php endif; ?>
        </div>

        <!-- Gallery Column (Instagram-Style) -->
        <div class="grid-column">
            <h2 class="grid-header">
                <iconify-icon icon="solar:gallery-wide-bold-duotone" style="color:var(--accent);"></iconify-icon>
                Galeria Submetida
            </h2>

            <?php if (!empty($curatedMedia)): ?>
                <div class="insta-grid">
                    <?php foreach ($curatedMedia as $media): ?>
                        <?php 
                            $rawUrl = trim((string)($media['media_content'] ?? ''));
                            $isVid = preg_match('/youtube\.com|youtu\.be|tiktok\.com|instagram\.com|reels|shorts|\.mp4/i', $rawUrl);
                            
                            // Validate raw video files vs social links
                            $isDirectVid = preg_match('/(\.mp4|\.webm|\.mov)$/i', $rawUrl) || strpos($rawUrl, 'storage/') !== false;
                            
                            $url = $rawUrl;
                            $thumb = '';

                            if (!empty($media['thumbnail_url'])) {
                                // Prioritize the original cached thumbnail!
                                $thumb = $media['thumbnail_url'];
                            } elseif ($isVid) {
                                // YouTube high-res thumb
                                if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $match)) {
                                    $thumb = 'https://img.youtube.com/vi/' . $match[1] . '/hqdefault.jpg';
                                } elseif (strpos($url, 'tiktok.com') !== false) {
                                    $thumb = 'https://www.google.com/s2/favicons?sz=128&domain=tiktok.com';
                                } elseif (strpos($url, 'instagram.com') !== false) {
                                    $thumb = 'https://www.google.com/s2/favicons?sz=128&domain=instagram.com';
                                } elseif ($isDirectVid) {
                                    $thumb = 'https://placehold.co/600x600/1e293b/white?text=Video';
                                } else {
                                    // It was flagged as isVid (social link) but no specific thumb logic worked
                                    $thumb = 'https://www.google.com/s2/favicons?sz=128&domain=' . parse_url($url, PHP_URL_HOST);
                                }
                            } else {
                                $thumb = strpos($url, 'storage/') === 0 ? base_url('/' . $url) : $url;
                            }
                            
                            // Prevent broken Thumb if URL is not a direct image and no thumbnail was cached
                            if (empty($media['thumbnail_url']) && !$isVid && !preg_match('/\.(jpg|jpeg|png|webp|gif|svg|avif)/i', $thumb) && strpos($thumb, 'http') === 0 && strpos($thumb, 'storage/') === false) {
                                 // It's a link but not an image, show placeholder
                                 $thumb = 'https://www.google.com/s2/favicons?sz=128&domain=' . parse_url($thumb, PHP_URL_HOST);
                            }
                            
                            $sessionId = $_COOKIE['hub_session_id'] ?? '';
                            $likeCount = db_fetch_column("SELECT COUNT(*) FROM public_interactions WHERE tenant_id = ? AND source_type = ? AND source_id = ? AND interaction_type = 'like'", [$profile['tenant_id'], $media['source_type'], $media['source_id']]);
                            $hasLiked = $sessionId ? db_fetch_one("SELECT id FROM public_interactions WHERE tenant_id = ? AND source_type = ? AND source_id = ? AND session_hash = ? AND interaction_type = 'like'", [$profile['tenant_id'], $media['source_type'], $media['source_id'], $sessionId]) : false;
                        ?>
                        <div class="grid-item" onclick="openMediaModal('<?= htmlspecialchars($media['media_content']) ?>', <?= $isVid ? 'true' : 'false' ?>, '<?= $media['source_type'] ?>', <?= $media['source_id'] ?>, <?= $likeCount ?>, <?= $hasLiked ? 'true' : 'false' ?>)">
                            <img src="<?= htmlspecialchars($thumb) ?>" alt="Media Thumbnail" loading="lazy">
                            <div class="media-type-icon">
                                <iconify-icon icon="<?= $isVid ? 'solar:play-circle-bold-duotone' : 'solar:camera-bold-duotone' ?>"></iconify-icon>
                            </div>
                            <div class="grid-overlay">
                                <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon>
                            </div>
                            <div class="grid-meta">
                                <span class="grid-meta-title"><?= htmlspecialchars($media['title']) ?></span>
                                <span class="grid-meta-author">por <?= htmlspecialchars($media['user_name']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px; color: var(--text-secondary); border: 1px dashed var(--border); border-radius: 20px;">
                    <iconify-icon icon="solar:folder-with-files-bold-duotone" style="font-size:3rem; margin-bottom:16px; opacity:0.5;"></iconify-icon><br>
                    Nenhuma mídia destacada pelos diretores ainda.
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Media Modal -->
<div id="mediaModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(8px);">
    <button onclick="closeMediaModal()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:white; font-size:2rem; cursor:pointer;"><iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon></button>
    <div id="modalContent" style="width:100%; max-width:800px; padding:20px; display:flex; flex-direction: column; align-items:center;">
        <div id="mediaContainer" style="width:100%;"></div>
        <div id="mediaActions" style="margin-top: 16px; display: flex; gap: 16px;">
            <!-- Actions Injected dynamically -->
        </div>
    </div>
</div>

<script>
    // Theme Management
    const themeToggleEl = document.getElementById('themeToggle');
    const themeIcon = document.getElementById('themeIcon');
    let currentTheme = localStorage.getItem('theme_preference') || '<?= htmlspecialchars($defaultVibe) ?>' || 'light';
    
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        if (theme === 'dark') {
            themeIcon.setAttribute('icon', 'solar:fire-bold-duotone'); // Fogueira
            themeIcon.style.color = '#38BDF8'; // Neon Blue fire
        } else {
            themeIcon.setAttribute('icon', 'solar:compass-bold-duotone'); // Bússola
            themeIcon.style.color = '#D97706'; // Amber compass
        }
    }

    function toggleTheme() {
        themeToggleEl.classList.remove('flipping');
        void themeToggleEl.offsetWidth; // Trigger reflow
        themeToggleEl.classList.add('flipping');
        
        setTimeout(() => {
            currentTheme = currentTheme === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme_preference', currentTheme);
            applyTheme(currentTheme);
        }, 300); // Change icon halfway through flip
    }
    
    // Init
    applyTheme(currentTheme);

    // Parallax Hero
    const parallaxBg = document.getElementById('parallaxBg');
    window.addEventListener('scroll', () => {
        const scrolled = window.scrollY;
        if (scrolled < window.innerHeight) {
            parallaxBg.style.transform = `translateZ(-1px) scale(1.5) translateY(${scrolled * 0.4}px)`;
        }
    });

    // Interaction Tools
    function shareUrl(url) {
        if (navigator.share) {
            navigator.share({
                title: 'Confira este evento do Clube!',
                url: url
            });
        } else {
            navigator.clipboard.writeText(url);
            alert('Link copiado para a área de transferência!');
        }
    }

    // Media Modal (Gallery)
    const mediaModal = document.getElementById('mediaModal');
    const mediaContainer = document.getElementById('mediaContainer');
    const mediaActions = document.getElementById('mediaActions');
    
    function openMediaModal(url, isVideo, sourceType, sourceId, likeCount, hasLiked) {
        let contentHtml = '';
        if (isVideo) {
            let ytMatch = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/ ]{11})/i);
            let ttMatch = url.match(/(?:https?:\/\/)?(?:www\.)?tiktok\.com\/.*\/video\/([0-9]+)/i);
            let igMatch = url.match(/instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/i);

            if (ytMatch) {
                contentHtml = `<iframe width="100%" style="aspect-ratio:16/9; border-radius:16px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);" src="https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>`;
            } else if (ttMatch) {
                contentHtml = `<iframe width="100%" style="aspect-ratio:9/16; max-height: 80vh; border-radius:16px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);" src="https://www.tiktok.com/embed/v2/${ttMatch[1]}" frameborder="0" allowfullscreen></iframe>`;
            } else if (igMatch) {
                contentHtml = `<iframe width="100%" style="aspect-ratio:9/16; max-height: 80vh; border-radius:16px; box-shadow: 0 20px 40px rgba(0,0,0,0.5);" src="https://www.instagram.com/reel/${igMatch[1]}/embed" frameborder="0" scrolling="no" allowtransparency="true"></iframe>`;
            } else if (url.includes('tiktok.com')) {
                // Short TikTok links handled gracefully
                contentHtml = `
                    <div style="text-align:center; padding: 40px; background: rgba(255,255,255,0.05); border-radius: 20px; border: 1px solid rgba(255,255,255,0.1);">
                        <iconify-icon icon="logos:tiktok-icon" style="font-size: 4rem; margin-bottom: 20px;"></iconify-icon>
                        <h3 style="color:white; margin-bottom: 20px;">Vídeo do TikTok</h3>
                        <p style="color:rgba(255,255,255,0.7); margin-bottom: 30px;">O link curto não suporta pré-visualização direta no momento.</p>
                        <a href="${url}" target="_blank" class="hud-btn primary" style="text-decoration:none; display:inline-flex; align-items:center; gap: 10px;">
                            <iconify-icon icon="solar:round-alt-arrow-right-bold"></iconify-icon>
                            ABRIR NO TIKTOK
                        </a>
                    </div>
                `;
            } else if (url.match(/(\.mp4|\.webm|\.mov)$/i) || url.includes('storage/')) {
                contentHtml = `<video src="${url}" controls autoplay playsinline style="max-height:70vh; max-width:100%; width:100%; border-radius:16px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); object-fit: contain;"></video>`;
            } else {
                // Generative link fallback inside modal
                contentHtml = `
                    <div style="text-align:center; padding: 40px;">
                        <iconify-icon icon="solar:link-bold-duotone" style="font-size: 4rem; color: #38BDF8; margin-bottom: 20px;"></iconify-icon>
                        <h3 style="color:white; margin-bottom: 30px;">Link Externo</h3>
                        <a href="${url}" target="_blank" class="hud-btn primary" style="text-decoration:none;">ABRIR LINK ORIGINAL</a>
                    </div>
                `;
            }
        } else {
            let imgUrl = url.startsWith('storage/') ? `/${url}` : url;
            contentHtml = `<img src="${imgUrl}" style="max-height:75vh; max-width:100%; width:100%; border-radius:16px; object-fit:contain; box-shadow: 0 20px 40px rgba(0,0,0,0.5);">`;
        }
        
        mediaContainer.innerHTML = contentHtml;

        let likedClass = hasLiked ? 'liked' : '';
        let heartIcon = hasLiked ? 'solar:heart-bold' : 'solar:heart-linear';
        
        mediaActions.innerHTML = `
            <button class="post-btn ${likedClass}" id="btnLike_${sourceType}_${sourceId}" onclick="handleLike('${sourceType}', ${sourceId})" style="color: white; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                <iconify-icon icon="${heartIcon}" style="font-size: 1.5rem;"></iconify-icon>
                <span id="countLike_${sourceType}_${sourceId}">${likeCount}</span>
            </button>
            <button class="post-btn" onclick="shareUrl('${window.location.href}')"  style="color: white; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                <iconify-icon icon="solar:share-bold-duotone" style="font-size: 1.5rem;"></iconify-icon>
            </button>
        `;

        mediaModal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    async function handleLike(sourceType, sourceId) {
        const btn = document.getElementById(`btnLike_${sourceType}_${sourceId}`);
        const countSpan = document.getElementById(`countLike_${sourceType}_${sourceId}`);
        
        try {
            const formData = new FormData();
            formData.append('source_type', sourceType);
            formData.append('source_id', sourceId);

            const response = await fetch('<?= base_url('c/' . $profile['slug'] . '/like') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            if (data.success) {
                countSpan.innerText = data.count;
                if (data.action === 'liked') {
                    btn.classList.add('liked');
                    btn.querySelector('iconify-icon').setAttribute('icon', 'solar:heart-bold');
                } else {
                    btn.classList.remove('liked');
                    btn.querySelector('iconify-icon').setAttribute('icon', 'solar:heart-linear');
                }
                // Haptic feedback
                if (navigator.vibrate) navigator.vibrate(50);
            }
        } catch(e) {
            console.error('Like error', e);
        }
    }

    function closeMediaModal() {
        mediaContainer.innerHTML = '';
        mediaModal.style.display = 'none';
        document.body.style.overflow = 'auto'; // restore scroll
    }

    // Close on click outside
    mediaModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMediaModal();
        }
    });

</script>
