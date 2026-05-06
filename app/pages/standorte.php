<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getDB();
$user = getCurrentUser();
$success = '';
$error = '';
$pageTitle = 'Standorte';

// ── Standort anlegen / bearbeiten ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name    = trim($_POST['s_name']    ?? '');
    $strasse = trim($_POST['s_strasse'] ?? '');
    $plz     = trim($_POST['s_plz']     ?? '');
    $ort     = trim($_POST['s_ort']     ?? '');
    $land    = trim($_POST['s_land']    ?? '');
    $sid     = (int)($_POST['sid'] ?? 0);

    if (empty($name)) {
        $error = 'Der Name des Standorts ist ein Pflichtfeld.';
    } else {
        try {
            if ($_POST['action'] === 'create') {
                $stmt = $pdo->prepare("
                    INSERT INTO Standorte (S_Name, S_Strasse, S_PLZ, S_Ort, S_Land, S_Creator, S_CreatorID)
                    VALUES (:name, :strasse, :plz, :ort, :land, :creator, :creator_id)
                ");
                $stmt->execute([
                    'name'       => $name,
                    'strasse'    => $strasse ?: null,
                    'plz'        => $plz ?: null,
                    'ort'        => $ort ?: null,
                    'land'       => $land ?: null,
                    'creator'    => $user['name'],
                    'creator_id' => $user['id'] ?: null
                ]);
                $success = 'Standort "' . htmlspecialchars($name) . '" wurde erfolgreich angelegt.';
            } elseif ($_POST['action'] === 'update' && $sid > 0) {
                $stmt = $pdo->prepare("
                    UPDATE Standorte 
                    SET S_Name = :name, S_Strasse = :strasse, S_PLZ = :plz, 
                        S_Ort = :ort, S_Land = :land, S_Changer = :changer, S_ChangerID = :changer_id
                    WHERE SID = :sid
                ");
                $stmt->execute([
                    'name'       => $name,
                    'strasse'    => $strasse ?: null,
                    'plz'        => $plz ?: null,
                    'ort'        => $ort ?: null,
                    'land'       => $land ?: null,
                    'changer'    => $user['name'],
                    'changer_id' => $user['id'] ?: null,
                    'sid'        => $sid
                ]);
                $success = 'Standort "' . htmlspecialchars($name) . '" wurde erfolgreich aktualisiert.';
            }
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
}

// ── Bearbeitungs-Modus laden ──────────────────────────────────
$editStandort = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM Standorte WHERE SID = ?");
    $stmt->execute([$_GET['edit']]);
    $editStandort = $stmt->fetch();
}

// ── Liste laden ───────────────────────────────────────────────
$standorte = $pdo->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM Inventar i WHERE i.SID = s.SID) as InventarCount
    FROM Standorte s
    ORDER BY S_Name ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Stammdaten</div>
    <h1 class="page-header__title">Standorte</h1>
    <p class="page-header__sub">Verwalten Sie die physischen Standorte und Büros Ihres Unternehmens.</p>
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
            <span class="card__title"><?= $editStandort ? 'Standort bearbeiten' : 'Standort hinzufügen' ?></span>
            <?php if ($editStandort): ?>
                <a href="/pages/standorte.php" class="btn btn--sm btn--outline">Abbrechen</a>
            <?php endif; ?>
        </div>
        <div class="card__body">
            <form method="POST" action="/pages/standorte.php">
                <input type="hidden" name="action" value="<?= $editStandort ? 'update' : 'create' ?>">
                <?php if ($editStandort): ?>
                    <input type="hidden" name="sid" value="<?= $editStandort['SID'] ?>">
                <?php endif; ?>

                <div class="form-grid form-grid--full">
                    <div class="form-group">
                        <label class="form-label" for="s_name">Bezeichnung <span>*</span></label>
                        <input class="form-control" type="text" id="s_name" name="s_name" 
                               value="<?= htmlspecialchars($editStandort['S_Name'] ?? '') ?>"
                               placeholder="z.B. Büro 1 – Erdgeschoss" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="s_strasse">Straße / Nr.</label>
                        <input class="form-control" type="text" id="s_strasse" name="s_strasse" 
                               value="<?= htmlspecialchars($editStandort['S_Strasse'] ?? '') ?>"
                               placeholder="z.B. Musterstraße 123">
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="s_plz">PLZ</label>
                            <input class="form-control" type="text" id="s_plz" name="s_plz" 
                                   value="<?= htmlspecialchars($editStandort['S_PLZ'] ?? '') ?>"
                                   placeholder="12345">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="s_ort">Ort</label>
                            <input class="form-control" type="text" id="s_ort" name="s_ort" 
                                   value="<?= htmlspecialchars($editStandort['S_Ort'] ?? '') ?>"
                                   placeholder="Musterstadt">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="s_land">Land</label>
                        <input class="form-control" type="text" id="s_land" name="s_land" 
                               value="<?= htmlspecialchars($editStandort['S_Land'] ?? '') ?>"
                               placeholder="z.B. Deutschland">
                    </div>
                    <div style="padding-top:12px;">
                        <button type="submit" class="btn <?= $editStandort ? 'btn--navy' : 'btn--gold' ?>" style="width:100%; justify-content:center;">
                            <?php if ($editStandort): ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Änderungen speichern
                            <?php else: ?>
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Standort speichern
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
            <span class="card__title"><?= count($standorte) ?> Standorte registriert</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Bezeichnung</th>
                        <th>Adresse</th>
                        <th>Angelegt am</th>
                        <th style="text-align:center;">Inventarstücke</th>
                        <th style="text-align:right;">Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($standorte)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:var(--gray-3);">Keine Standorte gefunden.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($standorte as $s): ?>
                        <tr class="<?= ($editStandort && $editStandort['SID'] == $s['SID']) ? 'table-active' : '' ?>">
                            <td>
                                <strong><?= htmlspecialchars($s['S_Name']) ?></strong><br>
                                <small style="color:var(--gray-3);">von <?= htmlspecialchars($s['S_Creator'] ?? '–') ?></small>
                            </td>
                            <td style="font-size: .85rem;">
                                <?php if ($s['S_Strasse'] || $s['S_Ort']): ?>
                                    <?= htmlspecialchars($s['S_Strasse'] ?? '') ?><br>
                                    <?= htmlspecialchars($s['S_PLZ'] ?? '') ?> <?= htmlspecialchars($s['S_Ort'] ?? '') ?>
                                    <?php if ($s['S_Land']): ?><br><small style="color:var(--gray-4);"><?= htmlspecialchars($s['S_Land']) ?></small><?php endif; ?>
                                <?php else: ?>
                                    <span style="color:var(--gray-4);">Keine Adresse</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--gray-3);"><?= date('d.m.Y', strtotime($s['S_CreateDate'])) ?></td>
                            <td style="text-align:center;">
                                <span class="badge <?= $s['InventarCount'] > 0 ? 'badge--gold' : 'badge--navy' ?>">
                                    <?= $s['InventarCount'] ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="?edit=<?= $s['SID'] ?>" class="btn btn--sm btn--outline" title="Bearbeiten">
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
