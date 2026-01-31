<?php
/**
 * Translation Helper Functions
 * 
 * Provides translation functions for the application.
 */

/**
 * Global translations cache
 */
$GLOBALS['__translations'] = null;
$GLOBALS['__locale'] = 'pt-BR';

/**
 * Load translations
 */
function load_translations(string $locale = 'pt-BR'): array
{
    if ($GLOBALS['__translations'] !== null && $GLOBALS['__locale'] === $locale) {
        return $GLOBALS['__translations'];
    }

    $file = BASE_PATH . '/lang/' . $locale . '.php';

    if (!file_exists($file)) {
        $GLOBALS['__translations'] = [];
        return [];
    }

    $GLOBALS['__translations'] = require $file;
    $GLOBALS['__locale'] = $locale;

    return $GLOBALS['__translations'];
}

/**
 * Get a translated string
 * 
 * @param string $key Dot-notation key (e.g., 'auth.login' or 'save')
 * @param array $replace Replacements for :placeholders
 * @return string
 */
function __(?string $key, array $replace = []): string
{
    if ($key === null) {
        return '';
    }

    $translations = load_translations();

    // Handle dot notation
    $parts = explode('.', $key);
    $value = $translations;

    foreach ($parts as $part) {
        if (!is_array($value) || !isset($value[$part])) {
            return $key; // Return key if translation not found
        }
        $value = $value[$part];
    }

    if (!is_string($value)) {
        return $key;
    }

    // Handle replacements (:name -> value)
    foreach ($replace as $placeholder => $replacement) {
        $value = str_replace(':' . $placeholder, $replacement, $value);
    }

    return $value;
}

/**
 * Alias for __()
 */
function trans(?string $key, array $replace = []): string
{
    return __($key, $replace);
}

/**
 * Echo a translated string (escaped for HTML)
 */
function _e(?string $key, array $replace = []): void
{
    echo htmlspecialchars(__($key, $replace), ENT_QUOTES, 'UTF-8');
}

/**
 * Get translation and format as JSON for JavaScript
 */
function __json(string $section): string
{
    $translations = load_translations();

    if (isset($translations[$section]) && is_array($translations[$section])) {
        return json_encode($translations[$section], JSON_UNESCAPED_UNICODE);
    }

    return '{}';
}

/**
 * Format relative time in Portuguese
 */
function time_ago(string $datetime): string
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return __('time.now');
    }

    if ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' ' . ($minutes == 1 ? __('time.minute') : __('time.minutes')) . ' ' . __('time.ago');
    }

    if ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' ' . ($hours == 1 ? __('time.hour') : __('time.hours')) . ' ' . __('time.ago');
    }

    if ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' ' . ($days == 1 ? __('time.day') : __('time.days')) . ' ' . __('time.ago');
    }

    return date('d/m/Y', $timestamp);
}
