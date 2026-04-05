<?php
/**
 * Public Landing Page — /c/[slug]
 * Reels/TikTok-inspired: full-screen mobile scroll-snap + desktop 3-column grid
 */
use App\Core\App;

$stats = [];
try {
    $stats = [
        'members'    => db_fetch_column("SELECT COUNT(*) FROM users WHERE tenant_id = ? AND status = 'active'", [$profile['tenant_id']]) ?? 0,
        'activities' => db_fetch_column("SELECT COUNT(*) FROM activities WHERE tenant_id = ? AND status = 'active'", [$profile['tenant_id']]) ?? 0,
    ];
} catch (Exception $e) {
    $stats = ['members' => 0, 'activities' => 0];
}

$clubSlug    = $profile['slug'] ?? '';
$likeUrl     = base_url('c/' . $clubSlug . '/like');
$mediaApiUrl = base_url('c/' . $clubSlug . '/media');
$commentBase = base_url('c/' . $clubSlug . '/media');
$sessionId   = $_COOKIE['hub_session_id'] ?? '';
?>

<style>
/* ── Hub palette — neon blue + neon green, subtle & elegant ────────── */
[data-theme='dark'] {
    --accent:           #00ccff;                        /* neon blue      */
    --accent-hover:     #00aadd;
    --accent-glow:      rgba(0, 204, 255, 0.10);
    --accent-secondary: #00e07a;                        /* neon green     */
    --primary:          #00ccff;
    --primary-dark:     #00aadd;
    --bg-base:          #07090f;
    --dark-bg:          #07090f;
    --bg-main:          #07090f;
    --bg-surface:       #0d1017;
    --bg-elevated:      #141b26;
    --border:           rgba(0, 204, 255, 0.10);
    --text-primary:     #e8f4ff;
    --text-secondary:   #7a9ab8;
    --shadow-sm:        0 1px 8px rgba(0,0,0,0.4);
    --shadow-md:        0 4px 28px rgba(0,0,0,0.55);
    --nav-bg:           rgba(7, 9, 15, 0.85);
}
[data-theme='light'] {
    --accent:           #0088bb;                        /* deep cyan-blue */
    --accent-hover:     #006a95;
    --accent-glow:      rgba(0, 136, 187, 0.10);
    --accent-secondary: #059669;                        /* deep green     */
    --primary:          #0088bb;
    --primary-dark:     #006a95;
    --bg-base:          #f0f5fa;
    --bg-surface:       #ffffff;
    --bg-elevated:      #e8f1f8;
    --border:           rgba(0, 136, 187, 0.14);
    --shadow-sm:        0 1px 6px rgba(0,0,0,0.08);
    --shadow-md:        0 4px 20px rgba(0,0,0,0.12);
}

/* ── Reset & base ─────────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }
body { overflow-x: hidden; }

/* ── Conversion Hero ──────────────────────────────────────────────── */
.conversion-hero {
    position: relative;
    width: 100%;
    height: min(75vh, 680px);
    display: flex;
    align-items: flex-end;
    justify-content: flex-start;
    overflow: hidden;
}

/* YouTube iframe scaled to fill (letterbox-proof) */
.hero-yt-wrap {
    position: absolute;
    inset: -10%;          /* oversized so resize doesn't expose letterbox */
    pointer-events: none;
    z-index: 0;
}
.hero-yt-wrap iframe {
    width: 100%;
    height: 100%;
    border: none;
    transform: scale(1.05); /* hide any thin edge artifact */
}

/* Image background */
.hero-img-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    z-index: 0;
    transform: scale(1.03);
    transition: transform 8s ease;
}
.conversion-hero:hover .hero-img-bg { transform: scale(1); }

/* Default gradient background */
.hero-gradient-bg {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #07090f 0%, #0d1f2d 50%, #091a10 100%);
    z-index: 0;
}

/* Dark cinematic overlay — stronger at bottom */
.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(0,0,0,0.25) 0%,
        rgba(0,0,0,0.3) 40%,
        rgba(0,0,0,0.72) 80%,
        rgba(7,9,15,0.95) 100%
    );
    z-index: 1;
}

/* Content */
.hero-content {
    position: relative;
    z-index: 2;
    padding: 40px 60px;
    max-width: 760px;
}

.hero-logo {
    width: 72px; height: 72px;
    border-radius: 18px;
    object-fit: cover;
    border: 2px solid rgba(0,204,255,0.4);
    box-shadow: 0 0 24px rgba(0,204,255,0.2);
    margin-bottom: 20px;
    display: block;
}

.hero-headline {
    font-family: 'Outfit', 'Inter', sans-serif;
    font-size: clamp(2.2rem, 4.5vw, 3.8rem);
    font-weight: 900;
    line-height: 1.08;
    letter-spacing: -0.03em;
    color: #ffffff;
    margin: 0 0 16px;
    text-shadow: 0 2px 20px rgba(0,0,0,0.5);
}
/* Accent the last word in neon */
.hero-headline em {
    font-style: normal;
    background: linear-gradient(90deg, #00ccff, #00e07a);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.hero-subheadline {
    font-size: clamp(0.95rem, 1.8vw, 1.15rem);
    color: rgba(255,255,255,0.75);
    line-height: 1.6;
    margin: 0 0 28px;
    max-width: 540px;
    text-shadow: 0 1px 8px rgba(0,0,0,0.4);
}

.hero-cta-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 24px;
}
.hero-cta-primary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 28px;
    background: linear-gradient(135deg, #00ccff, #00e07a);
    color: #04090e;
    border-radius: 14px;
    font-weight: 800;
    font-size: 0.95rem;
    text-decoration: none;
    box-shadow: 0 0 24px rgba(0,204,255,0.35);
    transition: all 0.2s;
}
.hero-cta-primary:hover {
    box-shadow: 0 0 40px rgba(0,204,255,0.55);
    transform: translateY(-2px);
}
.hero-cta-primary .material-icons-round { font-size: 20px; }

.hero-cta-secondary {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 13px 24px;
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.9);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 14px;
    font-weight: 700;
    font-size: 0.95rem;
    text-decoration: none;
    backdrop-filter: blur(8px);
    transition: all 0.2s;
}
.hero-cta-secondary:hover {
    background: rgba(255,255,255,0.14);
    border-color: rgba(0,204,255,0.4);
    color: #fff;
}
.hero-cta-secondary .material-icons-round { font-size: 20px; }

/* Info pills */
.hero-meta-pills { display: flex; gap: 10px; flex-wrap: wrap; }
.hero-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px;
    background: rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px;
    font-size: 0.78rem;
    color: rgba(255,255,255,0.7);
    backdrop-filter: blur(6px);
}
.hero-pill .material-icons-round { font-size: 15px; color: #00ccff; }

/* Scroll hint */
.hero-scroll-hint {
    position: absolute;
    bottom: 20px; left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    color: rgba(255,255,255,0.4);
    animation: bounceDown 1.8s ease infinite;
}
.hero-scroll-hint .material-icons-round { font-size: 2rem; display: block; }
@keyframes bounceDown {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50%       { transform: translateX(-50%) translateY(8px); }
}

/* Mobile adjustments */
@media (max-width: 768px) {
    .conversion-hero { height: min(60vh, 520px); align-items: flex-end; }
    .hero-content { padding: 28px 20px; }
    .hero-logo { width: 56px; height: 56px; border-radius: 14px; }
    .hero-cta-row { gap: 10px; }
    .hero-cta-primary, .hero-cta-secondary { padding: 11px 20px; font-size: 0.88rem; }
}

/* ── Layout shell ─────────────────────────────────────────────────── */
.hub-shell {
    display: grid;
    grid-template-columns: 300px 1fr 300px;
    grid-template-rows: auto;
    min-height: 100vh;
    max-width: 1400px;
    margin: 0 auto;
    padding: 24px 16px 80px;
    gap: 32px;
    align-items: start;
}

/* ── Left sidebar — Club identity ─────────────────────────────────── */
.hub-sidebar-left {
    position: sticky;
    top: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.club-card {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    text-align: center;
}

.club-avatar {
    width: 96px; height: 96px;
    border-radius: 24px;
    object-fit: cover;
    border: 3px solid var(--border);
    box-shadow: var(--shadow-md);
    margin: 0 auto 16px;
    display: block;
}
.club-avatar-placeholder {
    width: 96px; height: 96px;
    border-radius: 24px;
    background: var(--bg-elevated);
    border: 3px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem;
    margin: 0 auto 16px;
}
.club-name {
    font-family: 'Outfit', sans-serif;
    font-size: 1.25rem; font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 6px;
    line-height: 1.2;
}
.club-motto {
    font-size: 0.8rem; color: var(--accent);
    font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.08em; margin-bottom: 16px;
}
.club-desc {
    font-size: 0.875rem; color: var(--text-secondary);
    line-height: 1.55; margin-bottom: 16px;
}
.club-stats {
    display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;
}
.stat-chip {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 8px 14px;
    font-size: 0.8rem; font-weight: 700;
    display: flex; align-items: center; gap: 6px;
    color: var(--text-primary);
}
.stat-chip .material-icons-round { font-size: 16px; color: var(--accent); }

.cta-card {
    background: linear-gradient(135deg, rgba(0,204,255,0.15) 0%, rgba(0,224,122,0.12) 100%);
    border: 1px solid rgba(0,204,255,0.25);
    border-radius: 20px;
    padding: 24px;
    text-align: center;
    position: relative;
    overflow: hidden;
}
/* Subtle glow strip at top */
.cta-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0,204,255,0.6), rgba(0,224,122,0.6), transparent);
}
.cta-card h3 { margin: 0 0 8px; font-size: 1.1rem; font-weight: 800; color: var(--text-primary); }
.cta-card p  { margin: 0 0 16px; font-size: 0.85rem; color: var(--text-secondary); }
.cta-btn {
    display: inline-block;
    background: linear-gradient(135deg, #00ccff, #00e07a);
    color: #04090e;
    border: none;
    padding: 10px 28px;
    border-radius: 12px;
    font-weight: 800;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s;
    box-shadow: 0 0 20px rgba(0,204,255,0.25);
}
.cta-btn:hover {
    box-shadow: 0 0 32px rgba(0,204,255,0.45);
    transform: translateY(-1px);
}

/* Ideals */
.ideals-list { display: flex; flex-direction: column; gap: 8px; }
.ideal-item {
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px 14px;
    font-size: 0.82rem;
    display: flex; align-items: flex-start; gap: 8px;
    cursor: default;
    font-weight: 700; display: block; margin-bottom: 2px; }
.ideal-item span { color: var(--text-secondary); line-height: 1.4; }

/* ── Center — Media Feed ──────────────────────────────────────────── */
.hub-feed {
    display: flex;
    flex-direction: column;
    gap: 0;
    min-width: 0;
}

.feed-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 12px;
    flex-wrap: wrap;
}
.feed-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1.4rem; font-weight: 800;
    display: flex; align-items: center; gap: 10px;
    margin: 0;
}
.feed-title .material-icons-round { color: var(--accent); }

/* ── MEDIA CARDS ────────────────────────────────────────────────────────── */
.media-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 32px;
    padding: 24px 0;
}

.media-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    display: flex;
    flex-direction: column;
    position: relative;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.media-card:hover {
    transform: translateY(-8px);
    border-color: rgba(0, 204, 255, 0.4);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    background: rgba(255, 255, 255, 0.05);
}

.media-card__image-container {
    position: relative;
    aspect-ratio: 1; /* Quadrado tipo Instagram */
    background: #000;
    cursor: pointer;
    overflow: hidden;
}

.media-card__thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.media-card:hover .media-card__thumb {
    transform: scale(1.08);
}

.media-card__gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, transparent 60%, rgba(0,0,0,0.8) 100%);
    pointer-events: none;
}

.media-card__top {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 5;
}

.media-type-badge {
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.05em;
}

.media-card__body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.media-card__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.media-card__author {
    display: flex;
    align-items: center;
    gap: 10px;
}

.media-card__author img {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--accent);
}

.media-card__author span {
    font-size: 0.85rem;
    font-weight: 700;
    color: #fff;
    opacity: 0.9;
}

.media-card__title {
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.4;
    color: rgba(255, 255, 255, 0.95);
    margin-bottom: 4px;
    cursor: pointer;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ── PREMIUM INTERACTION BAR ────────────────────────────────────────────── */
.social-interaction-bar {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.05);
}

.interaction-item {
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
    color: rgba(255, 255, 255, 0.7);
}

.interaction-item .material-icons-round {
    font-size: 24px;
    transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.interaction-item span:not(.material-icons-round) {
    font-size: 0.85rem;
    font-weight: 700;
    font-family: 'JetBrains Mono', monospace;
}

.interaction-item:hover {
    color: var(--accent-cyan);
}

.interaction-item:hover .material-icons-round {
    transform: scale(1.2);
}

.interaction-item.liked {
    color: #ff3e60;
}

.interaction-item.liked .material-icons-round {
    animation: heartPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

@keyframes heartPop {
    0% { transform: scale(1); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1); }
}

/* Hide old overlaid actions */
.media-card__actions { display: none !important; }

/* Load more */
.load-sentinel { height: 40px; }
.load-spinner {
    display: flex; align-items: center; justify-content: center;
    gap: 10px; padding: 24px; color: var(--text-secondary);
    font-size: 0.9rem;
}
.load-spinner .spin {
    width: 20px; height: 20px;
    border: 2px solid var(--border);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Empty feed */
.feed-empty {
    text-align: center;
    padding: 80px 24px;
    color: var(--text-secondary);
    border: 1px dashed var(--border);
    border-radius: 20px;
}
.feed-empty .material-icons-round { font-size: 3rem; opacity: 0.35; margin-bottom: 12px; display: block; }

/* ── Right sidebar — Events ────────────────────────────────────────── */
.hub-sidebar-right {
    position: sticky;
    top: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.sidebar-section {
    background: var(--bg-surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 20px;
}
.sidebar-title {
    font-family: 'Outfit', sans-serif;
    font-size: 1rem; font-weight: 800;
    margin: 0 0 16px;
    display: flex; align-items: center; gap: 8px;
}
.sidebar-title .material-icons-round { font-size: 20px; color: var(--accent); }

.event-item {
    display: flex; gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid var(--border);
}
.event-item:last-child { border-bottom: none; padding-bottom: 0; }
.event-item:first-child { padding-top: 0; }

.event-date-badge {
    flex-shrink: 0;
    width: 44px; height: 44px;
    background: var(--accent-glow, rgba(56,189,248,0.08));
    border: 1px solid var(--accent);
    border-radius: 12px;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    font-size: 0.65rem; font-weight: 800;
    color: var(--accent);
    text-transform: uppercase;
    line-height: 1.1;
}
.event-date-badge .day { font-size: 1.1rem; line-height: 1; }

.event-info h4 {
    margin: 0 0 4px; font-size: 0.85rem; font-weight: 700;
    color: var(--text-primary);
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.event-info p { margin: 0; font-size: 0.75rem; color: var(--text-secondary); }

.event-link {
    display: inline-flex; align-items: center; gap: 6px;
    margin-top: 12px;
    padding: 8px 16px;
    background: var(--accent-glow, rgba(56,189,248,0.08));
    border: 1px solid var(--accent);
    border-radius: 10px;
    color: var(--accent);
    font-size: 0.8rem; font-weight: 700;
    text-decoration: none;
    transition: all 0.2s;
    width: 100%;
    justify-content: center;
}
.event-link:hover {
    background: var(--accent);
    color: #04090e;
    box-shadow: 0 0 16px rgba(0,204,255,0.3);
}
.event-link .material-icons-round { font-size: 16px; }

/* Ticker (achievements) */
.ticker-wrap {
    overflow: hidden;
    border-radius: 12px;
    background: var(--bg-elevated);
    border: 1px solid var(--border);
    padding: 10px 0;
}
.ticker-track {
    display: flex;
    gap: 24px;
    animation: ticker 20s linear infinite;
    white-space: nowrap;
    width: max-content;
}
.ticker-track:hover { animation-play-state: paused; }
@keyframes ticker {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-50%); }
}
.ticker-item {
    display: inline-flex; align-items: center; gap: 8px;
    font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);
}
.ticker-item .material-icons-round { font-size: 16px; color: #00e07a; }

/* ── MEDIA VIEWER ────────────────────────────────────────────────────────── */
.media-viewer {
    position: fixed;
    inset: 0;
    background: rgba(4, 6, 12, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    z-index: 9000;
    display: none;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.media-viewer.open {
    display: flex;
    opacity: 1;
}

.viewer-inner {
    width: 95%;
    max-width: 1200px;
    height: 90vh;
    display: flex;
    background: rgba(13, 17, 23, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 32px;
    overflow: hidden;
    position: relative;
    box-shadow: 0 40px 100px rgba(0, 0, 0, 0.8);
}

.viewer-media {
    flex: 1.6;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.viewer-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #0d1117;
    border-left: 1px solid rgba(255, 255, 255, 0.08);
}

.viewer-panel-header {
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.viewer-panel-header h3 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.viewer-actions {
    padding: 20px 24px;
    display: flex;
    align-items: center;
    gap: 24px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.viewer-action-btn {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.7);
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: 700;
    font-size: 0.95rem;
    transition: all 0.2s;
}

.viewer-action-btn .material-icons-round {
    font-size: 28px;
}

.viewer-action-btn:hover {
    color: var(--accent-cyan);
    transform: scale(1.05);
}

.viewer-action-btn.liked {
    color: #ff3e60;
}

/* Shield: transparent fixed zone in the top-right corner.
   Sits above iframes (z-index 99998) so stray touches near the close button
   are absorbed here instead of by the iframe content. */
#viewerCloseShield {
    position: fixed;
    top: 0; right: 0;
    width: 90px; height: 90px;
    z-index: 99998;
    display: none;
    pointer-events: auto;
}

.viewer-float-close {
    position: fixed;
    top: 16px;
    right: 16px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.35);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 99999;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    transition: background 0.2s, transform 0.2s;
    /* Expand tap target without changing visual size */
    padding: 12px;
    margin: -12px;
}

.viewer-float-close:hover,
.viewer-float-close:active {
    background: rgba(239, 68, 68, 0.85);
    transform: rotate(90deg);
}

.viewer-float-close .material-icons-round { font-size: 24px; }

.viewer-inner {
    display: flex;
    gap: 0;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
}

/* Media pane */
.viewer-media {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    min-width: 0;
    position: relative;
}

/* Comment panel — hardcoded dark, never inherits theme vars */
.viewer-panel {
    width: 340px;
    flex-shrink: 0;
    background: #0d1017;
    border-left: 1px solid rgba(0,204,255,0.14);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}
.viewer-panel-header {
    padding: 14px 18px;
    border-bottom: 1px solid rgba(0,204,255,0.12);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #080c12;
    flex-shrink: 0;
}
.viewer-panel-header h3 {
    margin: 0; font-size: 0.95rem; font-weight: 700; color: #e8f4ff;
    display: flex; align-items: center; gap: 8px;
}
.viewer-panel-header h3 .material-icons-round { font-size: 18px; color: #00ccff; }

.viewer-close-btn {
    background: rgba(0,204,255,0.1);
    border: 1px solid rgba(0,204,255,0.35);
    color: #e8f4ff;
    border-radius: 50%;
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s, border-color 0.2s;
}
.viewer-close-btn:hover {
    background: rgba(0,204,255,0.22);
    border-color: rgba(0,204,255,0.6);
}
.viewer-close-btn .material-icons-round { font-size: 18px; }

/* Back button — hidden on desktop, shown on mobile via @media below */
.viewer-panel-back {
    display: none;
    background: none;
    border: none;
    color: #e8f4ff;
    cursor: pointer;
    padding: 4px;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 50%;
    transition: background 0.2s;
}
.viewer-panel-back:hover { background: rgba(255,255,255,0.08); }
.viewer-panel-back .material-icons-round { font-size: 22px; }

/* Viewer actions row */
.viewer-actions {
    padding: 12px 18px;
    border-bottom: 1px solid rgba(0,204,255,0.1);
    display: flex;
    gap: 10px;
    align-items: center;
    flex-shrink: 0;
    background: #080c12;
}
.viewer-action-btn {
    display: flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    color: rgba(232,244,255,0.75);
    border-radius: 10px;
    padding: 7px 14px;
    cursor: pointer;
    font-size: 0.82rem; font-weight: 600;
    font-family: inherit;
    transition: all 0.2s;
}
.viewer-action-btn .material-icons-round { font-size: 17px; }
.viewer-action-btn:hover {
    background: rgba(0,204,255,0.1);
    border-color: rgba(0,204,255,0.45);
    color: #00ccff;
}
.viewer-action-btn.liked {
    color: #00e07a;
    border-color: rgba(0,224,122,0.45);
    background: rgba(0,224,122,0.08);
}
.viewer-action-btn.liked .material-icons-round { color: #00e07a; }

/* Comment list */
.comment-list {
    flex: 1;
    overflow-y: auto;
    padding: 14px 18px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 0;
}
.comment-list::-webkit-scrollbar { width: 3px; }
.comment-list::-webkit-scrollbar-thumb { background: rgba(0,204,255,0.2); border-radius: 2px; }

.comment-bubble {
    background: rgba(0,204,255,0.04);
    border: 1px solid rgba(0,204,255,0.1);
    border-radius: 12px;
    padding: 10px 13px;
}
.comment-bubble strong {
    display: block; font-size: 0.78rem; font-weight: 700;
    color: #00ccff; margin-bottom: 4px;
}
.comment-bubble p {
    margin: 0; font-size: 0.84rem; color: rgba(232,244,255,0.82); line-height: 1.45;
}
.comment-bubble time {
    font-size: 0.7rem; color: rgba(232,244,255,0.3);
    margin-top: 4px; display: block;
}

.comment-empty {
    text-align: center; padding: 32px 20px;
    color: rgba(232,244,255,0.3); font-size: 0.84rem;
}
.comment-empty .material-icons-round {
    font-size: 2rem; display: block; margin-bottom: 8px;
    color: rgba(0,204,255,0.3);
}

/* Comment form — hardcoded dark */
.comment-form {
    padding: 14px 18px;
    border-top: 1px solid rgba(0,204,255,0.12);
    background: #080c12;
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex-shrink: 0;
}
.comment-form input,
.comment-form textarea {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    padding: 9px 13px;
    color: #e8f4ff;
    font-size: 0.84rem;
    font-family: inherit;
    width: 100%;
    resize: none;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.comment-form input:focus,
.comment-form textarea:focus {
    border-color: #00ccff;
    box-shadow: 0 0 0 2px rgba(0,204,255,0.12);
}
.comment-form input::placeholder,
.comment-form textarea::placeholder { color: rgba(232,244,255,0.28); }
.comment-submit-btn {
    background: linear-gradient(135deg, #00ccff 0%, #00e07a 100%);
    color: #000;
    border: none;
    border-radius: 10px;
    padding: 10px 16px;
    font-weight: 800;
    font-size: 0.84rem;
    font-family: inherit;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: opacity 0.2s, transform 0.15s;
}
.comment-submit-btn:hover { opacity: 0.88; transform: translateY(-1px); }
.comment-submit-btn:active { transform: translateY(0); }
.comment-submit-btn .material-icons-round { font-size: 17px; }
.honeypot { display: none !important; }

/* Placeholder para mídias sem thumbnail (Instagram, TikTok) */
.media-card__image-container { position: relative; overflow: hidden; }
.media-card__thumb-placeholder {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #0d1020 0%, #0a1a14 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.media-card__thumb-placeholder::after {
    content: 'play_circle';
    font-family: 'Material Icons Round';
    font-size: 3.5rem;
    color: rgba(0, 204, 255, 0.4);
    pointer-events: none;
}
/* ── Avatar blur fallback (when no thumbnail available) ─────────── */
.thumb-fallback {
    position: absolute; inset: 0; width: 100%; height: 100%;
    overflow: hidden; cursor: pointer;
}
.thumb-fallback__bg {
    position: absolute; inset: -20px;
    width: calc(100% + 40px); height: calc(100% + 40px);
    background-size: cover; background-position: center;
    filter: blur(18px) brightness(0.45) saturate(1.3);
    transform: scale(1.1);
}
.thumb-fallback__bg--gradient {
    position: absolute; inset: 0;
    background: linear-gradient(145deg, #0a0f1a 0%, #0d1a2a 40%, #0a1a14 100%);
}
.thumb-fallback__avatar {
    position: relative; z-index: 1;
    width: 56px; height: 56px; border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(0, 204, 255, 0.5);
    box-shadow: 0 0 20px rgba(0, 204, 255, 0.2);
}
.thumb-fallback__play {
    position: relative; z-index: 1;
    font-family: 'Material Icons Round'; font-size: 3rem;
    color: rgba(0, 204, 255, 0.7);
    text-shadow: 0 0 24px rgba(0, 204, 255, 0.3);
}
.thumb-fallback__name {
    position: relative; z-index: 1;
    font-size: 0.65rem; opacity: 0.6; color: #fff;
    text-transform: uppercase; letter-spacing: 0.8px; margin-top: 8px;
    max-width: 80%; text-align: center;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
/* Glow ring when avatar is present */
.thumb-fallback__avatar + .thumb-fallback__play { margin-top: 6px; font-size: 2.2rem; }

/* Specific fix for Instagram/Social auto-sizing */
.social-embed {
    width: 100%;
    max-width: 500px;
    min-height: 450px;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    overflow: hidden;
    margin: 0 auto;
}
.social-embed iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    border: none;
}

/* Mobile viewer: vídeo full-screen + barra de ações flutuante */
@media (max-width: 768px) {
    .media-viewer { background: #000; }
    .viewer-inner {
        flex-direction: column;
        /* height:100% preenche o .media-viewer (position:fixed;inset:0) sem conflito
           com safe-areas de notch/home-indicator nem com o dvh do browser mobile */
        height: 100%;
        width: 100%; max-width: 100%;
        margin: 0;
        overflow: hidden;
        position: relative;
        align-items: stretch;
        /* Espaço seguro para notch (topo) e home indicator (rodapé) */
        padding-top: env(safe-area-inset-top);
        padding-bottom: env(safe-area-inset-bottom);
        box-sizing: border-box;
    }
    /* Vídeo ocupa apenas a área acima do painel (flex:1 + min-height:0).
       O iframe NUNCA se estende para a área do painel. */
    .viewer-media {
        flex: 1;
        min-height: 0;
        padding: 0;
        background: #000;
        display: flex;
        align-items: stretch;
        justify-content: center;
        overflow: hidden;
        /* Isola o contexto de empilhamento: iframe fica contido aqui */
        isolation: isolate;
    }
    /* Landscape (YouTube): mantém proporção 16:9, centraliza verticalmente */
    .viewer-media .embed-wrap.landscape {
        width: 100% !important;
        aspect-ratio: 16/9 !important;
        border-radius: 0 !important;
        flex-shrink: 0;
        align-self: center;
    }
    /* Portrait (TikTok, Shorts): preenche toda a altura disponível */
    .viewer-media .embed-wrap.portrait {
        width: 100% !important;
        max-width: 100% !important;
        aspect-ratio: unset !important;
        height: 100% !important;
        border-radius: 0 !important;
        flex-shrink: 0;
    }
    /* Imagens */
    .viewer-media img {
        max-height: 100% !important;
        max-width: 100% !important;
        width: auto !important;
        border-radius: 0 !important;
        object-fit: contain;
        align-self: center;
    }
    /* Social embeds (Instagram, Twitter): preenche altura, scroll interno */
    .viewer-media .embed-wrap.social-embed-wrap {
        width: 100% !important;
        max-width: 100% !important;
        height: 100% !important;
        min-height: unset;
        overflow-y: auto;
        overflow-x: hidden;
        padding-bottom: 0;
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }
    /* Painel: item flex abaixo do vídeo — SEM position:absolute.
       O iframe ocupa apenas a área de .viewer-media (acima); a área do painel
       não tem iframe por baixo, então toques nos botões nunca são capturados.
       flex: 0 0 auto cancela o flex-grow:1 herdado do CSS base (que causava
       o painel a ocupar metade da tela). */
    .viewer-panel {
        position: relative;
        flex: 0 0 auto;
        width: 100%;
        border-left: none;
        border-top: 1px solid rgba(0, 204, 255, 0.2);
        background: #0a0e18;
        flex-direction: column;
    }
    /* No modo barra: ocultar cabeçalho, lista de comentários e form */
    .viewer-panel-header { display: none; }
    .comment-list, .comment-form { display: none; }
    /* Modo showing-comments: painel vira overlay full-screen */
    .media-viewer.showing-comments .viewer-panel {
        position: fixed;
        inset: 0;
        height: 100dvh;
        background: #0d1117;
        border-top: none;
    }
    /* Modes */
    .media-viewer.showing-comments .viewer-media { filter: blur(10px); }
    @media (max-width: 768px) {
        .media-viewer.showing-comments .viewer-media { display: none; }
    }
    .media-viewer.showing-comments .viewer-panel-header { display: flex; }
    .media-viewer.showing-comments .comment-list {
        display: flex;
        flex-direction: column;
        flex: 1;
        overflow-y: auto;
    }
    .media-viewer.showing-comments .comment-form { display: block; }
    .media-viewer.showing-comments .viewer-media { display: none; }
    .viewer-close-btn { display: none; }
    .viewer-panel-back { display: flex; }
}
@media (min-width: 769px) {
    .viewer-close-btn { display: flex; }
    .viewer-panel-back { display: none; }
}

/* TikTok — gate estático até o usuário tocar (sem iframe cross-origin antes disso).
   Após o toque, iframe criado com sandbox que bloqueia window.top/window.open
   → TikTok não consegue redirecionar para app ou site externo. */
.tt-wrap { position: relative; }
.tt-gate {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    cursor: pointer;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    overflow: hidden;
    z-index: 2;
}
.tt-gate__bg {
    position: absolute;
    inset: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    opacity: 0.55;
}
.tt-gate__btn {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px 32px;
    background: rgba(0,0,0,0.68);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 22px;
    color: #fff;
    pointer-events: none;
    text-align: center;
}
.tt-gate__btn .material-icons-round { font-size: 2.8rem; }
.tt-gate__btn span:last-child {
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    line-height: 1.3;
}

/* Embed containers */
.embed-wrap { position: relative; border-radius: 16px; overflow: hidden; }
.embed-wrap.landscape { width: 100%; aspect-ratio: 16/9; }
.embed-wrap.portrait  { aspect-ratio: 9/16; height: min(80vh, 600px); width: auto; margin: 0 auto; }
.embed-wrap iframe    { position: absolute; inset: 0; width: 100%; height: 100%; border: none; }
.embed-wrap.twitter-embed { aspect-ratio: unset; overflow: visible; width: min(550px, 100%); margin: 0 auto; box-shadow: none; background: transparent; border-radius: 0; }
.embed-wrap.twitter-embed .twitter-tweet { margin: 0 auto !important; }
/* Social embeds (Instagram, TikTok) — dimensionadas pelo SDK da plataforma */
/* align-self:stretch faz o wrapper preencher a altura do .viewer-media no desktop,
   garantindo que o iframe absoluto (height:100%) tenha um pai com altura definida */
.embed-wrap.social-embed-wrap { aspect-ratio: unset; overflow-y: auto; width: 100%; max-width: 605px; margin: 0 auto; box-shadow: none; background: transparent; border-radius: 0; display: flex; justify-content: center; align-self: stretch; min-height: 400px; }

/* ── Mobile full-screen Reels ─────────────────────────────────────── */
@media (max-width: 768px) {
    .hub-shell { display: block; padding: 0; }
    .hub-sidebar-left, .hub-sidebar-right { display: none; }

    /* Hero banner (mobile only) */
    .mobile-hero {
        position: relative;
        padding: 80px 20px 24px;
        text-align: center;
        background: linear-gradient(to bottom, var(--bg-base, #0a0f1a) 0%, transparent 100%);
        border-bottom: 1px solid var(--border);
    }
    .mobile-hero .club-avatar  { margin-bottom: 12px; }
    .mobile-hero .club-name    { font-size: 1.4rem; margin-bottom: 4px; }
    .mobile-hero .club-motto   { margin-bottom: 8px; }
    .mobile-hero .club-stats   { justify-content: center; }

    /* Feed — padding lateral consistente em todo o conteúdo */
    .hub-feed {
        position: relative;
        padding: 0 16px;
    }
    /* story-bar precisa scrollar de borda a borda, então compensa o padding do pai */
    .story-bar {
        margin-left: -16px;
        margin-right: -16px;
        padding-left: 16px;
        padding-right: 16px;
    }
    .feed-toolbar { display: none; }

    /* Feed layout - simplified for modern look */
    .media-grid {
        display: flex;
        flex-direction: column;
        gap: 32px;
        padding: 16px 0;
        height: auto;
        overflow-y: visible;
        scroll-snap-type: none;
    }

    .media-card {
        height: auto;
        border-bottom: 1px solid var(--border);
        padding-bottom: 16px;
        touch-action: manipulation;
    }

    .media-card__image-container {
        border-radius: 12px;
        overflow: hidden;
        aspect-ratio: 1;
        background: #000;
        position: relative;
        touch-action: manipulation;
        cursor: pointer;
    }

    .media-card__thumb {
        height: 100%;
        object-fit: contain;
    }

    /* Hide the old floating actions */
    .media-card__actions { display: none !important; }

    /* Bigger bottom info on mobile */
    .media-card__info {
        padding: 16px 72px 24px 16px;
    }
    .media-card__title { font-size: 1rem; margin-bottom: 6px; }
    .media-card__author { font-size: 0.8rem; }

    /* Card action buttons bigger on mobile */
    .card-action-btn { width: 44px; height: 44px; }
    .card-action-btn .material-icons-round { font-size: 22px; }
    .card-action-count { font-size: 0.75rem; color: white; text-align: center; margin-top: -4px; }
}

/* Desktop: hide mobile hero */
@media (min-width: 769px) {
    .mobile-hero { display: none; }
    /* 2-col feed for mid-size screens */
    @media (max-width: 1100px) {
        .hub-shell { grid-template-columns: 260px 1fr; }
        .hub-sidebar-right { display: none; }
    }
}

/* ── Double-tap heart animation ───────────────────────────────────── */
@keyframes heartPop {
    0%   { transform: translate(-50%,-50%) scale(0); opacity: 1; }
    60%  { transform: translate(-50%,-50%) scale(1.4); opacity: 1; }
    100% { transform: translate(-50%,-50%) scale(1.8); opacity: 0; }
}
.heart-pop {
    position: absolute;
    pointer-events: none;
    z-index: 100;
    font-size: 5rem;
    animation: heartPop 0.7s ease forwards;
}

/* ── Story Bubbles ──────────────────────────────────────────────────────────── */
.story-bar {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    padding: 6px 4px 16px;
    margin-bottom: 8px;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.story-bar::-webkit-scrollbar { display: none; }

.story-bubble {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 0.18s;
    -webkit-tap-highlight-color: transparent;
    background: none;
    border: none;
    padding: 0;
}
.story-bubble:hover  { transform: scale(1.07); }
.story-bubble:active { transform: scale(0.96); }

/* Anel inativo: borda sólida definida em vez de fundo semi-transparente imperceptível */
.story-ring {
    position: relative;          /* contexto para o badge */
    width: 60px;
    height: 60px;
    border-radius: 50%;
    padding: 3px;
    background: rgba(255,255,255,0.0); /* sem fill — só borda via box-shadow */
    box-shadow: 0 0 0 2px rgba(255,255,255,0.22);
    transition: box-shadow 0.25s, background 0.25s;
}
/* Anel ativo: gradiente colorido */
.story-bubble.active .story-ring {
    box-shadow: none;
    background: linear-gradient(135deg, #00ccff 0%, #f43f5e 55%, #f97316 100%);
}
/* "Todos" sempre com gradiente suave */
.story-bubble-all .story-ring {
    box-shadow: none;
    background: linear-gradient(135deg, #00ccff 0%, #7c3aed 100%);
}

.story-ring img,
.story-ring .story-initials {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    /* gap visual entre anel e avatar */
    outline: 2.5px solid #07090f;
    display: block;
}
.story-initials {
    background: linear-gradient(135deg, #1a2a4a 0%, #0d1a2e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 800;
    color: #e8f4ff;
    text-transform: uppercase;
    outline: 2.5px solid #07090f;
}
/* Ativo: iniciais ficam mais brilhantes */
.story-bubble.active .story-initials,
.story-bubble-all .story-initials {
    background: rgba(0,204,255,0.18);
    color: #00ccff;
}

.story-name {
    font-size: 0.68rem;
    font-weight: 600;
    color: rgba(232,244,255,0.72);
    max-width: 64px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    letter-spacing: 0.01em;
}
.story-bubble.active .story-name {
    color: #e8f4ff;
}

/* ── Badge de contagem ─────────────────────────────────────────────────────── */
.story-bubble-badge {
    position: absolute;
    top: -1px;
    right: -1px;
    background: #00ccff;
    color: #000;
    font-size: 0.58rem;
    font-weight: 900;
    min-width: 17px;
    height: 17px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 4px;
    line-height: 1;
    border: 2px solid #07090f;
    pointer-events: none;
    z-index: 1;
}

/* ── Type Filter Bar ────────────────────────────────────────────────────────── */
.type-filter-bar {
    display: flex;
    gap: 7px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}
.type-filter-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 20px;
    /* contraste suficiente para leitura sem ser pesado visualmente */
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.07);
    color: rgba(232,244,255,0.75);
    font-size: 0.78rem;
    font-weight: 600;
    cursor: pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s;
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
}
.type-filter-btn:hover {
    border-color: rgba(0,204,255,0.45);
    color: #e8f4ff;
    background: rgba(0,204,255,0.08);
}
.type-filter-btn.active {
    background: rgba(0,204,255,0.16);
    border-color: #00ccff;
    color: #00ccff;
    font-weight: 700;
}
.type-filter-btn .material-icons-round { font-size: 14px; opacity: 0.85; }
.type-filter-btn.active .material-icons-round { opacity: 1; }

.type-filter-btn-count {
    background: rgba(255,255,255,0.14);
    border-radius: 10px;
    padding: 1px 6px;
    font-size: 0.7rem;
    font-weight: 700;
    margin-left: 1px;
    color: inherit;
}
.type-filter-btn.active .type-filter-btn-count {
    background: rgba(0,204,255,0.22);
}

/* ── Viewer Author Strip ─────────────────────────────────────────────────────── */
.viewer-author-strip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 18px;
    border-bottom: 1px solid rgba(0,204,255,0.08);
    background: rgba(0,0,0,0.2);
    flex-shrink: 0;
}
.viewer-author-strip img {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid rgba(0,204,255,0.3);
    flex-shrink: 0;
}
.viewer-author-strip-initials {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: rgba(0,204,255,0.15);
    border: 1px solid rgba(0,204,255,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    color: #00ccff;
    flex-shrink: 0;
}
.viewer-author-strip-info { flex: 1; min-width: 0; }
.viewer-author-strip-name {
    font-size: 0.85rem;
    font-weight: 700;
    color: #e8f4ff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.viewer-author-strip-posts {
    font-size: 0.7rem;
    color: rgba(255,255,255,0.4);
    margin-top: 1px;
}

/* ── Reaction Picker ─────────────────────────────────────────────────────────── */
.reaction-bar {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    align-items: center;
}
.reaction-summary {
    display: flex;
    gap: 2px;
    font-size: 0.78rem;
    align-items: center;
    color: rgba(255,255,255,0.55);
}
.reaction-summary span { cursor: default; }

.reaction-picker-wrap { position: relative; display: inline-flex; }
.reaction-picker {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%) scale(0.85);
    display: flex;
    gap: 4px;
    background: #1a1f2e;
    border: 1px solid rgba(0,204,255,0.2);
    border-radius: 30px;
    padding: 6px 10px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.6);
    opacity: 0;
    pointer-events: none;
    transition: all 0.18s cubic-bezier(0.175,0.885,0.32,1.275);
    z-index: 50;
    white-space: nowrap;
}
.reaction-picker.open {
    opacity: 1;
    pointer-events: auto;
    transform: translateX(-50%) scale(1);
}
.reaction-btn {
    font-size: 1.4rem;
    line-height: 1;
    cursor: pointer;
    border: none;
    background: none;
    padding: 2px 4px;
    border-radius: 8px;
    transition: transform 0.12s;
    display: flex;
    align-items: center;
    justify-content: center;
}
.reaction-btn:hover { transform: scale(1.35); }
.reaction-btn.my-reaction {
    background: rgba(0,204,255,0.15);
    transform: scale(1.15);
}

/* ── Event Albums bar (23snaps style) ────────────────────────────────────────── */
.event-albums-bar {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 4px 0 16px;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.event-albums-bar::-webkit-scrollbar { display: none; }
.event-album-card {
    flex-shrink: 0;
    width: 140px;
    border-radius: 16px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid rgba(255,255,255,0.08);
    transition: border-color 0.2s, transform 0.2s;
    position: relative;
    background: #0d1017;
}
.event-album-card:hover, .event-album-card.active {
    border-color: rgba(0,204,255,0.6);
    transform: translateY(-3px);
}
.event-album-thumb {
    width: 100%;
    aspect-ratio: 1;
    object-fit: cover;
    display: block;
}
.event-album-thumb-placeholder {
    width: 100%;
    aspect-ratio: 1;
    background: linear-gradient(135deg, #0d1020, #0a1a14);
    display: flex;
    align-items: center;
    justify-content: center;
}
.event-album-thumb-placeholder .material-icons-round {
    font-size: 2rem;
    color: rgba(0,204,255,0.3);
}
.event-album-info {
    padding: 8px 10px 10px;
}
.event-album-title {
    font-size: 0.72rem;
    font-weight: 700;
    color: #e8f4ff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.event-album-count {
    font-size: 0.65rem;
    color: rgba(255,255,255,0.4);
    margin-top: 2px;
}

/* ── TikTok Overlay ──────────────────────────────────────────────────────────── */
#tikTokOverlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 300;
    background: #000;
    overflow-y: scroll;
    scroll-snap-type: y mandatory;
    overscroll-behavior: contain;
}
#tikTokOverlay.open { display: block; }
.tt-card {
    height: 100svh;
    width: 100vw;
    scroll-snap-align: start;
    position: relative;
    overflow: hidden;
    background: #000;
    cursor: pointer;
}
.tt-card__bg {
    position: absolute;
    inset: 0;
    background: #0d1017;
}
.tt-card__thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    opacity: 0.85;
}
.tt-card__gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 55%);
    pointer-events: none;
}
.tt-card__info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 72px;
    padding: 20px 16px 48px;
}
.tt-card__title {
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin: 0 0 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.tt-card__author {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
    color: rgba(255,255,255,0.7);
}
.tt-card__author img {
    width: 24px; height: 24px;
    border-radius: 50%; object-fit: cover;
    border: 1px solid rgba(255,255,255,0.3);
}
.tt-card__side {
    position: absolute;
    right: 12px;
    bottom: 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}
.tt-side-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    background: none;
    border: none;
    color: #fff;
    cursor: pointer;
    font-size: 0.65rem;
    text-shadow: 0 1px 4px rgba(0,0,0,0.8);
}
.tt-side-btn .material-icons-round {
    font-size: 28px;
    filter: drop-shadow(0 1px 4px rgba(0,0,0,0.6));
}
.tt-side-btn.liked .material-icons-round { color: #f43f5e; }
#tikTokClose {
    position: fixed;
    top: 16px;
    right: 16px;
    z-index: 301;
    background: rgba(0,0,0,0.55);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 50%;
    width: 42px;
    height: 42px;
    display: none;
    align-items: center;
    justify-content: center;
    color: #fff;
    cursor: pointer;
    backdrop-filter: blur(8px);
}
#tikTokOverlay.open ~ #tikTokClose,
#tikTokClose.open { display: flex; }
.tt-type-badge {
    position: absolute;
    top: 16px;
    left: 16px;
    background: rgba(0,0,0,0.5);
    border-radius: 8px;
    padding: 3px 8px;
    font-size: 0.7rem;
    color: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    gap: 4px;
    backdrop-filter: blur(4px);
}
.tt-type-badge .material-icons-round { font-size: 13px; }

/* ── PWA Install Banner ───────────────────────────────────────────────────────── */
#pwaInstallBanner {
    position: fixed;
    bottom: 20px;
    left: 50%;
    /* calc(100% + 40px): move além da própria altura + espaço de sobra,
       garantindo que desapareça completamente independente do tamanho do banner */
    transform: translateX(-50%) translateY(calc(100% + 40px));
    background: #1a1f2e;
    border: 1px solid rgba(0,204,255,0.3);
    border-radius: 20px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    z-index: 400;
    transition: transform 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
    max-width: 340px;
    width: calc(100vw - 40px);
}
#pwaInstallBanner.visible { transform: translateX(-50%) translateY(0); }
#pwaInstallBanner .material-icons-round { color: #00ccff; font-size: 28px; flex-shrink: 0; }
.pwa-banner-text { flex: 1; min-width: 0; }
.pwa-banner-text strong { display: block; font-size: 0.9rem; color: #e8f4ff; }
.pwa-banner-text span { font-size: 0.75rem; color: rgba(255,255,255,0.5); }
.pwa-install-btn {
    background: #00ccff;
    color: #000;
    border: none;
    border-radius: 12px;
    padding: 7px 14px;
    font-weight: 700;
    font-size: 0.8rem;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
}
.pwa-dismiss-btn {
    background: none;
    border: none;
    color: rgba(255,255,255,0.4);
    cursor: pointer;
    padding: 4px;
    flex-shrink: 0;
}

/* ── Feed Mode Toggle ───────────────────────────────────────────────────────── */
.feed-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
}
.view-toggle-group {
    display: flex;
    gap: 2px;
    background: rgba(255,255,255,0.05);
    border-radius: 12px;
    padding: 3px;
    border: 1px solid rgba(255,255,255,0.1);
}
/* Separador visual entre os dois grupos */
.view-toggle-group + .view-toggle-group {
    border-left: 1px solid rgba(255,255,255,0.1);
    margin-left: 4px;
    padding-left: 6px;
}
.view-toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 6px 10px;
    border-radius: 9px;
    border: none;
    background: transparent;
    color: rgba(232,244,255,0.45);
    cursor: pointer;
    transition: color 0.15s, background 0.15s;
    -webkit-tap-highlight-color: transparent;
}
.view-toggle-btn:hover { color: rgba(232,244,255,0.85); background: rgba(255,255,255,0.07); }
.view-toggle-btn.active {
    background: #00ccff;
    color: #000;
}
.view-toggle-btn .material-icons-round { font-size: 17px; }

/* ── Feed Mode — vertical card list ────────────────────────────────────────── */
.media-grid.feed-mode {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.media-grid.feed-mode .media-card {
    width: 100%;
    max-width: 560px;
    margin: 0 auto;
    display: flex;
    flex-direction: row;
    height: auto;
    min-height: 0;
    border-radius: 20px;
    scroll-snap-align: start;
}
.media-grid.feed-mode .media-card__image-container {
    flex: 0 0 160px;
    width: 160px;
    height: auto;
    aspect-ratio: 9/16;
    border-radius: 16px 0 0 16px;
    overflow: hidden;
    cursor: pointer;
}
.media-grid.feed-mode .media-card__body {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 14px;
    min-width: 0;
}

/* ── Viewer Navigation Arrows ───────────────────────────────────────────────── */
/* Nav row — lives inside .viewer-panel, never overlaps the iframe */
.viewer-nav-row {
    display: none; /* shown via .visible class when viewer opens */
    align-items: center;
    justify-content: space-between;
    padding: 6px 14px;
    border-bottom: 1px solid rgba(0,204,255,0.1);
    background: #080c12;
    flex-shrink: 0;
    gap: 8px;
}
.viewer-nav-row.visible { display: flex; }
.viewer-nav-counter {
    font-size: 0.78rem;
    color: rgba(232,244,255,0.4);
    font-variant-numeric: tabular-nums;
    flex: 1;
    text-align: center;
    letter-spacing: 0.03em;
}
.viewer-nav-btn {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(0,204,255,0.1);
    border: 1px solid rgba(0,204,255,0.28);
    color: #e8f4ff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: background 0.2s, border-color 0.2s;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
}
.viewer-nav-btn:hover  { background: rgba(0,204,255,0.22); border-color: rgba(0,204,255,0.6); }
.viewer-nav-btn:disabled { opacity: 0.2; pointer-events: none; }
.viewer-nav-btn .material-icons-round { font-size: 20px; }

/* ── Author filter — hidden cards ───────────────────────────────────────────── */
.media-card.filtered-out {
    display: none !important;
}
</style>

<?php
// ── Helper: build thumbnail for a media item ─────────────────────────────────
function buildThumb(array $media): string {
    $url   = trim((string)($media['media_content'] ?? ''));
    $thumb = $media['thumbnail_url'] ?? '';

    // Filter out platform favicons (not real thumbnails)
    if (!empty($thumb) && strpos($thumb, 'favicon') !== false && strpos($thumb, 'google.com/s2/favicons') !== false) {
        $thumb = '';
    }

    // Local cached thumbnail (e.g. "uploads/thumbnails/thumb_42.jpg")
    if (!empty($thumb) && strpos($thumb, 'uploads/thumbnails/') !== false) {
        return base_url('/' . $thumb);
    }

    if (!empty($thumb)) return $thumb;

    // YouTube — direct URL (never expires)
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $m)) {
        return 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
    }
    // Direct image URL
    if (preg_match('/\.(jpg|jpeg|png|webp|gif|avif|svg)/i', $url)) {
        return strpos($url, 'storage/') === 0 ? base_url('/' . $url) : $url;
    }

    // No thumbnail available — return empty (avatar blur fallback handled in HTML)
    return '';
}

function isVideoUrl(string $url): bool {
    return (bool)preg_match('/youtube\.com|youtu\.be|tiktok\.com|instagram\.com|\.mp4|\.webm|\.mov/i', $url);
}

function mediaTypeBadge(array $media): string {
    $url = trim((string)($media['media_content'] ?? ''));
    if (preg_match('/youtube\.com\/shorts/i', $url)) return 'Shorts';
    if (preg_match('/youtube\.com|youtu\.be/i', $url)) return 'YouTube';
    if (preg_match('/tiktok\.com/i', $url)) return 'TikTok';
    if (preg_match('/instagram\.com/i', $url)) return 'Reels';
    if (preg_match('/\.(mp4|webm|mov)/i', $url)) return 'Vídeo';
    if (preg_match('/\.(jpg|jpeg|png|webp|gif)/i', $url)) return 'Foto';
    if (!empty($media['thumbnail_url'])) return 'Foto';
    return 'Mídia';
}

function mediaBadgeIcon(array $media): string {
    $badge = mediaTypeBadge($media);
    $icons = [
        'Shorts'  => 'play_circle',
        'YouTube' => 'smart_display',
        'TikTok'  => 'music_video',
        'Reels'   => 'photo_camera',
        'Vídeo'   => 'videocam',
        'Foto'    => 'image',
    ];
    return $icons[$badge] ?? 'perm_media';
}

function getMediaTypeKey(array $media): string {
    $url = trim((string)($media['media_content'] ?? ''));
    if (preg_match('/youtube\.com\/shorts/i', $url)) return 'youtube';
    if (preg_match('/youtube\.com|youtu\.be/i', $url)) return 'youtube';
    if (preg_match('/tiktok\.com/i', $url)) return 'tiktok';
    if (preg_match('/instagram\.com/i', $url)) return 'reels';
    if (preg_match('/\.(mp4|webm|mov)/i', $url)) return 'video';
    if (preg_match('/\.(jpg|jpeg|png|webp|gif)/i', $url)) return 'photo';
    if (!empty($media['thumbnail_url'])) return 'photo';
    return 'other';
}
?>

<?php
// ── Conversion Hero ─────────────────────────────────────────────────────────
$heroHeadline    = trim($profile['hero_headline']    ?? '');
$heroSubheadline = trim($profile['hero_subheadline'] ?? '');
$heroBannerType  = $profile['hero_banner_type']  ?? 'image';
$heroBannerUrl   = trim($profile['hero_banner_url']  ?? '');

// Extract YouTube video ID
$heroYtId = '';
if ($heroBannerType === 'youtube' && $heroBannerUrl) {
    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $heroBannerUrl, $m)) {
        $heroYtId = $m[1];
    }
}

$showHero = !empty($heroHeadline);
?>

<?php if ($showHero): ?>
<section class="conversion-hero" id="conversionHero">

    <?php if ($heroBannerType === 'youtube' && $heroYtId): ?>
    <!-- YouTube muted loop background -->
    <div class="hero-yt-wrap" id="heroYtWrap" aria-hidden="true">
        <iframe id="heroYtFrame"
            src="https://www.youtube.com/embed/<?= htmlspecialchars($heroYtId) ?>?autoplay=1&mute=1&loop=1&playlist=<?= htmlspecialchars($heroYtId) ?>&controls=0&playsinline=1&rel=0&showinfo=0&modestbranding=1&disablekb=1&iv_load_policy=3"
            allow="autoplay; encrypted-media"
            allowfullscreen
            loading="lazy"></iframe>
    </div>

    <?php elseif ($heroBannerType === 'image' && $heroBannerUrl): ?>
    <!-- Image background -->
    <div class="hero-img-bg" style="background-image: url('<?= htmlspecialchars($heroBannerUrl) ?>');" aria-hidden="true"></div>

    <?php else: ?>
    <!-- Default: gradient background -->
    <div class="hero-gradient-bg" aria-hidden="true"></div>
    <?php endif; ?>

    <!-- Dark overlay -->
    <div class="hero-overlay" aria-hidden="true"></div>

    <!-- Content -->
    <div class="hero-content">
        <?php if (!empty($profile['logo_url'])): ?>
        <img src="<?= htmlspecialchars($profile['logo_url']) ?>" alt="Logo" class="hero-logo">
        <?php endif; ?>

        <h1 class="hero-headline"><?= htmlspecialchars($heroHeadline) ?></h1>

        <?php if ($heroSubheadline): ?>
        <p class="hero-subheadline"><?= nl2br(htmlspecialchars($heroSubheadline)) ?></p>
        <?php endif; ?>

        <div class="hero-cta-row">
            <a href="#" onclick="openLeadModal(); return false;" class="hero-cta-primary">
                <span class="material-icons-round">groups</span>
                Quero Participar
            </a>
            <?php if (!empty($events)): ?>
            <a href="#hubShell" class="hero-cta-secondary" onclick="document.getElementById('hubShell').scrollIntoView({behavior:'smooth'}); return false;">
                <span class="material-icons-round">confirmation_number</span>
                Ver Eventos
            </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($profile['meeting_time']) || !empty($profile['meeting_address'])): ?>
        <div class="hero-meta-pills">
            <?php if (!empty($profile['meeting_time'])): ?>
            <span class="hero-pill">
                <span class="material-icons-round">schedule</span>
                <?= htmlspecialchars($profile['meeting_time']) ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($profile['meeting_address'])): ?>
            <span class="hero-pill">
                <span class="material-icons-round">location_on</span>
                <?= htmlspecialchars(mb_substr($profile['meeting_address'], 0, 50)) ?>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Scroll indicator -->
    <div class="hero-scroll-hint" aria-hidden="true">
        <span class="material-icons-round">keyboard_arrow_down</span>
    </div>

</section>
<?php endif; // showHero ?>

<!-- Lead Capture Modal -->
<div id="leadModal" style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(0,0,0,0.85); backdrop-filter:blur(12px); -webkit-backdrop-filter:blur(12px); align-items:center; justify-content:center; padding:16px;">
    <div style="background:#0d1017; border:1px solid rgba(0,204,255,0.25); border-radius:24px; width:100%; max-width:440px; padding:32px; position:relative; box-shadow:0 24px 80px rgba(0,0,0,0.6);">
        <!-- Close -->
        <button onclick="closeLeadModal()" style="position:absolute;top:16px;right:16px;background:rgba(0,204,255,0.1);border:1px solid rgba(0,204,255,0.3);color:#e8f4ff;border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;">
            <span class="material-icons-round" style="font-size:18px;">close</span>
        </button>
        <!-- Header -->
        <div style="text-align:center;margin-bottom:24px;">
            <div style="width:56px;height:56px;background:linear-gradient(135deg,#00ccff,#00e07a);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <span class="material-icons-round" style="font-size:28px;color:#000;">emoji_people</span>
            </div>
            <h2 style="margin:0 0 6px;color:#e8f4ff;font-size:1.3rem;font-weight:900;">Quero Participar!</h2>
            <p style="margin:0;color:rgba(232,244,255,0.55);font-size:0.88rem;">Deixe seus dados e o clube entrará em contato.</p>
        </div>
        <!-- Form -->
        <form id="leadForm" onsubmit="submitLead(event)">
            <div style="margin-bottom:14px;">
                <input type="text" name="name" placeholder="Seu nome completo *" maxlength="120" required
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:12px 14px;color:#e8f4ff;font-size:0.9rem;font-family:inherit;outline:none;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#00ccff'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
            </div>
            <div style="margin-bottom:14px;">
                <input type="tel" name="phone" id="leadPhone" placeholder="WhatsApp (ex: 44 99999-9999)" maxlength="30"
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:12px 14px;color:#e8f4ff;font-size:0.9rem;font-family:inherit;outline:none;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#00ccff'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
            </div>
            <div style="margin-bottom:14px;">
                <input type="email" name="email" placeholder="E-mail (opcional)" maxlength="160"
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:12px 14px;color:#e8f4ff;font-size:0.9rem;font-family:inherit;outline:none;box-sizing:border-box;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#00ccff'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'">
            </div>
            <div style="margin-bottom:20px;">
                <textarea name="message" placeholder="Por que quer participar? (opcional)" rows="2" maxlength="300"
                    style="width:100%;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:10px;padding:12px 14px;color:#e8f4ff;font-size:0.9rem;font-family:inherit;outline:none;box-sizing:border-box;resize:none;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#00ccff'" onblur="this.style.borderColor='rgba(255,255,255,0.12)'"></textarea>
            </div>
            <!-- Honeypot -->
            <input type="text" name="website" style="display:none!important;" tabindex="-1" autocomplete="off">
            <button type="submit" id="leadSubmitBtn"
                style="width:100%;background:linear-gradient(135deg,#00ccff,#00e07a);color:#000;border:none;border-radius:12px;padding:14px;font-weight:900;font-size:1rem;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity .2s;">
                <span class="material-icons-round">send</span>
                Enviar Interesse
            </button>
        </form>
        <!-- Success state (hidden initially) -->
        <div id="leadSuccess" style="display:none;text-align:center;padding:16px 0;">
            <span class="material-icons-round" style="font-size:3.5rem;color:#00e07a;display:block;margin-bottom:12px;">check_circle</span>
            <h3 style="color:#e8f4ff;margin:0 0 8px;font-size:1.2rem;">Recebemos seu interesse!</h3>
            <p id="leadSuccessMsg" style="color:rgba(232,244,255,0.6);margin:0;font-size:0.9rem;"></p>
        </div>
    </div>
</div>

<?php /* ══ MOBILE HERO ══════════════════════════════════════════════════════ */ ?>
<div class="mobile-hero" <?= $showHero ? 'style="display:none;"' : '' ?>>
    <?php if (!empty($profile['logo_url'])): ?>
        <img src="<?= htmlspecialchars($profile['logo_url']) ?>" alt="Logo" class="club-avatar">
    <?php else: ?>
        <div class="club-avatar-placeholder">⛺</div>
    <?php endif; ?>
    <h1 class="club-name"><?= htmlspecialchars($profile['display_name']) ?></h1>
    <?php if (!empty($profile['club_motto'])): ?>
        <div class="club-motto">"<?= htmlspecialchars($profile['club_motto']) ?>"</div>
    <?php endif; ?>
    <div class="club-stats">
        <div class="stat-chip">
            <span class="material-icons-round">groups</span>
            <?= number_format($stats['members']) ?> membros
        </div>
        <div class="stat-chip">
            <span class="material-icons-round">local_activity</span>
            <?= number_format($stats['activities']) ?> atividades
        </div>
    </div>
</div>

<?php /* ══ MAIN SHELL ══════════════════════════════════════════════════════════ */ ?>
<div class="hub-shell" id="hubShell">

    <?php /* ── LEFT SIDEBAR ─────────────────────────────────────────────────── */ ?>
    <aside class="hub-sidebar-left">
        <div class="club-card">
            <?php if (!empty($profile['logo_url'])): ?>
                <img src="<?= htmlspecialchars($profile['logo_url']) ?>" alt="Logo" class="club-avatar">
            <?php else: ?>
                <div class="club-avatar-placeholder">⛺</div>
            <?php endif; ?>
            <h2 class="club-name"><?= htmlspecialchars($profile['display_name']) ?></h2>
            <?php if (!empty($profile['club_motto'])): ?>
                <div class="club-motto">"<?= htmlspecialchars($profile['club_motto']) ?>"</div>
            <?php endif; ?>
            <?php if (!empty($profile['welcome_message'])): ?>
                <p class="club-desc"><?= nl2br(htmlspecialchars(mb_substr($profile['welcome_message'], 0, 200))) ?><?= mb_strlen($profile['welcome_message']) > 200 ? '...' : '' ?></p>
            <?php endif; ?>
            <div class="club-stats">
                <div class="stat-chip">
                    <span class="material-icons-round">groups</span>
                    <?= number_format($stats['members']) ?> membros
                </div>
                <div class="stat-chip">
                    <span class="material-icons-round">local_activity</span>
                    <?= number_format($stats['activities']) ?> atividades
                </div>
            </div>
        </div>

        <?php if (!empty($profile['club_vow']) || !empty($profile['club_law'])): ?>
        <div class="club-card">
            <div class="ideals-list">
                <?php if (!empty($profile['club_vow'])): ?>
                <div class="ideal-item">
                    <span class="material-icons-round">volunteer_activism</span>
                    <div>
                        <strong>Voto</strong>
                        <span><?= htmlspecialchars(mb_substr($profile['club_vow'], 0, 120)) ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($profile['club_law'])): ?>
                <div class="ideal-item">
                    <span class="material-icons-round">menu_book</span>
                    <div>
                        <strong>Lei</strong>
                        <span><?= htmlspecialchars(mb_substr($profile['club_law'], 0, 120)) ?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="cta-card">
            <h3>Quer ser Desbravador?</h3>
            <p>Junte-se ao <?= htmlspecialchars($profile['display_name']) ?> e viva aventuras incríveis!</p>
            <a href="#" onclick="openLeadModal(); return false;" class="cta-btn">Quero participar</a>
        </div>
    </aside>

    <?php /* ── CENTER FEED ──────────────────────────────────────────────────── */ ?>
    <?php
    // Build unique author list for story bubbles (from curated media already loaded)
    $_storyAuthors = [];
    $_storyNames   = [];
    $_authorCounts = array_count_values(array_filter(array_map(
        fn($m) => trim($m['user_name'] ?? ''), $curatedMedia
    )));
    foreach ($curatedMedia as $_sm) {
        $_sn = trim($_sm['user_name'] ?? '');
        if (!$_sn || in_array($_sn, $_storyNames)) continue;
        $_storyNames[]   = $_sn;
        $_storyAuthors[] = ['name' => $_sn, 'avatar' => trim($_sm['avatar_url'] ?? ''), 'count' => (int)($_authorCounts[$_sn] ?? 0)];
        if (count($_storyAuthors) >= 14) break;
    }
    ?>
    <main class="hub-feed">

        <?php if (count($_storyAuthors) > 1): ?>
        <div class="story-bar" id="storyBar" aria-label="Filtrar por membro">
            <?php /* "Todos" bubble */ ?>
            <button class="story-bubble story-bubble-all active" onclick="filterByAuthor(null, this)" aria-label="Todos os membros">
                <div class="story-ring">
                    <div class="story-initials" style="font-size:1.5rem;">★</div>
                </div>
                <span class="story-name">Todos</span>
            </button>
            <?php foreach ($_storyAuthors as $_sa): ?>
            <button class="story-bubble"
                    onclick="filterByAuthor(<?= json_encode($_sa['name']) ?>, this)"
                    aria-label="<?= htmlspecialchars($_sa['name']) ?>">
                <div class="story-ring" style="position:relative;">
                    <?php if (!empty($_sa['avatar'])): ?>
                    <img src="<?= htmlspecialchars($_sa['avatar']) ?>" alt="<?= htmlspecialchars($_sa['name']) ?>" loading="lazy">
                    <?php else: ?>
                    <div class="story-initials"><?= htmlspecialchars(mb_substr($_sa['name'], 0, 1)) ?></div>
                    <?php endif; ?>
                    <?php if ($_sa['count'] > 1): ?>
                    <span class="story-bubble-badge"><?= $_sa['count'] ?></span>
                    <?php endif; ?>
                </div>
                <span class="story-name"><?= htmlspecialchars(explode(' ', $_sa['name'])[0]) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="feed-toolbar">
            <h2 class="feed-title">
                <span class="material-icons-round">auto_awesome</span>
                Galeria do Clube
            </h2>
            <div style="display:flex;align-items:center;gap:8px;">
                <div class="view-toggle-group" role="group" aria-label="Ordenar">
                    <button class="view-toggle-btn active" id="btnSortRecent" onclick="sortCards('recent', this)" title="Mais recentes">
                        <span class="material-icons-round">schedule</span>
                    </button>
                    <button class="view-toggle-btn" id="btnSortLikes" onclick="sortCards('likes', this)" title="Mais curtidos">
                        <span class="material-icons-round">favorite_border</span>
                    </button>
                    <button class="view-toggle-btn" id="btnSortViews" onclick="sortCards('views', this)" title="Mais vistos">
                        <span class="material-icons-round">visibility</span>
                    </button>
                </div>
                <div class="view-toggle-group" role="group" aria-label="Modo de visualização">
                    <button class="view-toggle-btn active" id="btnGridMode" onclick="setViewMode('grid')" aria-label="Grade">
                        <span class="material-icons-round">grid_view</span>
                    </button>
                    <button class="view-toggle-btn" id="btnFeedMode" onclick="setViewMode('feed')" aria-label="Feed">
                        <span class="material-icons-round">view_agenda</span>
                    </button>
                    <button class="view-toggle-btn" id="btnTikTokMode" onclick="enterTikTokMode()" aria-label="Modo TikTok" title="Tela cheia">
                        <span class="material-icons-round">smart_display</span>
                    </button>
                </div>
            </div>
        </div>

        <?php if (!empty($curatedMedia)):
            // Build type counts for filter buttons
            $_typeCounts = ['photo' => 0, 'video' => 0, 'reels' => 0, 'tiktok' => 0, 'youtube' => 0];
            foreach ($curatedMedia as $_tm) {
                $_tk = getMediaTypeKey($_tm);
                if (isset($_typeCounts[$_tk])) $_typeCounts[$_tk]++;
            }
        ?>
        <div class="type-filter-bar" id="typeFilterBar">
            <button class="type-filter-btn active" data-media-filter="" onclick="filterByType('', this)">
                <span class="material-icons-round">apps</span>
                Tudo
                <span class="type-filter-btn-count"><?= count($curatedMedia) ?></span>
            </button>
            <?php if ($_typeCounts['photo'] > 0): ?>
            <button class="type-filter-btn" data-media-filter="photo" onclick="filterByType('photo', this)">
                <span class="material-icons-round">image</span>
                Fotos
                <span class="type-filter-btn-count"><?= $_typeCounts['photo'] ?></span>
            </button>
            <?php endif; ?>
            <?php if ($_typeCounts['video'] > 0): ?>
            <button class="type-filter-btn" data-media-filter="video" onclick="filterByType('video', this)">
                <span class="material-icons-round">videocam</span>
                Vídeos
                <span class="type-filter-btn-count"><?= $_typeCounts['video'] ?></span>
            </button>
            <?php endif; ?>
            <?php if ($_typeCounts['reels'] > 0): ?>
            <button class="type-filter-btn" data-media-filter="reels" onclick="filterByType('reels', this)">
                <span class="material-icons-round">photo_camera</span>
                Reels
                <span class="type-filter-btn-count"><?= $_typeCounts['reels'] ?></span>
            </button>
            <?php endif; ?>
            <?php if ($_typeCounts['tiktok'] > 0): ?>
            <button class="type-filter-btn" data-media-filter="tiktok" onclick="filterByType('tiktok', this)">
                <span class="material-icons-round">music_video</span>
                TikTok
                <span class="type-filter-btn-count"><?= $_typeCounts['tiktok'] ?></span>
            </button>
            <?php endif; ?>
            <?php if ($_typeCounts['youtube'] > 0): ?>
            <button class="type-filter-btn" data-media-filter="youtube" onclick="filterByType('youtube', this)">
                <span class="material-icons-round">smart_display</span>
                YouTube
                <span class="type-filter-btn-count"><?= $_typeCounts['youtube'] ?></span>
            </button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($eventGroups)): ?>
        <div class="event-albums-bar" id="eventAlbumsBar">
            <?php foreach ($eventGroups as $_eg): ?>
            <div class="event-album-card"
                 onclick="filterByDateRange(<?= json_encode($_eg['from']) ?>, <?= json_encode($_eg['to']) ?>, this)"
                 title="<?= htmlspecialchars($_eg['title']) ?>">
                <?php if (!empty($_eg['cover'])): ?>
                <img class="event-album-thumb" src="<?= htmlspecialchars($_eg['cover']) ?>" alt="<?= htmlspecialchars($_eg['title']) ?>" loading="lazy">
                <?php else: ?>
                <div class="event-album-thumb-placeholder">
                    <span class="material-icons-round">photo_library</span>
                </div>
                <?php endif; ?>
                <div class="event-album-info">
                    <div class="event-album-title"><?= htmlspecialchars($_eg['title']) ?></div>
                    <div class="event-album-count"><?= $_eg['count'] ?> mídia<?= $_eg['count'] > 1 ? 's' : '' ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($curatedMedia)): ?>
        <div class="media-grid" id="mediaGrid">
            <?php foreach ($curatedMedia as $media): ?>
            <?php
                $thumb    = buildThumb($media);
                $isVid    = isVideoUrl(trim($media['media_content'] ?? ''));
                $badge    = mediaTypeBadge($media);
                $badgeIco = mediaBadgeIcon($media);
                $st       = $media['source_type'];
                $si       = (int)$media['source_id'];
                $liked    = !empty($media['has_liked']);
                $likes    = (int)($media['like_count'] ?? 0);
                $views    = (int)($media['view_count'] ?? 0);
                $comCount = (int)($media['comment_count'] ?? 0);
                $content   = htmlspecialchars($media['media_content'] ?? '', ENT_QUOTES);
                $title     = htmlspecialchars($media['title'] ?? '');
                $author    = htmlspecialchars($media['user_name'] ?? '');
                $avatar    = htmlspecialchars($media['avatar_url'] ?? '');
                $mediaType = getMediaTypeKey($media);
                $mediaDate = $media['date'] ?? '';
                $reactionsJson = htmlspecialchars(json_encode($media['reactions'] ?? []), ENT_QUOTES);
                $myReaction    = htmlspecialchars($media['my_reaction'] ?? '', ENT_QUOTES);
            ?>
            <div class="media-card"
                 data-source-type="<?= $st ?>"
                 data-source-id="<?= $si ?>"
                 data-url="<?= $content ?>"
                 data-is-video="<?= $isVid ? '1' : '0' ?>"
                 data-like-count="<?= $likes ?>"
                 data-has-liked="<?= $liked ? '1' : '0' ?>"
                 data-comment-count="<?= $comCount ?>"
                 data-view-count="<?= $views ?>"
                 data-title="<?= $title ?>"
                 data-author="<?= $author ?>"
                 data-avatar="<?= $avatar ?>"
                 data-media-type="<?= $mediaType ?>"
                 data-date="<?= htmlspecialchars($mediaDate) ?>"
                 data-reactions="<?= $reactionsJson ?>"
                 data-my-reaction="<?= $myReaction ?>"
                 data-viewed="0">

                <div class="media-card__image-container" onclick="openViewer(this.closest('.media-card'))" ondblclick="doubleTapLike(event, this.closest('.media-card'))">
                    <?php if ($thumb): ?>
                    <img class="media-card__thumb" src="<?= htmlspecialchars($thumb) ?>" alt="<?= $title ?>" loading="lazy" onerror="thumbFallback(this)">
                    <?php endif;
                    $avatarFb = htmlspecialchars($media['avatar_url'] ?? '');
                    $nameFb   = htmlspecialchars($media['user_name'] ?? 'Membro');
                    ?>
                    <div class="thumb-fallback" style="<?= ($thumb ? 'display:none;' : 'display:flex;') ?> align-items: center; justify-content: center; flex-direction: column;">
                        <?php if ($avatarFb): ?>
                        <div class="thumb-fallback__bg" style="background-image:url('<?= $avatarFb ?>')"></div>
                        <img class="thumb-fallback__avatar" src="<?= $avatarFb ?>" alt="<?= $nameFb ?>" onerror="this.style.display='none'">
                        <?php else: ?>
                        <div class="thumb-fallback__bg thumb-fallback__bg--gradient"></div>
                        <?php endif; ?>
                        <div class="thumb-fallback__play">play_circle</div>
                        <div class="thumb-fallback__name"><?= $nameFb ?></div>
                    </div>
                    
                    <div class="media-card__gradient"></div>
                    <div class="media-card__top">
                        <div class="media-type-badge">
                            <span class="material-icons-round"><?= $badgeIco ?></span>
                            <?= $badge ?>
                        </div>
                    </div>
                </div>

                <div class="media-card__body">
                    <div class="media-card__meta">
                        <div class="media-card__author">
                            <?php if ($avatar): ?>
                                <img src="<?= $avatar ?>" alt="">
                            <?php endif; ?>
                            <span><?= $author ?></span>
                        </div>
                        <?php if ($views > 0): ?>
                        <div class="stat-chip" style="padding: 2px 8px; font-size: 0.7rem;">
                            <span class="material-icons-round" style="font-size: 14px;">visibility</span>
                            <?= number_format($views) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="media-card__title" onclick="openViewer(this.closest('.media-card'))"><?= $title ?></div>

                    <div class="social-interaction-bar">
                        <div class="reaction-picker-wrap" id="rxWrap_<?= $st ?>_<?= $si ?>">
                            <div class="reaction-picker" id="rxPicker_<?= $st ?>_<?= $si ?>">
                                <?php foreach (['like'=>'❤️','love'=>'😍','haha'=>'😂','wow'=>'😮','clap'=>'👏','fire'=>'🔥'] as $_rk => $_re): ?>
                                <button class="reaction-btn <?= ($myReaction === $_rk) ? 'my-reaction' : '' ?>"
                                        onclick="sendReaction('<?= $st ?>', <?= $si ?>, '<?= $_rk ?>', this); event.stopPropagation();"
                                        title="<?= $_rk ?>">
                                    <?= $_re ?>
                                </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="interaction-item <?= $liked ? 'liked' : '' ?>"
                                 id="cardLike_<?= $st ?>_<?= $si ?>"
                                 onclick="handleLike('<?= $st ?>', <?= $si ?>)"
                                 oncontextmenu="toggleReactionPicker('<?= $st ?>',<?= $si ?>); return false;"
                                 ontouchstart="startReactionHold('<?= $st ?>',<?= $si ?>)"
                                 ontouchend="clearReactionHold()">
                                <span class="material-icons-round"><?= $liked ? 'favorite' : 'favorite_border' ?></span>
                                <span id="cardLikeCount_<?= $st ?>_<?= $si ?>"><?= $likes > 0 ? $likes : '' ?></span>
                            </div>
                        </div>
                        <?php /* Reaction summary (top 3 emojis) */ ?>
                        <?php if (!empty($media['reactions'])): ?>
                        <div class="reaction-summary" id="rxSummary_<?= $st ?>_<?= $si ?>">
                            <?php $_rxEmojis = ['like'=>'❤️','love'=>'😍','haha'=>'😂','wow'=>'😮','clap'=>'👏','fire'=>'🔥']; ?>
                            <?php foreach (array_slice($media['reactions'], 0, 3) as $_rx): ?>
                            <span title="<?= (int)$_rx['cnt'] ?>"><?= $_rxEmojis[$_rx['reaction_type']] ?? '' ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <div class="interaction-item" onclick="openViewer(this.closest('.media-card'), true)">
                            <span class="material-icons-round">chat_bubble_outline</span>
                            <span><?= $comCount > 0 ? $comCount : '' ?></span>
                        </div>
                        <div class="interaction-item" onclick="shareMedia('<?= base_url('c/' . $clubSlug) ?>', '<?= $title ?>')">
                            <span class="material-icons-round">share</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($hasMore ?? false): ?>
        <div id="loadSentinel" class="load-sentinel"></div>
        <div id="loadSpinner" class="load-spinner" style="display:none;">
            <div class="spin"></div> Carregando mais...
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="feed-empty">
            <span class="material-icons-round">perm_media</span>
            Nenhuma mídia aprovada para exibição ainda.
        </div>
        <?php endif; ?>
    </main>

    <?php /* ── RIGHT SIDEBAR ──────────────────────────────────────────────────── */ ?>
    <aside class="hub-sidebar-right">
        <?php if (!empty($events)): ?>
        <div class="sidebar-section">
            <h3 class="sidebar-title">
                <span class="material-icons-round">event</span>
                Próximos Eventos
            </h3>
            <?php foreach ($events as $evt):
                $evtDate   = new DateTime($evt['start_datetime']);
                $evtIsFull = $evt['max_participants'] > 0 && ($evt['enrolled_count'] ?? 0) >= $evt['max_participants'];
            ?>
            <div class="event-item">
                <div class="event-date-badge">
                    <span><?= $evtDate->format('M') ?></span>
                    <span class="day"><?= $evtDate->format('d') ?></span>
                </div>
                <div class="event-info">
                    <h4><?= htmlspecialchars($evt['title']) ?></h4>
                    <p>
                        <?= $evtDate->format('H:i') ?>
                        <?php if ($evt['location']): ?> · <?= htmlspecialchars(mb_substr($evt['location'], 0, 30)) ?><?php endif; ?>
                    </p>
                    <p><?= $evt['is_paid'] ? 'R$ ' . number_format($evt['price'], 2, ',', '.') : 'Gratuito' ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <a href="<?= base_url('c/' . $clubSlug . '/evento/' . ($events[0]['slug'] ?? '')) ?>"
               class="event-link" style="margin-top:12px;">
                <span class="material-icons-round">confirmation_number</span>
                Ver eventos
            </a>
        </div>
        <?php endif; ?>

        <div class="sidebar-section" style="padding:16px;">
            <h3 class="sidebar-title">
                <span class="material-icons-round">emoji_events</span>
                Destaques
            </h3>
            <div class="ticker-wrap">
                <div class="ticker-track" id="tickerTrack">
                    <?php $tItems = array_slice($curatedMedia, 0, 8); foreach ($tItems as $t): ?>
                    <div class="ticker-item">
                        <span class="material-icons-round">star</span>
                        <?= htmlspecialchars(mb_substr($t['user_name'] ?? '', 0, 20)) ?>
                    </div>
                    <?php endforeach;
                    // Duplicate for seamless loop
                    foreach ($tItems as $t): ?>
                    <div class="ticker-item">
                        <span class="material-icons-round">star</span>
                        <?= htmlspecialchars(mb_substr($t['user_name'] ?? '', 0, 20)) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </aside>

</div><!-- /.hub-shell -->

<?php /* ══ MEDIA VIEWER MODAL ════════════════════════════════════════════════ */ ?>

<!-- Shield: absorbs stray taps near the close button before the iframe gets them -->
<div id="viewerCloseShield" onclick="closeViewer()"></div>

<!-- Botão de fechar FORA do modal para evitar que o iframe capture o evento -->
<button id="viewerFloatClose" class="viewer-float-close" aria-label="Fechar" style="display:none;">
    <span class="material-icons-round">close</span>
</button>

<div id="mediaViewer" class="media-viewer" role="dialog" aria-modal="true">

    <div class="viewer-inner">
        <!-- Media pane -->
        <div class="viewer-media" id="viewerMedia">
            <!-- Content injected by JS -->
        </div>

        <!-- Side panel -->
        <div class="viewer-panel">
            <div class="viewer-panel-header">
                <button class="viewer-panel-back" onclick="viewer.classList.remove('showing-comments')" aria-label="Voltar">
                    <span class="material-icons-round">arrow_back</span>
                </button>
                <h3 id="viewerTitle">
                    <span class="material-icons-round">chat_bubble_outline</span>
                    Comentários
                </h3>
                <button class="viewer-close-btn" onclick="closeViewer()" aria-label="Fechar">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            <!-- Nav row: inside the panel = completely outside the iframe area -->
            <div class="viewer-nav-row" id="viewerNavRow">
                <button id="viewerPrev" class="viewer-nav-btn" aria-label="Anterior">
                    <span class="material-icons-round">chevron_left</span>
                </button>
                <span class="viewer-nav-counter" id="viewerNavCounter"></span>
                <button id="viewerNext" class="viewer-nav-btn" aria-label="Próximo">
                    <span class="material-icons-round">chevron_right</span>
                </button>
            </div>

            <div class="viewer-author-strip" id="viewerAuthorInfo" style="display:none;">
                <!-- Author info injected by JS -->
            </div>

            <div class="viewer-actions" id="viewerActions">
                <!-- Like + share injected by JS -->
            </div>

            <div class="comment-list" id="commentList">
                <div class="comment-empty">
                    <span class="material-icons-round">chat_bubble_outline</span>
                    Carregando comentários...
                </div>
            </div>

            <form class="comment-form" id="commentForm" onsubmit="submitComment(event)">
                <input type="text" name="author_name" placeholder="Seu nome" maxlength="80" required autocomplete="name">
                <textarea name="content" placeholder="Adicione um comentário…" rows="3" maxlength="500" required></textarea>
                <!-- Honeypot -->
                <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off">
                <button type="submit" class="comment-submit-btn">
                    <span class="material-icons-round">send</span>
                    Enviar
                </button>
            </form>
        </div>
    </div>
</div>


<?php
// Preconnect hints for embed origins used in the viewer
$_hasIg = false; $_hasTt = false;
foreach ($curatedMedia as $_m) {
    $_u = trim($_m['media_content'] ?? '');
    if (strpos($_u, 'instagram.com') !== false) $_hasIg = true;
    if (strpos($_u, 'tiktok.com')    !== false) $_hasTt = true;
}
?>
<?php if ($_hasIg): ?><link rel="preconnect" href="https://www.instagram.com"><?php endif; ?>
<?php if ($_hasTt): ?><link rel="preconnect" href="https://www.tiktok.com"><?php endif; ?>
<?php /* Instagram and TikTok embeds use direct iframe (no SDK scripts needed) */ ?>

<script>

// ── Constants ────────────────────────────────────────────────────────────────
const LIKE_URL     = '<?= $likeUrl ?>';
const MEDIA_URL    = '<?= $mediaApiUrl ?>';
const COMMENT_BASE = '<?= $commentBase ?>';
const CLUB_SLUG    = '<?= htmlspecialchars($clubSlug, ENT_QUOTES) ?>';

// ── Thumbnail fallback handler ───────────────────────────────────────────────
function thumbFallback(img) {
    img.style.display = 'none';
    const fb = img.nextElementSibling;
    if (fb && fb.classList.contains('thumb-fallback')) {
        fb.style.display = 'flex';
    }
}

// ── Infinite scroll state ────────────────────────────────────────────────────
let currentPage = 1;
let isLoading   = false;
let hasMore     = <?= json_encode(!empty($hasMore)) ?>;
let currentViewerCard = null;

// ── Embed Engine ─────────────────────────────────────────────────────────────
const EmbedLoader = {
    _done: {},
    load(src, id) {
        if (this._done[id] || document.getElementById(id)) { this._done[id] = true; return Promise.resolve(); }
        return new Promise((res, rej) => {
            const s = document.createElement('script');
            s.src = src; s.id = id; s.async = true;
            s.onload = () => { this._done[id] = true; res(); };
            s.onerror = rej;
            document.body.appendChild(s);
        });
    }
};

// Regex matching exact patterns from helpers/media.php embed_media()
const RE_YT_SHORT = /youtube\.com\/shorts\/([^"&?\/\s]{11})/i;
const RE_YT       = /(?:(?:(?:m|www)\.)?youtube\.com\/(?:[^\/\s]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i;
const RE_TT       = /(?:www\.)?tiktok\.com\/.*\/video\/([0-9]+)/i;
const RE_IG       = /(?:www\.)?instagram\.com\/(p|reel|reels|tv)\/([A-Za-z0-9_-]+)/i;
const RE_TW       = /(?:twitter|x)\.com\/[^\s/]+\/status\/([0-9]+)/i;
const RE_VID      = /(\.mp4|\.webm|\.mov)$/i;

function buildEmbedHtml(url, isVideo, thumbUrl) {
    if (!isVideo) {
        const src = url.startsWith('storage/') ? '/' + url : url;
        return `<img src="${src}" style="max-height:85vh;max-width:100%;border-radius:24px;object-fit:contain;box-shadow:0 30px 90px rgba(0,0,0,0.8);">`;
    }

    const ytShort = url.match(RE_YT_SHORT);
    const ytMatch = !ytShort && url.match(RE_YT);
    const ttMatch = url.match(RE_TT);
    const igMatch = !ttMatch && url.match(RE_IG);
    const twMatch = url.match(RE_TW);

    // ── YouTube (iframe nativo) ────────────────────────────────────────
    if (ytShort) return `<div class="embed-wrap portrait"><iframe src="https://www.youtube.com/embed/${ytShort[1]}?autoplay=1&playsinline=1&rel=0&modestbranding=1" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" allowfullscreen></iframe></div>`;
    if (ytMatch) return `<div class="embed-wrap landscape"><iframe src="https://www.youtube.com/embed/${ytMatch[1]}?autoplay=1&playsinline=1&rel=0&modestbranding=1" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" allowfullscreen></iframe></div>`;

    // ── TikTok (iframe embed/v2) ──────────────────────────────────────
    // TikTok embed/v2 always autoplays muted. We show a custom unmute overlay:
    // tapping it is a direct user gesture in the parent context, which allows us
    // to re-set iframe.src with allow="autoplay" and the browser will permit audio.
    if (ttMatch) {
        const videoId = ttMatch[1];
        const ttSrc   = `https://www.tiktok.com/embed/v2/${videoId}?autoplay=1`;
        const bgHtml  = thumbUrl
            ? `<img class="tt-gate__bg" src="${thumbUrl}" alt="" loading="eager">`
            : '';
        // Gate: sem iframe até o toque — nenhum cross-origin para capturar eventos.
        // ttLaunch() cria o iframe DENTRO do handler do clique (gesto do usuário)
        // + sandbox bloqueia window.top.location / window.open → sem redirect ao app.
        return `<div class="embed-wrap portrait tt-wrap">
            <div class="tt-gate" onclick="ttLaunch(this)" data-src="${ttSrc}">
                ${bgHtml}
                <div class="tt-gate__btn">
                    <span class="material-icons-round">play_circle</span>
                    <span>Toque para<br>reproduzir</span>
                </div>
            </div>
        </div>`;
    }

    // ── Instagram (iframe direto — sem SDK, sem ad-blocker issues) ────
    // Instagram serve /reel/{id}/embed/ e /p/{id}/embed/ nativamente sem SDK.
    // É o mesmo mecanismo do botão "Compartilhar > Incorporar" do próprio app.
    if (igMatch) {
        const postType = igMatch[1].toLowerCase() === 'reels' ? 'reel' : igMatch[1];
        const postId   = igMatch[2];
        const isReel   = postType === 'reel' || postType === 'tv';
        const embedSrc = `https://www.instagram.com/${postType}/${postId}/embed/`;

        if (isReel) {
            // Reels e IGTV são portrait (9:16) — usa o sistema embed-wrap existente
            return `<div class="embed-wrap portrait">
                <iframe src="${embedSrc}"
                        style="position:absolute;inset:0;width:100%;height:100%;border:none;overflow:hidden;"
                        allowtransparency="true" allowfullscreen="true"
                        allow="encrypted-media; picture-in-picture; clipboard-write"
                        scrolling="no" loading="lazy">
                </iframe>
            </div>`;
        } else {
            // Posts regulares (p/) — proporção mais próxima de quadrado
            return `<div style="width:100%;max-width:540px;margin:0 auto;">
                <iframe src="${embedSrc}"
                        width="100%" height="600"
                        style="border:none;overflow:hidden;display:block;border-radius:12px;"
                        allowtransparency="true" allowfullscreen="true"
                        allow="encrypted-media; picture-in-picture; clipboard-write"
                        scrolling="no" loading="lazy">
                </iframe>
            </div>`;
        }
    }

    // ── X / Twitter ───────────────────────────────────────────────────
    if (twMatch) {
        const tweetUrl = url.replace(/https?:\/\/(?:www\.)?x\.com/i, 'https://twitter.com');
        EmbedLoader.load('https://platform.twitter.com/widgets.js', 'twitter-widgets-js')
            .then(() => window.twttr?.widgets?.load(viewerMedia));
        return `<div class="embed-wrap social-embed-wrap"><blockquote class="twitter-tweet" data-theme="dark" data-conversation="none"><a href="${tweetUrl}">Ver no X</a></blockquote></div>`;
    }

    // ── Direct / Storage Files ────────────────────────────────────────
    if (RE_VID.test(url) || url.includes('storage/')) {
        const src = url.startsWith('storage/') ? '/' + url : url;
        return `<div class="embed-wrap landscape" style="background:#000;"><video src="${src}" controls autoplay playsinline style="position:absolute;inset:0;width:100%;height:100%;object-fit:contain;"></video></div>`;
    }
    return externalLinkCard(url, 'Link Externo', 'open_in_new');
}

function externalLinkCard(url, name, icon) {
    const domain = (() => { try { return new URL(url).hostname; } catch(e) { return ''; } })();
    return `<div style="text-align:center;padding:48px 32px;background:rgba(255,255,255,0.04);border-radius:20px;border:1px solid rgba(255,255,255,0.1);">
        <span class="material-icons-round" style="font-size:4rem;margin-bottom:20px;display:block;color:var(--accent);">${icon}</span>
        <h3 style="color:white;margin:0 0 8px;font-size:1.2rem;">${name}</h3>
        ${domain ? `<p style="color:rgba(255,255,255,0.4);font-size:0.82rem;margin:0 0 28px;font-family:monospace;">${domain}</p>` : ''}
        <a href="${url}" onclick="closeViewer()"
           style="display:inline-flex;align-items:center;gap:10px;padding:12px 28px;background:var(--accent);color:#000;border-radius:12px;font-weight:800;text-decoration:none;">
            <span class="material-icons-round">open_in_new</span> Ver conteúdo
        </a>
    </div>`;
}

// ── Media Viewer ─────────────────────────────────────────────────────────────
const viewer       = document.getElementById('mediaViewer');
const viewerMedia  = document.getElementById('viewerMedia');
const viewerActions= document.getElementById('viewerActions');
const commentList  = document.getElementById('commentList');
const commentForm  = document.getElementById('commentForm');
const floatClose   = document.getElementById('viewerFloatClose');
const closeShield  = document.getElementById('viewerCloseShield');

// Use pointer/touch events with stopPropagation so the iframe never sees the tap
floatClose.addEventListener('pointerdown', function(e) {
    e.stopPropagation();
    e.preventDefault();
    closeViewer();
}, { passive: false });
// Fallback for environments that don't fire pointerdown
floatClose.addEventListener('click', function(e) {
    e.stopPropagation();
    e.preventDefault();
    closeViewer();
});

function openViewer(card, forceComments = false) {
    currentViewerCard = card;
    const url      = card.dataset.url;
    const isVideo  = card.dataset.isVideo === '1';
    const st       = card.dataset.sourceType;
    const si       = card.dataset.sourceId;
    const liked    = card.dataset.hasLiked === '1';
    const likes    = parseInt(card.dataset.likeCount) || 0;
    const title    = card.dataset.title;
    const author   = card.dataset.author || '';
    const avatar   = card.dataset.avatar || '';

    // Build embed
    const _cardThumb = card.querySelector('img.media-card__thumb')?.src || '';
    viewerMedia.innerHTML = buildEmbedHtml(url, isVideo, _cardThumb);

    // Update title
    document.getElementById('viewerTitle').innerHTML = `
        <span class="material-icons-round">${isVideo ? 'play_circle' : 'image'}</span>
        ${title || 'Mídia'}
    `;

    // Author strip
    const authorStrip = document.getElementById('viewerAuthorInfo');
    if (authorStrip && author) {
        // Count posts by this author visible in the grid
        const authorPostCount = document.querySelectorAll(`#mediaGrid .media-card[data-author="${CSS.escape(author)}"]`).length;
        const avatarHtml = avatar
            ? `<img src="${escapeHtml(avatar)}" alt="${escapeHtml(author)}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
               <div class="viewer-author-strip-initials" style="display:none;">${escapeHtml(author.charAt(0).toUpperCase())}</div>`
            : `<div class="viewer-author-strip-initials">${escapeHtml(author.charAt(0).toUpperCase())}</div>`;
        authorStrip.innerHTML = `
            ${avatarHtml}
            <div class="viewer-author-strip-info">
                <div class="viewer-author-strip-name">${escapeHtml(author)}</div>
                <div class="viewer-author-strip-posts">${authorPostCount > 0 ? authorPostCount + ' publicação' + (authorPostCount > 1 ? 'ões' : '') : ''}</div>
            </div>`;
        authorStrip.style.display = 'flex';
    } else if (authorStrip) {
        authorStrip.style.display = 'none';
    }

    // Twitter widget
    if (url.match(RE_TW)) {
        EmbedLoader.load('https://platform.twitter.com/widgets.js', 'twitter-widgets-js')
            .then(() => window.twttr?.widgets?.load(viewerMedia))
            .catch(() => { viewerMedia.innerHTML = externalLinkCard(url, 'X / Twitter', 'open_in_new'); });
    }

    // Actions bar - PREMIUM DESIGN
    viewerActions.innerHTML = `
        <button class="viewer-action-btn ${liked ? 'liked' : ''}" id="viewerLikeBtn"
                onclick="handleLike('${st}', ${si})">
            <span class="material-icons-round">${liked ? 'favorite' : 'favorite_border'}</span>
            <span id="viewerLikeCount">${likes > 0 ? likes : ''}</span>
        </button>
        <button class="viewer-action-btn" onclick="viewer.classList.add('showing-comments')">
            <span class="material-icons-round">chat_bubble_outline</span>
            <span>${parseInt(card.dataset.commentCount) || ''}</span>
        </button>
        <button class="viewer-action-btn" onclick="shareMedia(window.location.href, '${card.dataset.title}')">
            <span class="material-icons-round">share</span>
        </button>
        ${_viewerDownloadBtn(url, card.dataset.title)}`;

    // Load comments
    loadComments(st, si);

    // Reset form
    commentForm.dataset.sourceType = st;
    commentForm.dataset.sourceId   = si;
    commentForm.reset();

    viewer.classList.add('open');
    document.body.style.overflow = 'hidden';
    floatClose.style.display = 'flex';
    closeShield.style.display = 'block';

    // Track view
    trackView(st, si);

    // If mobile, check if we should show comments full screen
    if (window.innerWidth <= 768 && forceComments) {
        viewer.classList.add('showing-comments');
    } else {
        viewer.classList.remove('showing-comments');
    }

    // Instagram usa iframe direto — nenhum SDK para processar
}

function closeViewer() {
    viewer.classList.remove('open');
    document.body.style.overflow = '';
    floatClose.style.display = 'none';
    closeShield.style.display = 'none';
    viewerMedia.innerHTML = '';
    currentViewerCard = null;
    // Prevent the residual touch/click from firing on elements now exposed below the close button
    document.body.style.pointerEvents = 'none';
    setTimeout(() => { document.body.style.pointerEvents = ''; }, 350);
}

// Tap-to-unmute for TikTok.
// Mudar iframe.allow depois que o elemento já está no DOM não retroage na política
// de permissões do browser. A única forma correta é substituir o iframe por um
// novo elemento criado com allow="autoplay" desde o início — dentro de um gesto
// direto do usuário (click) para que o browser conceda a permissão de áudio.

function ttLaunch(gate) {
    const wrapper = gate.closest('.tt-wrap');
    const src     = gate.dataset.src;
    gate.remove(); // remove gate ANTES — cria o iframe dentro do mesmo gesto de clique

    const frame = document.createElement('iframe');
    frame.className       = 'tt-iframe';
    frame.src             = src;
    frame.allowFullscreen = true;
    // allow="autoplay": Android Chrome pode conceder áudio por ser gesto direto do user.
    frame.setAttribute('allow', 'autoplay; encrypted-media; fullscreen; picture-in-picture');
    // sandbox SEM allow-top-navigation e SEM allow-popups:
    //   → window.top.location = '...'  BLOQUEADO (sem redirect para app/site)
    //   → window.open(...)             BLOQUEADO
    //   → scripts TikTok               PERMITIDO (vídeo toca, player funciona)
    //   → same-origin requests         PERMITIDO (TikTok CDN/API funciona)
    frame.setAttribute('sandbox', 'allow-scripts allow-same-origin allow-presentation allow-pointer-lock allow-orientation-lock');
    frame.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
    frame.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;border:none;';
    wrapper.appendChild(frame);
}

viewer.addEventListener('click', e => {
    if (e.target === viewer) closeViewer();
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeViewer();
});

// ── Comments ─────────────────────────────────────────────────────────────────
async function loadComments(st, si) {
    commentList.innerHTML = '<div class="comment-empty"><span class="material-icons-round">refresh</span></div>';
    try {
        const res  = await fetch(`${COMMENT_BASE}/${st}/${si}/comments`);
        const data = await res.json();
        if (!data.comments || data.comments.length === 0) {
            commentList.innerHTML = '<div class="comment-empty"><span class="material-icons-round">chat_bubble_outline</span>Seja o primeiro a comentar!</div>';
            return;
        }
        commentList.innerHTML = data.comments.map(c => `
            <div class="comment-bubble">
                <strong>${escapeHtml(c.author_name)}</strong>
                <p>${escapeHtml(c.content)}</p>
                <time>${formatDate(c.created_at)}</time>
            </div>`).join('');
    } catch(e) {
        commentList.innerHTML = '<div class="comment-empty"><span class="material-icons-round">error_outline</span>Erro ao carregar comentários.</div>';
    }
}

async function submitComment(e) {
    e.preventDefault();
    const form = e.target;
    const btn  = form.querySelector('.comment-submit-btn');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    const st = form.dataset.sourceType;
    const si = form.dataset.sourceId;
    const fd = new FormData(form);

    try {
        const res  = await fetch(`${COMMENT_BASE}/${st}/${si}/comment`, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
            form.reset();
            // Show success note
            const note = document.createElement('div');
            note.style.cssText = 'text-align:center;color:#22c55e;font-size:0.85rem;font-weight:700;padding:8px;';
            note.textContent = data.message;
            commentList.prepend(note);
            setTimeout(() => note.remove(), 4000);
        } else {
            alert(data.error || 'Erro ao enviar comentário.');
        }
    } catch(ex) {
        alert('Erro de rede.');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-icons-round">send</span> Enviar';
    }
}

// ── Likes ─────────────────────────────────────────────────────────────────────
async function handleLike(st, si) {
    const fd = new FormData();
    fd.append('source_type', st);
    fd.append('source_id',   si);
    try {
        const res  = await fetch(LIKE_URL, { method: 'POST', body: fd });
        const data = await res.json();
        if (!data.success) return;

        const liked = data.action === 'liked';
        const count = data.count;

        // Update card
        const cardKey = `${st}_${si}`;
        const cardBtn  = document.getElementById(`cardLike_${cardKey}`);
        const cardCnt  = document.getElementById(`cardLikeCount_${cardKey}`);
        const cardEl   = cardBtn?.closest('.media-card');
        if (cardEl) {
            cardEl.dataset.hasLiked   = liked ? '1' : '0';
            cardEl.dataset.likeCount  = count;
        }
        if (cardBtn) {
            cardBtn.classList.toggle('liked', liked);
            cardBtn.querySelector('.material-icons-round').textContent = liked ? 'favorite' : 'favorite_border';
        }
        if (cardCnt) cardCnt.textContent = count > 0 ? count : '';

        // Update viewer
        const viewerBtn = document.getElementById('viewerLikeBtn');
        const viewerCnt = document.getElementById('viewerLikeCount');
        if (viewerBtn) {
            viewerBtn.classList.toggle('liked', liked);
            viewerBtn.querySelector('.material-icons-round').textContent = liked ? 'favorite' : 'favorite_border';
        }
        if (viewerCnt) viewerCnt.textContent = count;

        if (navigator.vibrate) navigator.vibrate(40);
    } catch(ex) { console.error('Like error', ex); }
}

// ── View Tracking ─────────────────────────────────────────────────────────────
async function trackView(st, si) {
    try {
        await fetch(`${COMMENT_BASE}/${st}/${si}/view`, { method: 'POST' });
    } catch(e) { /* silent */ }
}

// ── IntersectionObserver — view tracking + infinite scroll ──────────────────
const viewObserver = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
            const card = entry.target;
            if (card.dataset.viewed === '0') {
                card.dataset.viewed = '1';
                trackView(card.dataset.sourceType, card.dataset.sourceId);
            }
        }
    });
}, { threshold: 0.5 });

function observeCards(container) {
    container.querySelectorAll('.media-card').forEach(c => viewObserver.observe(c));
}

const grid = document.getElementById('mediaGrid');
if (grid) {
    observeCards(grid);
    // Tag original DOM order so "Recentes" sort can restore it
    grid.querySelectorAll('.media-card').forEach((c, i) => { c.dataset.origIdx = i; });
}

// Infinite scroll
const sentinel = document.getElementById('loadSentinel');
const spinner  = document.getElementById('loadSpinner');
if (sentinel && hasMore) {
    const scrollObserver = new IntersectionObserver(entries => {
        if (entries[0].isIntersecting && !isLoading && hasMore) loadMoreMedia();
    }, { rootMargin: '200px' });
    scrollObserver.observe(sentinel);
}

async function loadMoreMedia() {
    if (isLoading || !hasMore) return;
    isLoading = true;
    if (spinner) spinner.style.display = 'flex';

    currentPage++;
    try {
        const res  = await fetch(`${MEDIA_URL}?page=${currentPage}`);
        const data = await res.json();

        if (data.items && data.items.length > 0 && grid) {
            data.items.forEach(item => {
                const card = buildCardElement(item);
                grid.insertBefore(card, sentinel);
                viewObserver.observe(card);
            });
        }
        hasMore = data.hasMore;
        if (!hasMore && sentinel) sentinel.remove();
    } catch(e) {
        console.error('Load more error', e);
    } finally {
        isLoading = false;
        if (spinner) spinner.style.display = 'none';
    }
}

function buildCardElement(item) {
    const div       = document.createElement('div');
    const st        = item.source_type;
    const si        = item.source_id;
    const url       = item.media_content || '';
    const isVideo   = /youtube\.com|youtu\.be|tiktok\.com|instagram\.com|\.mp4|\.webm|\.mov/i.test(url);
    const liked     = !!item.has_liked;
    const likes     = parseInt(item.like_count) || 0;
    const comCount  = parseInt(item.comment_count) || 0;
    const views     = parseInt(item.view_count) || 0;
    const thumb     = buildThumbJs(item);
    const title     = escapeHtml(item.title || '');
    const author    = escapeHtml(item.user_name || '');
    const avatar    = item.avatar_url || '';

    // Detect media type key (mirrors PHP getMediaTypeKey)
    function _getTypeKey(u) {
        if (/youtube\.com\/shorts/i.test(u)) return 'youtube';
        if (/youtube\.com|youtu\.be/i.test(u)) return 'youtube';
        if (/tiktok\.com/i.test(u)) return 'tiktok';
        if (/instagram\.com/i.test(u)) return 'reels';
        if (/\.(mp4|webm|mov)/i.test(u)) return 'video';
        if (/\.(jpg|jpeg|png|webp|gif|avif|svg)/i.test(u)) return 'photo';
        return 'other';
    }
    const mediaType = _getTypeKey(url);

    div.className = 'media-card';
    div.dataset.sourceType  = st;
    div.dataset.sourceId    = si;
    div.dataset.url         = url;
    div.dataset.isVideo     = isVideo ? '1' : '0';
    div.dataset.likeCount   = likes;
    div.dataset.hasLiked    = liked ? '1' : '0';
    div.dataset.commentCount= comCount;
    div.dataset.viewCount   = views;
    div.dataset.title       = title;
    div.dataset.author      = author;
    div.dataset.avatar      = avatar;
    div.dataset.mediaType   = mediaType;
    div.dataset.viewed      = '0';
    // Apply active filters to newly loaded cards
    if (_activeAuthor && author !== _activeAuthor) div.classList.add('filtered-out');
    if (_activeType && mediaType !== _activeType) div.classList.add('filtered-out');
    div.setAttribute('onclick', 'openViewer(this)');
    div.setAttribute('ondblclick', 'doubleTapLike(event, this)');

    const nameParts = (author || 'Membro').trim().split(' ');
    const lastName = nameParts[nameParts.length - 1];
    
    div.innerHTML = `
        <div class="media-card__image-container" onclick="openViewer(this.closest('.media-card'))" ondblclick="doubleTapLike(event, this.closest('.media-card'))">
            ${thumb ? `<img class="media-card__thumb" src="${escapeHtml(thumb)}" alt="${title}" loading="lazy" onerror="thumbFallback(this)">` : ''}

            <div class="thumb-fallback" style="${thumb ? 'display:none;' : 'display:flex;'} align-items: center; justify-content: center; flex-direction: column;">
                ${avatar ? `<div class="thumb-fallback__bg" style="background-image:url('${escapeHtml(avatar)}')"></div>
                <img class="thumb-fallback__avatar" src="${escapeHtml(avatar)}" alt="${escapeHtml(author)}" onerror="this.style.display='none'">` : `<div class="thumb-fallback__bg thumb-fallback__bg--gradient"></div>`}
                <div class="thumb-fallback__play">play_circle</div>
                <div class="thumb-fallback__name">${escapeHtml(author)}</div>
            </div>

            <div class="media-card__gradient"></div>
            <div class="media-card__top">
                <div class="media-type-badge">
                    <span class="material-icons-round">${{youtube:'smart_display',tiktok:'music_video',reels:'photo_camera',video:'videocam',photo:'image'}[mediaType]||'perm_media'}</span>
                    ${{youtube:'YouTube',tiktok:'TikTok',reels:'Reels',video:'Vídeo',photo:'Foto'}[mediaType]||'Mídia'}
                </div>
            </div>
        </div>
        <div class="media-card__body">
            <div class="media-card__meta">
                <div class="media-card__author">
                    ${avatar ? `<img src="${escapeHtml(avatar)}" alt="">` : ''}
                    <span>${author}</span>
                </div>
                ${views > 0 ? `<div class="stat-chip" style="padding: 2px 8px; font-size: 0.7rem;"><span class="material-icons-round" style="font-size: 14px;">visibility</span>${views}</div>` : ''}
            </div>
            <div class="media-card__title" onclick="openViewer(this.closest('.media-card'))">${title}</div>
            <div class="social-interaction-bar">
                <div class="interaction-item ${liked ? 'liked' : ''}" id="cardLike_${st}_${si}" onclick="handleLike('${st}', ${si})">
                    <span class="material-icons-round">${liked ? 'favorite' : 'favorite_border'}</span>
                    <span id="cardLikeCount_${st}_${si}">${likes > 0 ? likes : ''}</span>
                </div>
                <div class="interaction-item" onclick="openViewer(this.closest('.media-card'), true)">
                    <span class="material-icons-round">chat_bubble_outline</span>
                    <span>${comCount > 0 ? comCount : ''}</span>
                </div>
                <div class="interaction-item" onclick="shareMedia(window.location.href, '${title}')">
                    <span class="material-icons-round">share</span>
                </div>
            </div>
        </div>`;
    return div;
}

function buildThumbJs(item) {
    const url = item.media_content || '';
    if (item.thumbnail_url) {
        if (item.thumbnail_url.indexOf('uploads/thumbnails/') !== -1 && item.thumbnail_url.indexOf('http') !== 0) {
            return '<?= base_url("/") ?>' + item.thumbnail_url;
        }
        return item.thumbnail_url;
    }
    const yt = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([^"&?\/\s]{11})/i);
    if (yt) return `https://img.youtube.com/vi/${yt[1]}/hqdefault.jpg`;
    return ''; // Empty triggers avatar blur fallback
}

// ── Double-tap like ───────────────────────────────────────────────────────────
function doubleTapLike(e, card) {
    e.stopPropagation();
    if (!card.dataset.hasLiked || card.dataset.hasLiked === '0') {
        handleLike(card.dataset.sourceType, card.dataset.sourceId);
    }
    // Show floating heart
    const heart  = document.createElement('span');
    heart.className = 'heart-pop material-icons-round';
    heart.textContent = 'favorite';
    heart.style.cssText = `left:${e.clientX - card.getBoundingClientRect().left}px;top:${e.clientY - card.getBoundingClientRect().top}px;color:#f43f5e;position:absolute;z-index:100;font-size:5rem;pointer-events:none;`;
    heart.style.animation = 'heartPop 0.7s ease forwards';
    card.appendChild(heart);
    setTimeout(() => heart.remove(), 750);
}

// ── Utilities ─────────────────────────────────────────────────────────────────
function shareMedia(url, title) {
    const shareData = { title: title || 'Clube de Desbravadores', url: url };
    if (navigator.share) {
        navigator.share(shareData).catch(() => {});
    } else {
        navigator.clipboard.writeText(url).then(() => {
            showToast('Link copiado!');
        });
    }
}

function showToast(msg) {
    const t = document.createElement('div');
    t.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:rgba(0,0,0,0.8);color:white;padding:10px 20px;border-radius:20px;font-size:0.85rem;z-index:99999;pointer-events:none;backdrop-filter:blur(8px);';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

// ── Viewer Navigation (prev/next + swipe + keyboard) ─────────────────────────
// Buttons live inside .viewer-panel — never overlap the iframe, no workarounds needed.
const viewerPrevBtn    = document.getElementById('viewerPrev');
const viewerNextBtn    = document.getElementById('viewerNext');
const viewerNavRow     = document.getElementById('viewerNavRow');
const viewerNavCounter = document.getElementById('viewerNavCounter');

if (viewerPrevBtn) viewerPrevBtn.addEventListener('click', () => navigateViewer(-1));
if (viewerNextBtn) viewerNextBtn.addEventListener('click', () => navigateViewer(1));

function getAllCards() {
    return Array.from(document.querySelectorAll('#mediaGrid .media-card:not(.filtered-out)'));
}

function navigateViewer(direction) {
    if (!currentViewerCard) return;
    const cards = getAllCards();
    const idx   = cards.indexOf(currentViewerCard);
    const next  = cards[idx + direction];
    if (next) openViewer(next);
}

function updateNavButtons() {
    if (!currentViewerCard) return;
    const cards = getAllCards();
    const idx   = cards.indexOf(currentViewerCard);
    if (viewerPrevBtn) viewerPrevBtn.disabled = idx <= 0;
    if (viewerNextBtn) viewerNextBtn.disabled = idx >= cards.length - 1;
    if (viewerNavCounter) viewerNavCounter.textContent = `${idx + 1} / ${cards.length}`;
}

// Show/hide nav row with the viewer
const _origOpenViewer  = openViewer;
const _origCloseViewer = closeViewer;
openViewer = function(card, forceComments) {
    _origOpenViewer(card, forceComments);
    if (viewerNavRow) viewerNavRow.classList.add('visible');
    updateNavButtons();
};
closeViewer = function() {
    _origCloseViewer();
    if (viewerNavRow) viewerNavRow.classList.remove('visible');
};

// Keyboard arrow navigation
document.addEventListener('keydown', e => {
    if (!viewer?.classList.contains('open')) return;
    if (e.key === 'ArrowRight') navigateViewer(1);
    if (e.key === 'ArrowLeft')  navigateViewer(-1);
});

// Swipe gesture detection (on the viewer backdrop — iframes don't block this area)
let _swX = 0, _swY = 0;
viewer?.addEventListener('touchstart', e => {
    _swX = e.touches[0].clientX;
    _swY = e.touches[0].clientY;
}, { passive: true });
viewer?.addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - _swX;
    const dy = e.changedTouches[0].clientY - _swY;
    if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > 55) {
        // Horizontal swipe: left=next, right=prev
        navigateViewer(dx < 0 ? 1 : -1);
    }
}, { passive: true });

// ── Story Bubbles — Filter by author ─────────────────────────────────────────
let _activeAuthor = null;
let _activeType   = '';

function _applyFilters() {
    document.querySelectorAll('#mediaGrid .media-card').forEach(card => {
        const authorOk = !_activeAuthor || card.dataset.author === _activeAuthor;
        const typeOk   = !_activeType   || card.dataset.mediaType === _activeType;
        card.classList.toggle('filtered-out', !(authorOk && typeOk));
    });
}

function filterByAuthor(authorName, btn) {
    _activeAuthor = authorName;
    document.querySelectorAll('.story-bubble').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    _applyFilters();
}

function filterByType(typeKey, btn) {
    _activeType = typeKey;
    document.querySelectorAll('.type-filter-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    _applyFilters();
}

// ── Sort cards ────────────────────────────────────────────────────────────────
function sortCards(mode, btn) {
    document.querySelectorAll('#btnSortRecent, #btnSortLikes, #btnSortViews').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const grid     = document.getElementById('mediaGrid');
    const sentinel = document.getElementById('infiniteScrollSentinel');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.media-card'));
    // Ensure all cards have an origIdx (dynamically loaded cards may not have one)
    let _maxIdx = cards.reduce((m, c) => Math.max(m, parseInt(c.dataset.origIdx) || 0), -1);
    cards.forEach(c => { if (!c.dataset.origIdx) c.dataset.origIdx = ++_maxIdx; });

    cards.sort((a, b) => {
        if (mode === 'likes') return (parseInt(b.dataset.likeCount)||0) - (parseInt(a.dataset.likeCount)||0);
        if (mode === 'views') return (parseInt(b.dataset.viewCount)||0) - (parseInt(a.dataset.viewCount)||0);
        // 'recent': restore original server order
        return (parseInt(a.dataset.origIdx)||0) - (parseInt(b.dataset.origIdx)||0);
    });
    cards.forEach(c => grid.insertBefore(c, sentinel || null));
}

// ── Feed Mode vs Grid Mode ────────────────────────────────────────────────────
let _currentViewMode = 'grid';

function setViewMode(mode) {
    _currentViewMode = mode;
    const g = document.getElementById('mediaGrid');
    if (!g) return;

    if (mode === 'feed') {
        g.classList.add('feed-mode');
        document.getElementById('btnGridMode')?.classList.remove('active');
        document.getElementById('btnFeedMode')?.classList.add('active');
    } else {
        g.classList.remove('feed-mode');
        document.getElementById('btnGridMode')?.classList.add('active');
        document.getElementById('btnFeedMode')?.classList.remove('active');
    }
}

// ── Download button in viewer ─────────────────────────────────────────────────
// Shown only for storage/ URLs (native content, not external embeds)
function _viewerDownloadBtn(url, title) {
    const isStorage = url && (url.startsWith('storage/') || url.startsWith('/storage/'));
    if (!isStorage) return '';
    const src = url.startsWith('/') ? url : '/' + url;
    return `<a class="viewer-action-btn" href="${src}" download="${escapeHtml(title || 'midia')}"
              title="Baixar" style="text-decoration:none;">
        <span class="material-icons-round">download</span>
        <span></span>
    </a>`;
}

function formatDate(str) {
    if (!str) return '';
    try {
        return new Date(str).toLocaleDateString('pt-BR', { day:'2-digit', month:'2-digit', year:'numeric' });
    } catch(e) { return str; }
}

// ── Lead Modal ────────────────────────────────────────────────────────────────
const LEAD_URL = '<?= base_url('c/' . $clubSlug . '/lead') ?>';

function openLeadModal() {
    const m = document.getElementById('leadModal');
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLeadModal() {
    const m = document.getElementById('leadModal');
    m.style.display = 'none';
    document.body.style.overflow = '';
}
document.getElementById('leadModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeLeadModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLeadModal();
});

async function submitLead(e) {
    e.preventDefault();
    const form = e.target;
    const btn  = document.getElementById('leadSubmitBtn');
    btn.disabled = true;
    btn.textContent = 'Enviando...';

    try {
        const res  = await fetch(LEAD_URL, { method: 'POST', body: new FormData(form) });
        const data = await res.json();
        if (data.success) {
            document.getElementById('leadForm').style.display    = 'none';
            document.getElementById('leadSuccess').style.display = 'block';
            document.getElementById('leadSuccessMsg').textContent = data.message;
            setTimeout(closeLeadModal, 4000);
        } else {
            alert(data.error || 'Erro ao enviar. Tente novamente.');
            btn.disabled = false;
            btn.textContent = 'Enviar Interesse';
        }
    } catch(ex) {
        alert('Erro de conexão.');
        btn.disabled = false;
        btn.textContent = 'Enviar Interesse';
    }
}

// ── Reactions ─────────────────────────────────────────────────────────────────
const REACTION_EMOJIS = {like:'❤️',love:'😍',haha:'😂',wow:'😮',clap:'👏',fire:'🔥'};
const _reactUrl = '<?= base_url('c/' . $clubSlug . '/react') ?>';
let _holdTimer  = null;

function toggleReactionPicker(st, si) {
    const picker = document.getElementById(`rxPicker_${st}_${si}`);
    if (!picker) return;
    document.querySelectorAll('.reaction-picker.open').forEach(p => { if (p !== picker) p.classList.remove('open'); });
    picker.classList.toggle('open');
}
function startReactionHold(st, si) {
    _holdTimer = setTimeout(() => { toggleReactionPicker(st, si); _holdTimer = null; }, 500);
}
function clearReactionHold() { if (_holdTimer) { clearTimeout(_holdTimer); _holdTimer = null; } }

// Close open pickers when clicking outside
document.addEventListener('click', e => {
    if (!e.target.closest('.reaction-picker-wrap')) {
        document.querySelectorAll('.reaction-picker.open').forEach(p => p.classList.remove('open'));
    }
});

async function sendReaction(st, si, type, btn) {
    const picker = btn.closest('.reaction-picker');
    if (picker) picker.classList.remove('open');

    try {
        const res  = await fetch(_reactUrl, {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `source_type=${encodeURIComponent(st)}&source_id=${encodeURIComponent(si)}&reaction_type=${encodeURIComponent(type)}`
        });
        const data = await res.json();
        if (!data.success) return;

        // Update card data-my-reaction
        const card = document.getElementById(`rxWrap_${st}_${si}`)?.closest('.media-card');
        if (card) card.dataset.myReaction = data.my_reaction || '';

        // Update picker button states
        const pickerEl = document.getElementById(`rxPicker_${st}_${si}`);
        if (pickerEl) pickerEl.querySelectorAll('.reaction-btn').forEach(b => {
            b.classList.toggle('my-reaction', b.title === data.my_reaction);
        });

        // Update reaction summary
        const summary = document.getElementById(`rxSummary_${st}_${si}`);
        if (summary) {
            const top3 = (data.counts || []).slice(0, 3);
            summary.innerHTML = top3.map(r => `<span title="${r.cnt}">${REACTION_EMOJIS[r.reaction_type]||''}</span>`).join('');
        }

        // Also update viewer if open
        const viewerReactions = document.getElementById('viewerReactions');
        if (viewerReactions && document.getElementById('mediaViewer')?.classList.contains('open')) {
            const top3 = (data.counts || []).slice(0, 3);
            viewerReactions.innerHTML = top3.map(r => `<span class="viewer-reaction-chip">${REACTION_EMOJIS[r.reaction_type]||''} <small>${r.cnt}</small></span>`).join('');
        }
    } catch(e) { /* silent */ }
}

// ── Event Date Range Filter ───────────────────────────────────────────────────
let _activeAlbum = null;
function filterByDateRange(from, to, albumCard) {
    if (_activeAlbum === albumCard && albumCard.classList.contains('active')) {
        // Toggle off
        _activeAlbum = null;
        albumCard.classList.remove('active');
        window._activeDateFrom = null;
        window._activeDateTo   = null;
        _applyFilters();
        return;
    }
    document.querySelectorAll('.event-album-card').forEach(c => c.classList.remove('active'));
    albumCard.classList.add('active');
    _activeAlbum          = albumCard;
    window._activeDateFrom = new Date(from).getTime();
    window._activeDateTo   = new Date(to).getTime();
    _applyFilters();
}

// Patch _applyFilters to also filter by date range
const _origApplyFilters = _applyFilters;
function _applyFilters() {
    document.querySelectorAll('#mediaGrid .media-card').forEach(card => {
        const authorOk = !_activeAuthor || card.dataset.author === _activeAuthor;
        const typeOk   = !_activeType   || card.dataset.mediaType === _activeType;
        let dateOk = true;
        if (window._activeDateFrom && card.dataset.date) {
            const ts = new Date(card.dataset.date).getTime();
            dateOk = ts >= window._activeDateFrom && ts <= window._activeDateTo;
        }
        card.classList.toggle('filtered-out', !(authorOk && typeOk && dateOk));
    });
}

// ── TikTok Overlay ────────────────────────────────────────────────────────────
const tikTokOverlay = document.getElementById('tikTokOverlay');
const tikTokClose   = document.getElementById('tikTokClose');

function enterTikTokMode() {
    if (!tikTokOverlay) return;
    const cards = getAllCards();
    tikTokOverlay.innerHTML = '';

    cards.forEach((card, idx) => {
        const url        = card.dataset.url || '';
        const title      = card.dataset.title || '';
        const author     = card.dataset.author || '';
        const avatar     = card.dataset.avatar || '';
        const st         = card.dataset.sourceType;
        const si         = card.dataset.sourceId;
        const liked      = card.dataset.hasLiked === '1';
        const likes      = parseInt(card.dataset.likeCount) || 0;
        const mediaType  = card.dataset.mediaType || '';
        const typeIcons  = {youtube:'smart_display',tiktok:'music_video',reels:'photo_camera',video:'videocam',photo:'image'};
        const typeLabels = {youtube:'YouTube',tiktok:'TikTok',reels:'Reels',video:'Vídeo',photo:'Foto'};

        // Get thumbnail from child img
        const imgEl   = card.querySelector('.media-card__thumb');
        const thumbSrc = imgEl?.src || '';

        const ttCard = document.createElement('div');
        ttCard.className = 'tt-card';
        ttCard.dataset.idx = idx;
        ttCard.innerHTML = `
            <div class="tt-card__bg">
                ${thumbSrc ? `<img class="tt-card__thumb" src="${escapeHtml(thumbSrc)}" alt="${escapeHtml(title)}" loading="lazy">` : ''}
            </div>
            <div class="tt-card__gradient"></div>
            <div class="tt-type-badge">
                <span class="material-icons-round">${typeIcons[mediaType]||'perm_media'}</span>
                ${typeLabels[mediaType]||'Mídia'}
            </div>
            <div class="tt-card__info">
                <div class="tt-card__title">${escapeHtml(title)}</div>
                <div class="tt-card__author">
                    ${avatar ? `<img src="${escapeHtml(avatar)}" alt="">` : ''}
                    <span>${escapeHtml(author)}</span>
                </div>
            </div>
            <div class="tt-card__side">
                <button class="tt-side-btn ${liked?'liked':''}" id="ttLike_${st}_${si}"
                        onclick="handleLike('${st}',${si}); this.classList.toggle('liked')">
                    <span class="material-icons-round">${liked?'favorite':'favorite_border'}</span>
                    <span id="ttLikeCount_${st}_${si}">${likes||''}</span>
                </button>
                <button class="tt-side-btn" onclick="openViewer(document.querySelector('.media-card[data-source-type=\\'${st}\\'][data-source-id=\\'${si}\\']'), true)">
                    <span class="material-icons-round">chat_bubble_outline</span>
                    <span></span>
                </button>
                <button class="tt-side-btn" onclick="shareMedia(window.location.href, '${escapeHtml(title).replace(/'/g,"\\'")}')" >
                    <span class="material-icons-round">share</span>
                    <span></span>
                </button>
                <button class="tt-side-btn" onclick="openViewer(document.querySelector('.media-card[data-source-type=\\'${st}\\'][data-source-id=\\'${si}\\']'))">
                    <span class="material-icons-round">open_in_full</span>
                    <span></span>
                </button>
            </div>`;
        tikTokOverlay.appendChild(ttCard);
    });

    tikTokOverlay.classList.add('open');
    tikTokClose.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function exitTikTokMode() {
    tikTokOverlay?.classList.remove('open');
    tikTokClose?.classList.remove('open');
    tikTokOverlay.innerHTML = '';
    document.body.style.overflow = '';
}

tikTokClose?.addEventListener('click', exitTikTokMode);

// ── Service Worker Registration ───────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => { /* offline */ });
    });
}

// ── PWA Install Banner ────────────────────────────────────────────────────────
let _deferredPrompt = null;
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    _deferredPrompt = e;
    setTimeout(() => document.getElementById('pwaInstallBanner')?.classList.add('visible'), 3000);
});

function pwaInstall() {
    if (!_deferredPrompt) return;
    _deferredPrompt.prompt();
    _deferredPrompt.userChoice.then(() => {
        _deferredPrompt = null;
        document.getElementById('pwaInstallBanner')?.classList.remove('visible');
    });
}
function pwaDismiss() {
    document.getElementById('pwaInstallBanner')?.classList.remove('visible');
}
</script>

<!-- TikTok fullscreen overlay -->
<div id="tikTokOverlay"></div>
<button id="tikTokClose" aria-label="Fechar modo TikTok">
    <span class="material-icons-round">close</span>
</button>

<!-- PWA Install Banner -->
<div id="pwaInstallBanner" role="banner">
    <span class="material-icons-round">install_mobile</span>
    <div class="pwa-banner-text">
        <strong>Adicionar à tela inicial</strong>
        <span>Acesse o clube sem abrir o navegador</span>
    </div>
    <button class="pwa-install-btn" onclick="pwaInstall()">Instalar</button>
    <button class="pwa-dismiss-btn" onclick="pwaDismiss()" aria-label="Fechar">
        <span class="material-icons-round">close</span>
    </button>
</div>
