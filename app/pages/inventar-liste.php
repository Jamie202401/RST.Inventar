<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireLogin();
$pdo = getDB();

$suche   = trim($_GET['suche'] ?? '');
$filterK = (int) ($_GET['kid'] ?? 0);
$filterS = (int) ($_GET['sid'] ?? 0);

$where  = ['1=1'];
$params = [];
if ($suche) {
    $where[]  = '(g.G_Name LIKE ? OR h.H_Name LIKE ? OR i.Barcode LIKE ?)';
    $like     = "%$suche%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($filterK) { $where[] = 'g.KID = ?'; $params[] = $filterK; }
if ($filterS) { $where[] = 'i.SID = ?'; $params[] = $filterS; }

$sql = 'SELECT i.InvID, i.Barcode,
               g.GID, g.G_Name, h.H_Name AS G_Hersteller, g.G_Kosten, g.G_Garantieende,
               k.K_Name AS Kategorie,
               s.S_Name AS Standort,
               g.G_CreateDate, g.G_Creator
        FROM Inventar i
        JOIN Geraete   g ON i.GID = g.GID
        JOIN Kategorie k ON g.KID = k.KID
        JOIN Standorte s ON i.SID = s.SID
        LEFT JOIN Hersteller h ON g.HID = h.HID
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY i.InvID DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inventar = $stmt->fetchAll();

$kategorien = $pdo->query('SELECT KID, K_Name FROM Kategorie ORDER BY K_Name')->fetchAll();
$standorte  = $pdo->query('SELECT SID, S_Name FROM Standorte ORDER BY S_Name')->fetchAll();

// Statistiken
$gesamt      = count($inventar);
$abgelaufen  = 0;
$baldAblauf  = 0;
foreach ($inventar as $item) {
    if ($item['G_Garantieende']) {
        $ts = strtotime($item['G_Garantieende']);
        if ($ts < time()) $abgelaufen++;
        elseif ($ts < strtotime('+90 days')) $baldAblauf++;
    }
}

renderHeader('Inventarliste', 'liste');
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Inventar</div>
    <h1 class="page-header__title">Inventarliste</h1>
    <p class="page-header__sub">Übersicht aller erfassten Inventarartikel.</p>
</div>

<!-- Statistiken -->
<div class="stats-row fade-in">
    <span class="stats-pill">
        <span class="stats-pill__dot stats-pill__dot--navy"></span>
        <?= $gesamt ?> Artikel gesamt
    </span>
    <?php if ($abgelaufen > 0): ?>
    <span class="stats-pill">
        <span class="stats-pill__dot stats-pill__dot--red"></span>
        <?= $abgelaufen ?> Garantie abgelaufen
    </span>
    <?php endif; ?>
    <?php if ($baldAblauf > 0): ?>
    <span class="stats-pill">
        <span class="stats-pill__dot" style="background:var(--warning);"></span>
        <?= $baldAblauf ?> Garantie läuft bald ab
    </span>
    <?php endif; ?>
</div>

<!-- Filter -->
<div class="card fade-in" style="margin-bottom:24px;">
    <div class="card__body" style="padding:20px 28px;">
        <form method="GET" action="/pages/inventar-liste.php">
            <div class="filter-bar">
                <div class="form-group">
                    <label class="form-label">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Suche
                    </label>
                    <input class="form-control" type="text" name="suche"
                        placeholder="Name, Hersteller, Barcode..."
                        value="<?= htmlspecialchars($suche) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Kategorie</label>
                    <select class="form-control" name="kid">
                        <option value="">Alle Kategorien</option>
                        <?php foreach ($kategorien as $k): ?>
                        <option value="<?= $k['KID'] ?>" <?= $filterK == $k['KID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['K_Name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Standort</label>
                    <select class="form-control" name="sid">
                        <option value="">Alle Standorte</option>
                        <?php foreach ($standorte as $s): ?>
                        <option value="<?= $s['SID'] ?>" <?= $filterS == $s['SID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['S_Name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex;gap:8px;align-items:flex-end;">
                    <button type="submit" class="btn btn--primary">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        Suchen
                    </button>
                    <?php if ($suche || $filterK || $filterS): ?>
                    <a href="/pages/inventar-liste.php" class="btn btn--outline" title="Filter zurücksetzen">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabelle -->
<div class="card fade-in">
    <div class="card__header">
        <span class="card__title">
            <?php if ($suche || $filterK || $filterS): ?>
                Gefilterte Ergebnisse
                <span class="badge badge--navy" style="margin-left:8px;"><?= $gesamt ?></span>
            <?php else: ?>
                Alle Artikel
            <?php endif; ?>
        </span>
        <a href="/pages/inventar-erstellen.php" class="btn btn--gold btn--sm">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Neuer Artikel
        </a>
    </div>

    <?php if (empty($inventar)): ?>
    <div class="card__body">
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <div class="empty-state__title">Keine Artikel gefunden</div>
            <div class="empty-state__text">Versuchen Sie andere Suchbegriffe oder setzen Sie die Filter zurück.</div>
            <a href="/pages/inventar-liste.php" class="btn btn--outline">Filter zurücksetzen</a>
        </div>
    </div>
    <?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Gerät</th>
                    <th>Hersteller</th>
                    <th>Kategorie</th>
                    <th>Standort</th>
                    <th>Kosten</th>
                    <th>Garantie</th>
                    <th>Angelegt</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventar as $item):
                    $garantieAbgelaufen = $item['G_Garantieende'] && strtotime($item['G_Garantieende']) < time();
                    $garantieBald = $item['G_Garantieende']
                        && strtotime($item['G_Garantieende']) < strtotime('+90 days')
                        && !$garantieAbgelaufen;
                ?>
                <tr<?= $garantieAbgelaufen ? ' style="background:#FFF5F5;"' : '' ?>>
                    <td>
                        <code style="font-size:.8rem;color:var(--navy);background:var(--navy-light);padding:3px 8px;border-radius:5px;">
                            <?= htmlspecialchars($item['Barcode']) ?>
                        </code>
                    </td>
                    <td><strong style="color:var(--gray-1);"><?= htmlspecialchars($item['G_Name']) ?></strong></td>
                    <td style="color:var(--gray-3);"><?= htmlspecialchars($item['G_Hersteller'] ?? '–') ?></td>
                    <td><span class="badge badge--gold"><?= htmlspecialchars($item['Kategorie']) ?></span></td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:.88rem;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--gray-4);flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($item['Standort']) ?>
                        </span>
                    </td>
                    <td style="font-size:.88rem;"><?= $item['G_Kosten'] ? number_format((float)$item['G_Kosten'], 2, ',', '.') . ' €' : '<span style="color:var(--gray-4);">–</span>' ?></td>
                    <td>
                        <?php if (!$item['G_Garantieende']): ?>
                            <span style="color:var(--gray-4);">–</span>
                        <?php elseif ($garantieAbgelaufen): ?>
                            <span class="badge badge--red">Abgelaufen</span>
                        <?php elseif ($garantieBald): ?>
                            <span class="badge" style="background:#FEF3C7;color:#92400E;">Läuft bald ab</span>
                        <?php else: ?>
                            <span style="font-size:.85rem;color:var(--success);">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:2px;"><polyline points="20 6 9 17 4 12"/></svg>
                                <?= date('d.m.Y', strtotime($item['G_Garantieende'])) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.82rem;color:var(--gray-4);"><?= date('d.m.Y', strtotime($item['G_CreateDate'])) ?></td>
                    <td>
                        <div class="tbl-actions">
                            <a href="/pages/barcodes.php" class="tbl-btn" title="Barcode anzeigen">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php renderFooter(); ?>
