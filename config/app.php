<?php
/**
 * Application Configuration
 * 
 * Central configuration for DesbravaHub platform.
 * All URL and environment settings are managed here.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */
    'name' => 'DesbravaHub',

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    | The base URL of your application. This value is used for generating
    | links, assets, emails, and API endpoints. NEVER hardcode URLs elsewhere.
    |
    | Change this single value to update all URLs globally.
    */
    'base_url' => env('APP_BASE_URL', 'https://cruzeirodosuljuveve.org'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    | Supported: "dev", "staging", "production"
    */
    'environment' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    | When enabled, detailed error messages will be shown.
    | NEVER enable in production.
    */
    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    */
    'timezone' => 'America/Sao_Paulo',

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    */
    'locale' => 'pt_BR',

    /*
    |--------------------------------------------------------------------------
    | Asset Version
    |--------------------------------------------------------------------------
    | Used for cache busting. Increment when deploying new assets.
    */
    'asset_version' => '1.0.7',
];
