<?php
/**
 * Media Helper Functions
 * 
 * Provides robust functions for automatically embedding media (YouTube, Instagram, TikTok)
 * from URLs provided by users in structured questions and proof submissions.
 */

/**
 * Automatically converts a media URL (YouTube, Instagram, TikTok) into the 
 * appropriate HTML embed code (iframe, blockquote, script).
 * If the URL is not from a supported media platform, it returns a safe clickable link.
 * 
 * @param string $url The string to be analyzed and embedded.
 * @return string HTML code for the embed or a clickable anchor link.
 */
function embed_media(string $url): string
{
    // Basic sanitization
    $url = trim($url);
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return htmlspecialchars($url);
    }

    // ---------------------------------------------------------
    // 1. YouTube
    // Matches: youtube.com/watch?v=ID, youtu.be/ID, youtube.com/embed/ID, youtube.com/shorts/ID, youtube.com/live/ID
    // ---------------------------------------------------------
    $youtubePattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/ ]{11})/i';
    if (preg_match($youtubePattern, $url, $matches)) {
        $videoId = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
        return '<div class="yt-embed-compact" style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; border-radius: 12px; margin-top: 8px;">
                    <iframe src="https://www.youtube.com/embed/' . $videoId . '" 
                            title="YouTube video player" 
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 0;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            referrerpolicy="strict-origin-when-cross-origin" 
                            allowfullscreen>
                    </iframe>
                </div>';
    }

    // ---------------------------------------------------------
    // 2. Instagram
    // Matches: instagram.com/reel/ID, instagram.com/p/ID, instagram.com/tv/ID
    // Cleans up tracking parameters (utm_source, igsh, etc) to ensure the embed works cleanly.
    // ---------------------------------------------------------
    $instagramPattern = '/(?:https?:\/\/)?(?:www\.)?instagram\.com\/(?:p|reel|tv)\/([A-Za-z0-9_-]+)/i';
    if (preg_match($instagramPattern, $url, $matches)) {
        $postId = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
        // Construct a clean, canonical URL for the embed, stripping any existing query params
        $cleanUrl = "https://www.instagram.com/reel/{$postId}/?utm_source=ig_embed&utm_campaign=loading";
        
        return '<div class="social-embed-compact" style="margin-top: 8px; display: flex; justify-content: center; width: 100%;">
                    <blockquote class="instagram-media" data-instgrm-permalink="' . $cleanUrl . '" data-instgrm-version="14" 
                                style="background:#FFF; border:0; border-radius:3px; box-shadow:0 0 1px 0 rgba(0,0,0,0.5),0 1px 10px 0 rgba(0,0,0,0.15); margin: 1px; max-width:540px; min-width:326px; padding:0; width:99.375%; width:-webkit-calc(100% - 2px); width:calc(100% - 2px);">
                    </blockquote>
                    <script async src="//www.instagram.com/embed.js"></script>
                </div>';
    }

    // ---------------------------------------------------------
    // 3. TikTok
    // Matches: tiktok.com/@user/video/ID, vm.tiktok.com/ID
    // Uses the raw URL but extracts the video ID for semantic attributes.
    // ---------------------------------------------------------
    // Mobile specific (vm.tiktok.com) or standard web format
    // Standard format regex capturing ID:
    $tiktokPattern = '/(?:https?:\/\/)?(?:www\.)?tiktok\.com\/.*\/video\/([0-9]+)/i';
    if (preg_match($tiktokPattern, $url, $matches)) {
        $videoId = htmlspecialchars($matches[1], ENT_QUOTES, 'UTF-8');
        // Extract the base URL without query parameters for the `cite` attribute
        $cleanUrl = strtok($url, '?');
        $cleanUrl = htmlspecialchars($cleanUrl, ENT_QUOTES, 'UTF-8');

        return '<div class="social-embed-compact" style="margin-top: 8px; display: flex; justify-content: center; width: 100%;">
                    <blockquote class="tiktok-embed" cite="' . $cleanUrl . '" data-video-id="' . $videoId . '" 
                                style="max-width: 605px; min-width: 325px; margin: 0;"> 
                        <section> </section> 
                    </blockquote> 
                    <script async src="https://www.tiktok.com/embed.js"></script>
                </div>';
    }
    
    // Check for short mobile format vm.tiktok.com (usually redirects, but we try to embed it as a link since we don't have the ID)
    if (strpos($url, 'vm.tiktok.com') !== false || strpos($url, 'vt.tiktok.com') !== false) {
          return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" style="color: var(--accent-primary);"><iconify-icon icon="logos:tiktok-icon"></iconify-icon> Ver vídeo no TikTok</a>';
    }


    // ---------------------------------------------------------
    // 4. Default Fallback
    // For normal texts or URLs not recognized as supported media
    // ---------------------------------------------------------
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    return '<a href="' . $safeUrl . '" target="_blank" style="color: var(--accent-primary);"><iconify-icon icon="solar:link-circle-bold-duotone"></iconify-icon> ' . $safeUrl . '</a>';
}

/**
 * Fetches the original high-resolution thumbnail for a social media URL.
 * Supports YouTube, TikTok, and Instagram (via oEmbed or fallback).
 * 
 * @param string $url The media URL.
 * @return string|null The thumbnail URL or null if not found.
 */
function fetch_media_thumbnail(string $url): ?string
{
    $url = trim($url);
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) return null;

    // 1. YouTube
    $youtubePattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/ ]{11})/i';
    if (preg_match($youtubePattern, $url, $matches)) {
        return "https://img.youtube.com/vi/{$matches[1]}/hqdefault.jpg";
    }

    // 2. TikTok (Public oEmbed)
    if (str_contains($url, 'tiktok.com')) {
        try {
            $oembedUrl = "https://www.tiktok.com/oembed?url=" . urlencode($url);
            $ctx = stream_context_create(['http' => ['timeout' => 5]]);
            $response = @file_get_contents($oembedUrl, false, $ctx);
            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data['thumbnail_url'])) {
                    return $data['thumbnail_url'];
                }
            }
        } catch (\Exception $e) {
            // Log or ignore
        }
        return 'https://www.google.com/s2/favicons?sz=128&domain=tiktok.com';
    }

    // 3. Instagram (Direct thumb hacks are blocked, use brand icon as high-res fallback)
    if (str_contains($url, 'instagram.com')) {
        return 'https://www.google.com/s2/favicons?sz=128&domain=instagram.com';
    }

    // 4. Direct Video File (.mp4)
    if (str_ends_with(strtolower($url), '.mp4')) {
        return 'https://placehold.co/600x600/1e293b/white?text=Video';
    }

    return null;
}
