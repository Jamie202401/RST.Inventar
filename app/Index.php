<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/includes/auth.php';

startSession();

if (isLoggedIn()) {
    header('Location: /pages/dashboard.php');
    exit;
}

// Keycloak-Authorization-URL aufbauen
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$authUrl = KC_BASE_PUBLIC
    . '/realms/' . KC_REALM
    . '/protocol/openid-connect/auth?'
    . http_build_query([
        'client_id'     => KC_CLIENT_ID,
        'response_type' => 'code',
        'scope'         => 'openid profile email roles',
        'redirect_uri'  => APP_URL . '/callback.php',
        'state'         => $state,
    ]);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden – RST-Inventar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="login-page">

    <!-- Linke Seite – Branding -->
    <div class="login-left">
        <div class="login-brand">
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                <rect width="36" height="36" rx="8" fill="#C8963E"/>
                <path d="M9 11h18M9 18h13M9 25h15" stroke="#0F2D52" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <span class="login-brand__name">RST-Inventar</span>
        </div>

        <div class="login-tagline">
            <h1 class="login-tagline__title">
                Inventar<br>
                <span>intelligent</span><br>
                verwalten.
            </h1>
            <p class="login-tagline__text">
                Das Inventarverwaltungssystem der RST-Veolia GmbH &amp; Co. KG — zentral, schnell und übersichtlich.
            </p>
        </div>

        <div class="login-features">
            <div class="login-feature">
                <div class="login-feature__dot"></div>
                Artikel anlegen und verwalten
            </div>
            <div class="login-feature">
                <div class="login-feature__dot"></div>
                Automatische Barcode-Generierung
            </div>
            <div class="login-feature">
                <div class="login-feature__dot"></div>
                Lückenlose Änderungshistorie
            </div>
            <div class="login-feature">
                <div class="login-feature__dot"></div>
                Standortverwaltung
            </div>
        </div>
    </div>

    <!-- Rechte Seite – Login -->
    <div class="login-right">
        <div class="login-form">
            <h2 class="login-form__title">Willkommen zurück</h2>
            <p class="login-form__sub">Melden Sie sich mit Ihrem Unternehmens-Account an.</p>

            <a href="<?= htmlspecialchars($authUrl) ?>" class="btn btn--navy login-submit" style="display:flex;align-items:center;justify-content:center;gap:10px;text-decoration:none;margin-top:32px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                    <path d="M15 12H9m3-3v6"/>
                </svg>
                Mit Keycloak anmelden
            </a>

            <p style="margin-top:24px; text-align:center; font-size:.8rem; color:var(--gray-4);">
                RST-Veolia GmbH &amp; Co. KG &middot; Herrenberg
            </p>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
</body>
</html>
