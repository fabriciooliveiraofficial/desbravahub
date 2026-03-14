<?php
/**
 * URL Helper Functions
 * 
 * URL generation helpers that use the centralized base_url configuration.
 * All URL generation in the application MUST use these helpers.
 */

/**
 * Get the base URL of the application
 * 
 * @param string $path Optional path to append
 * @return string Full URL
 * 
 * Usage:
 *   base_url()                    // 'https://cruzeirodosuljuveve.org'
 *   base_url('admin/dashboard')   // 'https://cruzeirodosuljuveve.org/admin/dashboard'
 */
function base_url(string $path = ''): string
{
    static $detectedBaseUrl = null;

    $baseUrl = config('app.base_url');

    // If config is missing, detect from environment or request
    if (empty($baseUrl)) {
        if ($detectedBaseUrl === null) {
            // Priority 1: Environment variable
            $envUrl = env('APP_BASE_URL');
            if ($envUrl) {
                $detectedBaseUrl = $envUrl;
            } 
            // Priority 2: Request-based detection
            elseif (isset($_SERVER['HTTP_HOST'])) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $detectedBaseUrl = $protocol . '://' . $host;
            }
            // Priority 3: No server context (CLI/cron) — use relative paths
            else {
                $detectedBaseUrl = '';
            }
        }
        $baseUrl = $detectedBaseUrl;
    }

    $baseUrl = rtrim($baseUrl, '/');

    if (empty($path)) {
        return $baseUrl;
    }

    // Separate fragment (#) and query string (?) from the path
    $hashSplit = explode('#', $path, 2);
    $pathWithoutHash = $hashSplit[0];
    $fragment = isset($hashSplit[1]) ? '#' . $hashSplit[1] : '';

    $querySplit = explode('?', $pathWithoutHash, 2);
    $pathPart = $querySplit[0];
    $query = isset($querySplit[1]) ? '?' . $querySplit[1] : '';

    // Encode spaces in path segments but keep slashes
    $segments = explode('/', ltrim($pathPart, '/'));
    $encodedSegments = array_map(function($segment) {
        return rawurlencode($segment);
    }, $segments);
    $encodedPath = implode('/', $encodedSegments);

    return $baseUrl . '/' . $encodedPath . $query . $fragment;
}

/**
 * Get URL for a tenant path
 * 
 * @param string $tenantSlug Tenant identifier
 * @param string $path Path within tenant
 * @return string Full tenant URL
 * 
 * Usage:
 *   tenant_url('club-alpha', 'dashboard')  // 'https://.../club-alpha/dashboard'
 */
function tenant_url(string $tenantSlug, string $path = ''): string
{
    $tenantPath = $tenantSlug;

    if (!empty($path)) {
        $tenantPath .= '/' . ltrim($path, '/');
    }

    return base_url($tenantPath);
}

/**
 * Get URL for an asset with cache busting
 * 
 * @param string $path Asset path relative to public directory
 * @return string Full asset URL with version
 * 
 * Usage:
 *   asset_url('css/style.css')    // 'https://.../assets/css/style.css?v=1.0.0'
 *   asset_url('js/app.js')        // 'https://.../assets/js/app.js?v=1.0.0'
 */
function asset_url(string $path): string
{
    // config('app.asset_version', '1.0.0') fallback
    $assetPathRelative = 'assets/' . ltrim($path, '/');
    $fullPath = __DIR__ . '/../public/' . $assetPathRelative;

    $version = config('app.asset_version', '1.0.0');

    // If file exists, use its modification time as version
    if (file_exists($fullPath)) {
        $version = filemtime($fullPath);
    }

    return base_url($assetPathRelative) . '?v=' . $version;
}

/**
 * Get URL for API endpoints
 * 
 * @param string $endpoint API endpoint path
 * @return string Full API URL
 * 
 * Usage:
 *   api_url('version')           // 'https://.../api/version'
 *   api_url('users/profile')     // 'https://.../api/users/profile'
 */
function api_url(string $endpoint): string
{
    return base_url('api/' . ltrim($endpoint, '/'));
}

/**
 * Generate a redirect URL (for auth, etc.)
 * 
 * @param string $path Path to redirect to
 * @param array $params Query parameters
 * @return string Redirect URL
 */
function redirect_url(string $path, array $params = []): string
{
    $url = base_url($path);

    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}

/**
 * Get the current URL
 * 
 * @return string Current full URL
 */
function current_url(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        ? 'https'
        : 'http';

    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Check if the current request is HTTPS
 * 
 * @return bool
 */
function is_https(): bool
{
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}
/**
 * Set HTTP Response Code
 * 
 * @param int $code HTTP response code
 * @return int The status code
 */
function http_code(int $code): int
{
    return http_response_code($code);
}
