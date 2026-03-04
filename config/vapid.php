<?php
/**
 * VAPID Configuration
 * 
 * Fallback values are hardcoded because Hostinger's shared hosting
 * can fail to parse the .env file in certain web server contexts.
 */

return [
    'public_key' => env('VAPID_PUBLIC_KEY', 'BDKSEL_Z5_tcSvHd5syzfjVadw-TYZ1VrWTbxSjPIJhA0h1rWLCNrqrtweqQ-ekzVorpurcQZu8dxHpFzI-1rAY'),
    'private_key' => env('VAPID_PRIVATE_KEY', 'makKcmBz9_mMk7vUR4_bwLXiSoT1pX-KYUiWeL9xkBA'),
];
