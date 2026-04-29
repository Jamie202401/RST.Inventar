<?php

require_once __DIR__ . '/../Includes/auth.php';
requireLogin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'RST-Inventar') ?> – RST-Inventar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/RST-INVENTAR/app/assets/css/app.css">
</head>
<body>

<nav class="sidebar">
    <div class="sidebar__brand">
        <svg width="28" height="28" viewBox="0 0 36 36" fill="none">
            <rect width="36" height="36" rx="8" fill="#C8963E"/>
            <path d="M9 11h18M9 18h13M9 25h15" stroke="#0F2D52" stroke-width="3" stroke-linecap="round"/>
        </svg>
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
        <a href="/RST-INVENTAR/app/pages/dashboard.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>
            Dashboard
        </a>
        <a href="/RST-INVENTAR/app/pages/inventar_erstellen.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'inventar_erstellen.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Artikel anlegen
        </a>
        <a href="/RST-INVENTAR/app/pages/inventar_liste.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'inventar_liste.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            Inventarliste
        </a>
        <a href="/RST-INVENTAR/app/pages/barcodes.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'barcodes.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14M3 5h2M7 5h2"/>
            </svg>
            Barcodes
        </a>
    </nav>

    <a href="/RST-INVENTAR/app/pages/logout.php" class="sidebar__logout">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Abmelden
    </a>
</nav>

<main class="main">
    <div class="main__inner">