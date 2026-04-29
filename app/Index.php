<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

startSession();

// prüft ob mann eingelogt ist wenn ja dann Wird man auf das Dashbaord geleitet wenn nicht bleibt man auf der Index seite
if (isLoggedIn()) {
    header('Location: /RST-INVENTAR/app/index.php');
    exit;
}


$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if(empty($username) || empty ($password)){
        $error = 'Bitte Benutzernamen und Passwort eingeben';
    }else{
        $pdo = getDB();
        $stmt =  $pdo->prepare('SELECT BID, B_Name, B_Passwort FROM Benutzer WHERE B_Name = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if($user && password_verify($password, $user['B_Passwort'])){
            loginUser((int)$user['BID'], $user['B_Name']);
            header('Location: /RST-INVENTAR/app/pages/dashboard.php');
            exit;
        }else{
            $error = 'Benutzername oder Passwort ist falsch';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden – RST-Inventar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/RST-INVENTAR/app/assets/css/app.css">
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

    <!-- Rechte Seite – Login Formular -->
    <div class="login-right">
        <div class="login-form">
            <h2 class="login-form__title">Willkommen zurück</h2>
            <p class="login-form__sub">Melden Sie sich mit Ihren Zugangsdaten an.</p>

            <?php if ($error): ?>
            <div class="alert alert--error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/RST-Inventar/app/index.php">
                <div class="form-group">
                    <label class="form-label" for="username">Benutzername <span>*</span></label>
                    <input
                        class="form-control"
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Ihr Benutzername"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Passwort <span>*</span></label>
                    <input
                        class="form-control"
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ihr Passwort"
                        required
                    >
                </div>

                <button type="submit" class="btn btn--navy login-submit" style="background:var(--navy);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Anmelden
                </button>
            </form>

            <p style="margin-top:24px; text-align:center; font-size:.8rem; color:var(--gray-4);">
                RST-Veolia GmbH &amp; Co. KG &middot; Herrenberg
            </p>
        </div>
    </div>

    <script src="/RST-INVENTAR/app/assets/js/app.js"></script>
</body>
</html>
