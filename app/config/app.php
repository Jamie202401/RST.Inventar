<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('APP_NAME',    'RST-Inventar');
define('APP_VERSION', '1.0.0');
define('APP_ROOT',    dirname(__DIR__));

define('SESSION_LIFETIME', 3600);

define('BARCODE_PREFIX', 'RST-');
define('BARCODE_TYPE',   'C128');

// ── Keycloak OIDC ─────────────────────────────────────────────
// KC_BASE_PUBLIC  = URL, die der Browser aufruft (von außen erreichbar)
// KC_BASE_INTERNAL = URL, die PHP im Container aufruft (Docker-intern)
define('KC_BASE_PUBLIC',   'http://localhost:8180');
define('KC_BASE_INTERNAL', 'http://keycloak:8080');
define('KC_REALM',         'rst-inventar');
define('KC_CLIENT_ID',     'rst-inventar-app');
define('KC_CLIENT_SECRET', 'rst-inventar-client-secret');

// Öffentliche App-URL (Browser-seitig) – wird als redirect_uri genutzt
define('APP_URL', 'http://localhost');
