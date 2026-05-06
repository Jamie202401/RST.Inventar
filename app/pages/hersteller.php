<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getDB();
$user = getCurrentUser();
$success = '';
$error = '';
$pageTitle = 'Hersteller';

// ── Hersteller anlegen / bearbeiten ───────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name    = trim($_POST['h_name']    ?? '');
    $land    = trim($_POST['h_land']    ?? '');
    $kundennummer = trim($_POST['h_kundennummer'] ?? '');
    $website = trim($_POST['h_website'] ?? '');
    $hid     = (int)($_POST['hid'] ?? 0);

    if (empty($name)) {
        $error = 'Der Name des Herstellers ist ein Pflichtfeld.';
    } else {
        try {
            if ($_POST['action'] === 'create') {
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
                    'creator_id'   => $user['id'] ?: null
                ]);
                $success = 'Hersteller "' . htmlspecialchars($name) . '" wurde erfolgreich angelegt.';
            } elseif ($_POST['action'] === 'update' && $hid > 0) {
                $stmt = $pdo->prepare("
                    UPDATE Hersteller 
                    SET H_Name = :name, H_Kundennummer = :kundennummer, H_Land = :land, 
                        H_Webseite = :website, H_Changer = :changer, H_ChangerID = :changer_id
                    WHERE HID = :hid
                ");
                $stmt->execute([
                    'name'         => $name,
                    'kundennummer' => $kundennummer ?: null,
                    'land'         => $land ?: null,
                    'website'      => $website ?: null,
                    'changer'      => $user['name'],
                    'changer_id'   => $user['id'] ?: null,
                    'hid'          => $hid
                ]);
                $success = 'Hersteller "' . htmlspecialchars($name) . '" wurde erfolgreich aktualisiert.';
            }
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
}

// ── Bearbeitungs-Modus laden ──────────────────────────────────
$editHersteller = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Hersteller WHERE HID = ?");
    $stmt->execute([$_GET['edit']]);
    $editHersteller = $stmt->fetch();
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
            <span class="card__title"><?= $editHersteller ? 'Hersteller bearbeiten' : 'Hersteller hinzufügen' ?></span>
            <?php if ($editHersteller): ?>
                <a href="/pages/hersteller.php" class="btn btn--sm btn--outline">Abbrechen</a>
            <?php endif; ?>
        </div>
        <div class="card__body">
            <form method="POST" action="/pages/hersteller.php">
                <input type="hidden" name="action" value="<?= $editHersteller ? 'update' : 'create' ?>">
                <?php if ($editHersteller): ?>
                    <input type="hidden" name="hid" value="<?= $editHersteller['HID'] ?>">
                <?php endif; ?>

                <div class="form-grid form-grid--full">
                    <div class="form-group">
                        <label class="form-label" for="h_name">Name <span>*</span></label>
                        <input class="form-control" type="text" id="h_name" name="h_name" 
                               value="<?= htmlspecialchars($editHersteller['H_Name'] ?? '') ?>"
                               placeholder="z.B. Dell, HP, Apple" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="h_kundennummer">Kundennummer</label>
                        <input class="form-control" type="text" id="h_kundennummer" name="h_kundennummer" 
                               value="<?= htmlspecialchars($editHersteller['H_Kundennummer'] ?? '') ?>"
                               placeholder="Ihre Kd.-Nr. beim Hersteller">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="h_land">Land</label>
                        <input class="form-control" type="text" id="h_land" name="h_land" 
                               value="<?= htmlspecialchars($editHersteller['H_Land'] ?? '') ?>"
                               placeholder="z.B. USA, Deutschland">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="h_website">Webseite</label>
                        <input class="form-control" type="url" id="h_website" name="h_website" 
                               value="<?= htmlspecialchars($editHersteller['H_Webseite'] ?? '') ?>"
                               placeholder="https://...">
                    </div>
                    <div style="padding-top:12px;">
                        <button type="submit" class="btn <?= $editHersteller ? 'btn--navy' : 'btn--gold' ?>" style="width:100%; justify-content:center;">
                            <?php if ($editHersteller): ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Änderungen speichern
                            <?php else: ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Hersteller speichern
                            <?php endif; ?>
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
                        <th style="text-align:right;">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($hersteller)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:40px; color:var(--gray-3);">Keine Hersteller gefunden.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($hersteller as $h): ?>
                        <tr class="<?= ($editHersteller && $editHersteller['HID'] == $h['HID']) ? 'table-active' : '' ?>">
                            <td><strong><?= htmlspecialchars($h['H_Name']) ?></strong></td>
                            <td style="font-family:monospace; color:var(--gray-3);"><?= htmlspecialchars($h['H_Kundennummer'] ?? '–') ?></td>
                            <td><?= htmlspecialchars($h['H_Land'] ?? '–') ?></td>
                            <td>
                                <?php if ($h['H_Webseite']): ?>
                                    <a href="<?= htmlspecialchars($h['H_Webseite']) ?>" target="_blank" class="badge badge--navy" style="display:inline-flex; align-items:center; gap:4px; text-decoration:none;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                        WWW
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
                            <td style="text-align:right;">
                                <a href="?edit=<?= $h['HID'] ?>" class="btn btn--sm btn--outline" title="Bearbeiten">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </a>
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
