<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getDB();
$user = getCurrentUser();
$success = '';
$error = '';
$pageTitle = 'Hersteller';

// ── Hersteller anlegen ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name    = trim($_POST['h_name']    ?? '');
    $land    = trim($_POST['h_land']    ?? '');
    $kundennummer = trim($_POST['h_kundennummer'] ?? '');
    $website = trim($_POST['h_website'] ?? '');

    if (empty($name)) {
        $error = 'Der Name des Herstellers ist ein Pflichtfeld.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Hersteller (H_Name, H_Kundennummer, H_Land, H_Webseite, H_Creator, H_CreatorID)
                VALUES (:name, :kundennummer, :land, :website, :creator, :creator_id)
            ");
            $stmt->execute([
                'name'         => $name,
                'kundennummer' => $kundennummer ?: null,
                'land'         => $land ?: null,
                'website'      => $website ?: null,
                'creator'      => $user['name'],
                'creator_id' => $user['id'] ?: null
            ]);
            $success = 'Hersteller "' . htmlspecialchars($name) . '" wurde erfolgreich angelegt.';
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
}

// ── Liste laden ───────────────────────────────────────────────
$hersteller = $pdo->query("
    SELECT h.*, 
           (SELECT COUNT(*) FROM Geraete g WHERE g.HID = h.HID) as GeraeteCount
    FROM Hersteller h
    ORDER BY H_Name ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Stammdaten</div>
    <h1 class="page-header__title">Hersteller</h1>
    <p class="page-header__sub">Verwalten Sie die Hersteller Ihrer Inventarartikel.</p>
</div>

<?php if ($success): ?>
<div class="alert alert--success fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <div><?= $success ?></div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert--error fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="dashboard-grid fade-in">
    <!-- Formular Links -->
    <div class="card" style="grid-column: span 1;">
        <div class="card__header">
            <span class="card__title">Hersteller hinzufügen</span>
        </div>
        <div class="card__body">
            <form method="POST" action="/pages/hersteller.php">
                <input type="hidden" name="action" value="create">
                <div class="form-grid form-grid--full">
                    <div class="form-group">
                        <label class="form-label" for="h_name">Name <span>*</span></label>
                        <input class="form-control" type="text" id="h_name" name="h_name" 
                               placeholder="z.B. Dell, HP, Apple" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="h_kundennummer">Kundennummer</label>
                        <input class="form-control" type="text" id="h_kundennummer" name="h_kundennummer" 
                               placeholder="Ihre Kd.-Nr. beim Hersteller">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="h_land">Land</label>
                        <input class="form-control" type="text" id="h_land" name="h_land" 
                               placeholder="z.B. USA, Deutschland">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="h_website">Webseite</label>
                        <input class="form-control" type="url" id="h_website" name="h_website" 
                               placeholder="https://...">
                    </div>
                    <div style="padding-top:12px;">
                        <button type="submit" class="btn btn--gold" style="width:100%; justify-content:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Hersteller speichern
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste Rechts -->
    <div class="card" style="grid-column: span 2;">
        <div class="card__header">
            <span class="card__title"><?= count($hersteller) ?> Hersteller registriert</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Kundennummer</th>
                        <th>Land</th>
                        <th>Webseite</th>
                        <th style="text-align:center;">Geräte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($hersteller)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:var(--gray-3);">Keine Hersteller gefunden.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($hersteller as $h): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($h['H_Name']) ?></strong></td>
                            <td style="font-family:monospace; color:var(--gray-3);"><?= htmlspecialchars($h['H_Kundennummer'] ?? '–') ?></td>
                            <td><?= htmlspecialchars($h['H_Land'] ?? '–') ?></td>
                            <td>
                                <?php if ($h['H_Webseite']): ?>
                                    <a href="<?= htmlspecialchars($h['H_Webseite']) ?>" target="_blank" class="badge badge--navy" style="display:inline-flex; align-items:center; gap:4px; text-decoration:none;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        Zur Webseite
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--gray-4);">–</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge <?= $h['GeraeteCount'] > 0 ? 'badge--gold' : 'badge--navy' ?>">
                                    <?= $h['GeraeteCount'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
