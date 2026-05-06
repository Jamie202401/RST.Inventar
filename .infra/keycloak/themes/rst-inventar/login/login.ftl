<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${msg("loginTitle",(realm.displayName!''))}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="${url.resourcesPath}/css/app.css">
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
            <div class="login-feature"><div class="login-feature__dot"></div>Artikel anlegen und verwalten</div>
            <div class="login-feature"><div class="login-feature__dot"></div>Automatische Barcode-Generierung</div>
            <div class="login-feature"><div class="login-feature__dot"></div>Lückenlose Änderungshistorie</div>
            <div class="login-feature"><div class="login-feature__dot"></div>Standortverwaltung</div>
        </div>
    </div>

    <!-- Rechte Seite – Login -->
    <div class="login-right">
        <div class="login-form">
            <h2 class="login-form__title">Willkommen zurück</h2>
            <p class="login-form__sub">Melden Sie sich mit Ihrem Unternehmens-Account an.</p>

            <#if message?has_content && (message.type != 'warning' || !isAppInitiatedAction??)>
                <div class="alert alert--${message.type}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>${message.summary?no_esc}</span>
                </div>
            </#if>

            <form id="kc-form-login" onsubmit="login.disabled = true; return true;" action="${url.loginAction}" method="post">
                <div class="form-group">
                    <label class="form-label" for="username"><#if !realm.loginWithEmailAllowed>${msg("username")}<#elseif !realm.registrationEmailAsUsername>${msg("usernameOrEmail")}<#else>${msg("email")}</#if> <span>*</span></label>
                    <input class="form-control" tabindex="1" id="username" name="username" value="${(login.username!'')}" type="text" autofocus autocomplete="off" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">${msg("password")} <span>*</span></label>
                    <input class="form-control" tabindex="2" id="password" name="password" type="password" autocomplete="off" required>
                </div>

                <button type="submit" class="btn btn--navy login-submit" name="login" id="kc-login" style="background:var(--navy);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Anmelden
                </button>
            </form>

            <p style="margin-top:24px; text-align:center; font-size:.8rem; color:var(--gray-4);">
                RST-Veolia GmbH &amp; Co. KG &middot; Herrenberg
            </p>
        </div>
    </div>
</body>
</html>
