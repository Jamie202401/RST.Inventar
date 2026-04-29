<?php
// ============================================================
// Layout-Helfer – HTML Header & Footer
// ============================================================

function renderHeader(string $title = 'RST-Inventar', string $activeNav = ''): void
{
    $user = getCurrentUser();
    ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> – RST-Inventar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<nav class="sidebar">
    <div class="sidebar__brand">
        <div class="sidebar__logo">
            <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
                <rect width="28" height="28" rx="6" fill="#C8963E"/>
                <path d="M7 8h14M7 14h10M7 20h12" stroke="#0F2D52" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </div>
        <span class="sidebar__name">RST-Inventar</span>
    </div>

    <div class="sidebar__user">
        <div class="sidebar__avatar"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
        <div class="sidebar__userinfo">
            <span class="sidebar__username"><?= htmlspecialchars($user['name']) ?></span>
            <span class="sidebar__role">Systembenutzer</span>
        </div>
    </div>

    <nav class="sidebar__nav">
        <a href="/pages/dashboard.php" class="sidebar__link <?= $activeNav === 'dashboard' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="/pages/inventar-erstellen.php" class="sidebar__link <?= $activeNav === 'erstellen' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            Artikel anlegen
        </a>
        <a href="/pages/barcodes.php" class="sidebar__link <?= $activeNav === 'barcodes' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14M3 5h2M7 5h2"/></svg>
            Barcodes
        </a>
    </nav>

    <a href="/pages/logout.php" class="sidebar__logout">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Abmelden
    </a>
</nav>

<main class="main">
    <div class="main__inner">
    <?php
}

function renderFooter(): void
{
    ?>
    </div><!-- /.main__inner -->
</main><!-- /.main -->

<script src="/assets/js/app.js"></script>
</body>
</html>
    <?php
}
