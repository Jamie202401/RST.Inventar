<?php
// ============================================================
// Dashboard – RST-Inventar
// ============================================================

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

requireLogin();

$pageTitle = 'Dashboard';
$pdo = getDB();

// Statistiken laden
$stats = [
    'geraete' => $pdo->query('SELECT COUNT(*) FROM Geraete')->fetchColumn(),
    'inventar' => $pdo->query('SELECT COUNT(*) FROM Inventar')->fetchColumn(),
    'standorte' => $pdo->query('SELECT COUNT(*) FROM Standorte')->fetchColumn(),
    'kategorien' => $pdo->query('SELECT COUNT(*) FROM Kategorie')->fetchColumn(),
];

// Letzte 5 Geräte
$letzteGeraete = $pdo->query(
    'SELECT g.GID, g.G_Name, h.H_Name AS G_Hersteller, k.K_Name AS Kategorie,
            g.G_CreateDate, g.G_Creator
     FROM Geraete g
     JOIN Kategorie k ON g.KID = k.KID
     LEFT JOIN Hersteller h ON g.HID = h.HID
     ORDER BY g.G_CreateDate DESC LIMIT 5'
)->fetchAll();

// Garantien die in 90 Tagen ablaufen
$garantieAblauf = $pdo->query(
    'SELECT COUNT(*) FROM Geraete
     WHERE G_Garantieende IS NOT NULL
     AND G_Garantieende <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
     AND G_Garantieende >= CURDATE()'
)->fetchColumn();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Übersicht</div>
    <h1 class="page-header__title">Dashboard</h1>
    <p class="page-header__sub">Willkommen, <?= htmlspecialchars(getCurrentUser()['name']) ?>. Hier ist eine Übersicht Ihres Inventars.</p>
</div>

<?php if ($garantieAblauf > 0): ?>
<div class="alert alert--warning fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <strong><?= $garantieAblauf ?> Gerät<?= $garantieAblauf > 1 ? 'e' : '' ?></strong> mit ablaufender Garantie in den nächsten 90 Tagen.
</div>
<?php endif; ?>

<!-- Statistik-Karten -->
<div class="dashboard-grid fade-in">
    <div class="stat-card">
        <div class="stat-card__label">Geräte gesamt</div>
        <div class="stat-card__value"><?= $stats['geraete'] ?></div>
        <div class="stat-card__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Inventareinträge</div>
        <div class="stat-card__value"><?= $stats['inventar'] ?></div>
        <div class="stat-card__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Standorte</div>
        <div class="stat-card__value"><?= $stats['standorte'] ?></div>
        <div class="stat-card__icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </div>
    </div>
</div>

<!-- Aktions-Karten -->
<div class="action-grid fade-in">
    <a href="/pages/inventar_erstellen.php" class="action-card action-card--gold">
        <div class="action-card__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        </div>
        <div class="action-card__title">Artikel anlegen</div>
        <div class="action-card__desc">Neues Gerät mit allen Details erfassen und dem Inventar hinzufügen.</div>
        <div class="action-card__arrow">Jetzt anlegen →</div>
    </a>

    <a href="/pages/barcodes.php" class="action-card">
        <div class="action-card__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14M3 5h2M7 5h2"/></svg>
        </div>
        <div class="action-card__title">Barcodes ausgeben</div>
        <div class="action-card__desc">Barcodes für alle Inventarartikel anzeigen, drucken oder exportieren.</div>
        <div class="action-card__arrow">Zu den Barcodes →</div>
    </a>

    <a href="/pages/inventar_liste.php" class="action-card">
        <div class="action-card__icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        </div>
        <div class="action-card__title">Inventar ansehen</div>
        <div class="action-card__desc">Alle Inventarartikel mit Filtern und Suchfunktion durchsuchen.</div>
        <div class="action-card__arrow">Zur Liste →</div>
    </a>
</div>

<!-- Letzte Geräte -->
<div class="card fade-in">
    <div class="card__header">
        <span class="card__title">Zuletzt hinzugefügte Geräte</span>
        <a href="/pages/inventar_liste.php" class="btn btn--outline btn--sm">Alle anzeigen</a>
    </div>
    <?php if (empty($letzteGeraete)): ?>
    <div class="card__body">
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="20" height="14" rx="2"/></svg>
            </div>
            <div class="empty-state__title">Noch keine Geräte vorhanden</div>
            <div class="empty-state__text">Legen Sie Ihr erstes Gerät an um es hier zu sehen.</div>
            <a href="/pages/inventar_erstellen.php" class="btn btn--gold">Erstes Gerät anlegen</a>
        </div>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Gerätename</th>
                    <th>Hersteller</th>
                    <th>Kategorie</th>
                    <th>Angelegt von</th>
                    <th>Datum</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($letzteGeraete as $g): ?>
                <tr>
                    <td><span class="badge badge--navy"><?= htmlspecialchars($g['GID']) ?></span></td>
                    <td><strong><?= htmlspecialchars($g['G_Name']) ?></strong></td>
                    <td><?= htmlspecialchars($g['G_Hersteller'] ?? '–') ?></td>
                    <td><span class="badge badge--gold"><?= htmlspecialchars($g['Kategorie']) ?></span></td>
                    <td><?= htmlspecialchars($g['G_Creator'] ?? '–') ?></td>
                    <td><?= date('d.m.Y', strtotime($g['G_CreateDate'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>