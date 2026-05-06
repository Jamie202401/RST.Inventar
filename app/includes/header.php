<?php

require_once __DIR__ . '/../includes/auth.php';
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
    <link rel="stylesheet" href="/assets/css/app.css">
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
        <a href="/pages/dashboard.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
            </svg>
            Dashboard
        </a>
        <a href="/pages/inventar_erstellen.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'inventar_erstellen.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Artikel anlegen
        </a>
        <a href="/pages/inventar_liste.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'inventar_liste.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/>
                <line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            Inventarliste
        </a>
        <a href="/pages/scanner.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'scanner.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 7V4h3M17 4h3v3M4 17v3h3M17 20h3v-3"/><path d="M3 12h18" stroke-dasharray="2 2"/>
            </svg>
            Scanner
        </a>
        <a href="/pages/etiketten.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'etiketten.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>
            </svg>
            Etiketten
        </a>

        <div class="sidebar__sep"></div>
        <div class="sidebar__label">Stammdaten</div>

        <a href="/pages/hersteller.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'hersteller.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>
            </svg>
            Hersteller
        </a>
        <a href="/pages/lieferanten.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'lieferanten.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Lieferanten
        </a>
        <a href="/pages/standorte.php"
           class="sidebar__link <?= basename($_SERVER['PHP_SELF']) === 'standorte.php' ? 'active' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
            Standorte
        </a>
    </nav>

    <a href="/pages/logout.php" class="sidebar__logout">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Abmelden
    </a>
</nav>

<main class="main">
    <div class="main__inner <?= $mainContainerClass ?? '' ?>">