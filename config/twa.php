<?php

/**
 * Trusted Web Activity (TWA) configuration.
 *
 * Used by:
 *   • routes/web.php   → /.well-known/assetlinks.json
 *   • Future Bubblewrap builds for the Play Store
 *
 * To wire a published app:
 *   1) Build the AAB with `bubblewrap build` (uses twa-manifest.json at repo root).
 *   2) Upload to Play Console → enable "Play App Signing".
 *   3) Copy the SHA-256 from Play Console → Setup → App Signing → "App signing key certificate".
 *   4) Copy the SHA-256 from your local upload keystore (`bubblewrap build` prints it).
 *   5) Put BOTH into TWA_SHA256 in .env, comma-separated.
 *      → assetlinks.json will serve both fingerprints; Chrome will trust either.
 */
return [

    /** The exact Android application id used in Bubblewrap (twa-manifest.json → packageId). */
    'package_name' => env('TWA_PACKAGE_NAME', 'com.banhawy.app'),

    /**
     * SHA-256 fingerprint(s) of the signing certificate(s).
     * Format: 64 hex chars separated by colons. Multiple → comma-separated.
     * Example: "AA:BB:..:11, CC:DD:..:22"
     * Until set, /.well-known/assetlinks.json serves an empty array — Chrome will
     * NOT trust the app and the URL bar will still show (TWA-broken state).
     */
    'sha256' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TWA_SHA256', ''))
    ))),
];
