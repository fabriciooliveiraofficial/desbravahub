<?php
/**
 * Media Helper Functions
 *
 * Provides robust functions for automatically embedding media
 * (YouTube, Instagram, X/Twitter, TikTok) from user-supplied URLs.
 *
 * SDK INJECTION PATTERN
 * ---------------------
 * embed_media() no longer inlines <script> tags.
 * Instead it registers SDKs via $GLOBALS['_media_sdk_registry'].
 * Call embed_media_sdk_scripts() once, just before </body>, to output
 * the consolidated <script> tags without duplication.
 */

/**
 * Convert a media URL into an HTML embed block.
 * Falls back to a safe anchor link for unrecognised URLs.
 *
 * @param string $url The URL to embed.
 * @return string HTML embed or anchor.
 */
function embed_media(string $url): string
{
    $url = trim($url);
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return htmlspecialchars($url);
    }

    // ------------------------------------------------------------------
    // 1. YouTube
    //    Matches: youtube.com, m.youtube.com, youtu.be
    //    Paths:   /watch?v=ID  /embed/ID  /shorts/ID  /live/ID  /v/ID
    // ------------------------------------------------------------------
    $ytPattern = '/(?:(?:(?:m|www)\.)?youtube\.com\/(?:[^\/\s]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i';
    if (preg_match($ytPattern, $url, $m)) {
        $videoId = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
        $isShort = (bool) preg_match('/\/shorts\//i', $url);

        if ($isShort) {
            // 9:16 portrait for Shorts, capped width
            $wrapStyle = 'position:relative;padding-bottom:177.78%;height:0;overflow:hidden;max-width:320px;border-radius:12px;margin-top:8px;';
        } else {
            // 16:9 landscape for standard videos
            $wrapStyle = 'position:relative;padding-bottom:56.25%;height:0;overflow:hidden;max-width:100%;border-radius:12px;margin-top:8px;';
        }

        return '<div class="yt-embed-compact" style="' . $wrapStyle . '">
                    <iframe src="https://www.youtube.com/embed/' . $videoId . '"
                            title="YouTube video player"
                            loading="lazy"
                            style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"
                            allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture;web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen>
                    </iframe>
                </div>';
    }

    // ------------------------------------------------------------------
    // 2. Instagram
    //    Matches: /p/ID  /reel/ID  /reels/ID  /tv/ID
    // ------------------------------------------------------------------
    $igPattern = '/(?:https?:\/\/)?(?:www\.)?instagram\.com\/(p|reel|reels|tv)\/([A-Za-z0-9_-]+)/i';
    if (preg_match($igPattern, $url, $m)) {
        $postType  = strtolower($m[1]);
        $postId    = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');

        // Normalise plural /reels/ → /reel/ for the canonical embed URL
        $embedPath = ($postType === 'reels') ? 'reel' : $postType;
        $cleanUrl  = "https://www.instagram.com/{$embedPath}/{$postId}/?utm_source=ig_embed&utm_campaign=loading";

        $GLOBALS['_media_sdk_registry']['instagram'] = true;

        return '<div class="social-embed-compact" style="margin-top:8px;display:flex;justify-content:center;width:100%;">
                    <blockquote class="instagram-media"
                                data-instgrm-permalink="' . $cleanUrl . '"
                                data-instgrm-version="14"
                                style="background:#FFF;border:0;border-radius:3px;box-shadow:0 0 1px 0 rgba(0,0,0,.5),0 1px 10px 0 rgba(0,0,0,.15);margin:1px;max-width:540px;min-width:326px;padding:0;width:calc(100% - 2px);">
                    </blockquote>
                </div>';
    }

    // ------------------------------------------------------------------
    // 3. X / Twitter
    //    Matches: twitter.com/user/status/ID  and  x.com/user/status/ID
    // ------------------------------------------------------------------
    $xPattern = '/(?:https?:\/\/)?(?:www\.)?(?:twitter|x)\.com\/[^\/\s]+\/status\/([0-9]+)/i';
    if (preg_match($xPattern, $url, $m)) {
        // Normalise x.com → twitter.com so widgets.js resolves the tweet
        $tweetUrl = preg_replace('/https?:\/\/(?:www\.)?x\.com/i', 'https://twitter.com', $url);
        $tweetUrl = htmlspecialchars($tweetUrl, ENT_QUOTES, 'UTF-8');

        $GLOBALS['_media_sdk_registry']['twitter'] = true;

        return '<div class="social-embed-compact" style="margin-top:8px;display:flex;justify-content:center;width:100%;">
                    <blockquote class="twitter-tweet" data-lang="pt">
                        <a href="' . $tweetUrl . '">Ver tweet</a>
                    </blockquote>
                </div>';
    }

    // ------------------------------------------------------------------
    // 4. TikTok — standard URL: tiktok.com/@user/video/ID
    // ------------------------------------------------------------------
    $tiktokPattern = '/(?:https?:\/\/)?(?:www\.)?tiktok\.com\/.*\/video\/([0-9]+)/i';
    if (preg_match($tiktokPattern, $url, $m)) {
        $videoId  = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
        $cleanUrl = htmlspecialchars(strtok($url, '?'), ENT_QUOTES, 'UTF-8');

        $GLOBALS['_media_sdk_registry']['tiktok'] = true;

        return '<div class="social-embed-compact" style="margin-top:8px;display:flex;justify-content:center;width:100%;">
                    <blockquote class="tiktok-embed"
                                cite="' . $cleanUrl . '"
                                data-video-id="' . $videoId . '"
                                style="max-width:605px;min-width:325px;margin:0;">
                        <section></section>
                    </blockquote>
                </div>';
    }

    // ------------------------------------------------------------------
    // 4b. TikTok — short links: vm.tiktok.com/ID  or  vt.tiktok.com/ID
    //     Resolve via oEmbed (server-side HTTP) to extract the real embed.
    // ------------------------------------------------------------------
    if (preg_match('/(?:vm|vt)\.tiktok\.com/i', $url)) {
        $oembedBlock = _tiktok_resolve_short($url);
        if ($oembedBlock !== null) {
            $GLOBALS['_media_sdk_registry']['tiktok'] = true;
            return '<div class="social-embed-compact" style="margin-top:8px;display:flex;justify-content:center;width:100%;">'
                 . $oembedBlock
                 . '</div>';
        }
        // Fallback: plain link
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return '<a href="' . $safeUrl . '" target="_blank" style="color:var(--accent-primary);">'
             . '<iconify-icon icon="logos:tiktok-icon"></iconify-icon> Ver vídeo no TikTok'
             . '</a>';
    }

    // ------------------------------------------------------------------
    // 5. Fallback — clickable link
    // ------------------------------------------------------------------
    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    return '<a href="' . $safeUrl . '" target="_blank" style="color:var(--accent-primary);">'
         . '<iconify-icon icon="solar:link-circle-bold-duotone"></iconify-icon> ' . $safeUrl
         . '</a>';
}

/**
 * Resolve a TikTok short URL (vm/vt) via oEmbed and return a <blockquote> string.
 * Returns null on failure.
 *
 * @internal
 */
function _tiktok_resolve_short(string $url): ?string
{
    try {
        $oembedApiUrl = 'https://www.tiktok.com/oembed?url=' . urlencode($url);
        $ctx          = stream_context_create(['http' => ['timeout' => 4, 'ignore_errors' => true]]);
        $response     = @file_get_contents($oembedApiUrl, false, $ctx);

        if (!$response) return null;

        $data = json_decode($response, true);
        if (empty($data)) return null;

        // oEmbed can return a ready-made <blockquote> in the html field
        if (!empty($data['html'])) {
            // Strip any inline <script> from the oEmbed HTML — we inject centrally
            return preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $data['html']);
        }

        // Alternatively, extract the video ID from the thumbnail URL
        if (!empty($data['thumbnail_url'])) {
            if (preg_match('/\/([0-9]{15,20})[~\/]/', $data['thumbnail_url'], $m)) {
                $videoId  = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $safeUrl  = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                return '<blockquote class="tiktok-embed"
                                    cite="' . $safeUrl . '"
                                    data-video-id="' . $videoId . '"
                                    style="max-width:605px;min-width:325px;margin:0;">
                            <section></section>
                        </blockquote>';
            }
        }
    } catch (\Throwable $e) {
        // Silently fail — the caller will use the link fallback
    }

    return null;
}

/**
 * Output consolidated SDK <script> tags for all media embeds used on the page.
 * Call this ONCE, just before </body>.
 *
 * @return string HTML <script> tags (empty string if no embeds were used).
 */
function embed_media_sdk_scripts(): string
{
    $registry = $GLOBALS['_media_sdk_registry'] ?? [];
    $out = '';

    if (!empty($registry['instagram'])) {
        $out .= '<script async src="//www.instagram.com/embed.js"></script>' . "\n";
    }
    if (!empty($registry['tiktok'])) {
        $out .= '<script async src="https://www.tiktok.com/embed.js"></script>' . "\n";
    }
    if (!empty($registry['twitter'])) {
        $out .= '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>' . "\n";
    }

    return $out;
}

/**
 * Detect media type and fetch a high-quality thumbnail if possible
 */
function fetch_media_thumbnail(string $url): ?string
{
    $url = trim($url);
    if (empty($url)) return null;

    // 1. YouTube (Supports various patterns including shorts and live)
    $ytPattern = '/(?:(?:(?:m|www)\.)?youtube\.com\/(?:[^\/\s]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/|live\/)|youtu\.be\/)([^"&?\/\s]{11})/i';
    if (preg_match($ytPattern, $url, $m)) {
        return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
    }

    // 2. TikTok (Public oEmbed — handles all tiktok.com domains including vm/vt)
    if (strpos($url, 'tiktok.com') !== false) {
        try {
            $oembedUrl = 'https://www.tiktok.com/oembed?url=' . urlencode($url);
            $ctx = stream_context_create(['http' => ['timeout' => 3, 'header' => "User-Agent: DesbravaHub/1.0\r\n"]]);
            $response  = @file_get_contents($oembedUrl, false, $ctx);
            
            if ($response) {
                $data = json_decode($response, true);
                if (!empty($data['thumbnail_url'])) {
                    return $data['thumbnail_url'];
                }
            }
        } catch (\Exception $e) { /* Fallback below */ }
        // Note: No platform logos allowed (User Rule)
    }

    // 3. Instagram (Requires oEmbed with Access Token usually, fallback to premium placeholder)
    // Direct scraping of IG thumbnails is unreliable without a proxy/token.
    
    // 4. Direct Video Files
    if (preg_match('/\.(mp4|webm|mov|ogg)$/i', $url)) {
        // In a real scenario, we might use FFmpeg to generate a frame.
        // For now, we return null so the frontend can use its placeholder.
        return null; 
    }

    // Default: Fallback to the premium placeholder provided in assets if nothing else works
    // The frontend buildThumbJs handles this, but we can set a default relative path here too
    return null;
}
