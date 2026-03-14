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

.viewer-float-close {
    position: fixed;
    top: 16px;
    right: 16px;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.65);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 99999;
    touch-action: manipulation;
    -webkit-tap-highlight-color: transparent;
    transition: background 0.2s, transform 0.2s;
}

.viewer-float-close:hover {
    background: rgba(239, 68, 68, 0.8);
    transform: rotate(90deg);
}

.viewer-float-close .material-icons-round { font-size: 22px; }

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
        height: 100dvh;
        width: 100%; max-width: 100%;
        margin: 0;
        overflow: hidden;
        position: relative;
    }
    /* Vídeo ocupa toda a altura disponível */
    .viewer-media {
        flex: 1;
        min-height: 0;
        padding: 0;
        background: #000;
        display: flex;
        align-items: stretch;
        justify-content: center;
        overflow: hidden;
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
    /* Painel: barra de ações fixada na parte inferior */
    .viewer-panel {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        width: 100%; height: auto;
        border-left: none;
        border-top: 1px solid rgba(0, 204, 255, 0.12);
        background: rgba(7, 9, 15, 0.88);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        z-index: 20;
        flex-direction: column;
        flex-shrink: 0;
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

/* Embed containers */
.embed-wrap { position: relative; border-radius: 16px; overflow: hidden; }
.embed-wrap.landscape { width: 100%; aspect-ratio: 16/9; }
.embed-wrap.portrait  { aspect-ratio: 9/16; height: min(80vh, 600px); width: auto; margin: 0 auto; }
.embed-wrap iframe    { position: absolute; inset: 0; width: 100%; height: 100%; border: none; }
.embed-wrap.twitter-embed { aspect-ratio: unset; overflow: visible; width: min(550px, 100%); margin: 0 auto; box-shadow: none; background: transparent; border-radius: 0; }
.embed-wrap.twitter-embed .twitter-tweet { margin: 0 auto !important; }
/* Social embeds (Instagram, TikTok) — dimensionadas pelo SDK da plataforma */
.embed-wrap.social-embed-wrap { aspect-ratio: unset; overflow-y: auto; width: 100%; max-width: 605px; margin: 0 auto; box-shadow: none; background: transparent; border-radius: 0; display: flex; justify-content: center; }

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

    /* Reel feed */
    .hub-feed { position: relative; }
    .feed-toolbar { display: none; }

    /* Feed layout - simplified for modern look */
    .media-grid {
        display: flex;
        flex-direction: column;
        gap: 32px;
        padding: 16px 16px;
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
</style>

<?php
// ── Helper: build thumbnail for a media item ─────────────────────────────────
function buildThumb(array $media): string {
    $url   = trim((string)($media['media_content'] ?? ''));
    $thumb = $media['thumbnail_url'] ?? '';
    
    // Check if thumbnail is just a platform favicon
    if (!empty($thumb) && strpos($thumb, 'favicon') !== false && strpos($thumb, 'google.com/s2/favicons') !== false) {
        $thumb = '';
    }

    if (!empty($thumb)) return $thumb;

    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $m)) {
        return 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
    }
    if (strpos($url, 'instagram.com') !== false) {
        return base_url('/assets/images/video_placeholder.png');
    }
    if (strpos($url, 'tiktok.com') !== false) {
        return base_url('/assets/images/video_placeholder.png');
    }
    if (preg_match('/\.(jpg|jpeg|png|webp|gif|avif|svg)/i', $url)) {
        return strpos($url, 'storage/') === 0 ? base_url('/' . $url) : $url;
    }
    if (strpos($url, 'storage/') === 0) return base_url('/' . $url);
    return base_url('/assets/images/video_placeholder.png');
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
    <main class="hub-feed">
        <div class="feed-toolbar">
            <h2 class="feed-title">
                <span class="material-icons-round">auto_awesome</span>
                Galeria do Clube
            </h2>
        </div>

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
                $content  = htmlspecialchars($media['media_content'] ?? '', ENT_QUOTES);
                $title    = htmlspecialchars($media['title'] ?? '');
                $author   = htmlspecialchars($media['user_name'] ?? '');
                $avatar   = htmlspecialchars($media['avatar_url'] ?? '');
            ?>
            <div class="media-card"
                 data-source-type="<?= $st ?>"
                 data-source-id="<?= $si ?>"
                 data-url="<?= $content ?>"
                 data-is-video="<?= $isVid ? '1' : '0' ?>"
                 data-like-count="<?= $likes ?>"
                 data-has-liked="<?= $liked ? '1' : '0' ?>"
                 data-comment-count="<?= $comCount ?>"
                 data-title="<?= $title ?>"
                 data-viewed="0">

                <div class="media-card__image-container" onclick="openViewer(this.closest('.media-card'))" ondblclick="doubleTapLike(event, this.closest('.media-card'))">
                    <?php if ($thumb): ?>
                    <img class="media-card__thumb" src="<?= htmlspecialchars($thumb) ?>" alt="<?= $title ?>" loading="lazy">
                    <?php else: ?>
                    <div class="media-card__thumb-placeholder"></div>
                    <?php endif; ?>
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
                        <div class="interaction-item <?= $liked ? 'liked' : '' ?>" 
                             id="cardLike_<?= $st ?>_<?= $si ?>"
                             onclick="handleLike('<?= $st ?>', <?= $si ?>)">
                            <span class="material-icons-round"><?= $liked ? 'favorite' : 'favorite_border' ?></span>
                            <span id="cardLikeCount_<?= $st ?>_<?= $si ?>"><?= $likes > 0 ? $likes : '' ?></span>
                        </div>
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

<!-- Botão de fechar FORA do modal para evitar que o iframe capture o evento -->
<button id="viewerFloatClose" class="viewer-float-close" onclick="closeViewer()" aria-label="Fechar" style="display:none;">
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
/* Detectar quais SDKs são necessários para pré-carregar */
$_needsIgSdk = false;
$_needsTtSdk = false;
foreach ($curatedMedia as $_m) {
    $_u = trim($_m['media_content'] ?? '');
    if (strpos($_u, 'instagram.com') !== false) $_needsIgSdk = true;
    if (strpos($_u, 'tiktok.com') !== false)    $_needsTtSdk = true;
}
?>
<?php if ($_needsIgSdk): ?>
<script async id="instagram-embed-js" src="//www.instagram.com/embed.js"></script>
<?php endif; ?>
<?php if ($_needsTtSdk): ?>
<script async id="tiktok-embed-js" src="https://www.tiktok.com/embed.js"></script>
<?php endif; ?>

<script>

// ── Constants ────────────────────────────────────────────────────────────────
const LIKE_URL     = '<?= $likeUrl ?>';
const MEDIA_URL    = '<?= $mediaApiUrl ?>';
const COMMENT_BASE = '<?= $commentBase ?>';
const CLUB_SLUG    = '<?= htmlspecialchars($clubSlug, ENT_QUOTES) ?>';

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

function buildEmbedHtml(url, isVideo) {
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

    // ── TikTok (Official Embed System) ───────────────────────────────
    if (ttMatch) {
        const videoId = ttMatch[1];
        // Loading official blockquote for better compliance and features
        EmbedLoader.load('https://www.tiktok.com/embed.js', 'tiktok-embed-js')
            .then(() => { if (window.tiktok) window.tiktok.Embeds?.render?.(); });
            
        return `<div class="embed-wrap portrait" style="background:#000;">
            <blockquote class="tiktok-embed" cite="${url}" data-video-id="${videoId}" style="max-width: 605px;min-width: 325px;" >
                <section> <a target="_blank" title="@tiktok" href="https://www.tiktok.com/video/${videoId}">Carregando vídeo...</a> </section>
            </blockquote>
        </div>`;
    }

    // ── Instagram (blockquote + SDK oficial) ──────────────────────────
    if (igMatch) {
        const postType = igMatch[1].toLowerCase() === 'reels' ? 'reel' : igMatch[1];
        const postId   = igMatch[2];
        const igUrl    = `https://www.instagram.com/${postType}/${postId}/?utm_source=ig_embed&utm_campaign=loading`;
        
        EmbedLoader.load('https://www.instagram.com/embed.js', 'instagram-embed-js')
            .then(() => { if (window.instgrm) window.instgrm.Embeds.process(); });

        return `<div class="embed-wrap social-embed-wrap">
            <blockquote class="instagram-media" data-instgrm-permalink="${igUrl}" data-instgrm-version="14"
                style="background:#FFF;border:0;border-radius:12px;box-shadow:0 0 1px 0 rgba(0,0,0,.5),0 1px 10px 0 rgba(0,0,0,.15);margin:1px;max-width:540px;min-width:326px;padding:0;width:99.375%;width:-webkit-calc(100% - 2px);width:calc(100% - 2px);">
                <div style="padding:16px;"> <a href="${url}" target="_blank" style="color:#000;text-decoration:none;font-family:Sans-Serif;font-size:14px;font-style:normal;font-weight:normal;line-height:17px;">Ver no Instagram</a> </div>
            </blockquote>
        </div>`;
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

function openViewer(card, forceComments = false) {
    currentViewerCard = card;
    const url      = card.dataset.url;
    const isVideo  = card.dataset.isVideo === '1';
    const st       = card.dataset.sourceType;
    const si       = card.dataset.sourceId;
    const liked    = card.dataset.hasLiked === '1';
    const likes    = parseInt(card.dataset.likeCount) || 0;
    const title    = card.dataset.title;

    // Build embed
    viewerMedia.innerHTML = buildEmbedHtml(url, isVideo);

    // Update title
    document.getElementById('viewerTitle').innerHTML = `
        <span class="material-icons-round">${isVideo ? 'play_circle' : 'image'}</span>
        ${title || 'Mídia'}
    `;

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
        </button>`;

    // Load comments
    loadComments(st, si);

    // Reset form
    commentForm.dataset.sourceType = st;
    commentForm.dataset.sourceId   = si;
    commentForm.reset();

    viewer.classList.add('open');
    document.body.style.overflow = 'hidden';
    floatClose.style.display = 'flex';

    // Track view
    trackView(st, si);

    // If mobile, check if we should show comments full screen
    if (window.innerWidth <= 768 && forceComments) {
        viewer.classList.add('showing-comments');
    } else {
        viewer.classList.remove('showing-comments');
    }

    // Re-processar Instagram SDK: tenta imediatamente + retry se ainda carregando
    const _igProcess = () => {
        if (window.instgrm) {
            window.instgrm.Embeds.process();
        } else {
            // SDK ainda carregando — aguarda mais 800ms e tenta novamente
            setTimeout(() => { if (window.instgrm) window.instgrm.Embeds.process(); }, 800);
        }
    };
    setTimeout(_igProcess, 100);
}

function closeViewer() {
    viewer.classList.remove('open');
    document.body.style.overflow = '';
    floatClose.style.display = 'none';
    viewerMedia.innerHTML = '';
    currentViewerCard = null;
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
if (grid) observeCards(grid);

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
    const thumb     = item.thumbnail_url || buildThumbJs(item);
    const title     = escapeHtml(item.title || '');
    const author    = escapeHtml(item.user_name || '');
    const avatar    = item.avatar_url || '';

    div.className = 'media-card';
    div.dataset.sourceType  = st;
    div.dataset.sourceId    = si;
    div.dataset.url         = url;
    div.dataset.isVideo     = isVideo ? '1' : '0';
    div.dataset.likeCount   = likes;
    div.dataset.hasLiked    = liked ? '1' : '0';
    div.dataset.commentCount= comCount;
    div.dataset.title       = title;
    div.dataset.viewed      = '0';
    div.setAttribute('onclick', 'openViewer(this)');
    div.setAttribute('ondblclick', 'doubleTapLike(event, this)');

    div.innerHTML = `
        <div class="media-card__image-container" onclick="openViewer(this.closest('.media-card'))">
            ${thumb ? `<img class="media-card__thumb" src="${escapeHtml(thumb)}" alt="${title}" loading="lazy">` : '<div class="media-card__thumb-placeholder"></div>'}
            <div class="media-card__gradient"></div>
            <div class="media-card__top">
                <div class="media-type-badge">
                    <span class="material-icons-round">${isVideo ? 'smart_display' : 'image'}</span>
                    ${isVideo ? 'Vídeo' : 'Foto'}
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
    if (item.thumbnail_url) return item.thumbnail_url;
    const yt = url.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([^"&?\/\s]{11})/i);
    if (yt) return `https://img.youtube.com/vi/${yt[1]}/hqdefault.jpg`;
    return '<?= base_url("assets/images/video_placeholder.png") ?>';
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
</script>
