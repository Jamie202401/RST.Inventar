<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo     = getDB();
$user    = getCurrentUser();
$success = '';
$error   = '';
$pageTitle = 'Artikel bearbeiten';

$invID = (int) ($_GET['id'] ?? 0);

if ($invID <= 0) {
    header('Location: /pages/inventar_liste.php');
    exit;
}

// ── Stammdaten laden ──────────────────────────────────────────
$kategorien  = $pdo->query('SELECT KID, K_Name FROM Kategorie ORDER BY K_Name')->fetchAll();
$standorte   = $pdo->query('SELECT SID, S_Name FROM Standorte ORDER BY S_Name')->fetchAll();
$hersteller  = $pdo->query('SELECT HID, H_Name FROM Hersteller ORDER BY H_Name')->fetchAll();
$lieferanten = $pdo->query('SELECT LID, L_Name FROM Lieferant ORDER BY L_Name')->fetchAll();

// ── Artikel laden ──────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT i.*, g.* 
    FROM Inventar i
    JOIN Geraete g ON i.GID = g.GID
    WHERE i.InvID = ?
");
$stmt->execute([$invID]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: /pages/inventar_liste.php');
    exit;
}

// ── Speichern ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['g_name']         ?? '');
    $hid          = (int) ($_POST['hid']          ?? 0);
    $lid          = (int) ($_POST['lid']          ?? 0);
    $kaufdatum    = $_POST['g_kaufdatum']   ?: null;
    $kosten       = $_POST['g_kosten'] !== '' ? (float) $_POST['g_kosten'] : null;
    $garantieende = $_POST['g_garantieende'] ?: null;
    $kid          = (int) ($_POST['kid'] ?? 0);
    $sid          = (int) ($_POST['sid'] ?? 0);
    $barcode      = trim($_POST['barcode'] ?? '');

    if (empty($name)) {
        $error = 'Der Gerätename ist ein Pflichtfeld.';
    } elseif ($kid === 0) {
        $error = 'Bitte eine Kategorie auswählen.';
    } elseif ($sid === 0) {
        $error = 'Bitte einen Standort auswählen.';
    } elseif (empty($barcode)) {
        $error = 'Der Barcode darf nicht leer sein.';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Geraete updaten
            $stmt = $pdo->prepare("
                UPDATE Geraete 
                SET G_Name = ?, HID = ?, LID = ?, G_Kaufdatum = ?, 
                    G_Kosten = ?, G_Garantieende = ?, KID = ?, 
                    G_Changer = ?, G_ChangerID = ?
                WHERE GID = ?
            ");
            $stmt->execute([
                $name, 
                $hid ?: null, 
                $lid ?: null, 
                $kaufdatum, 
                $kosten, 
                $garantieende, 
                $kid, 
                $user['name'], 
                $user['id'], 
                $item['GID']
            ]);

            // 2. Inventar updaten
            $stmt = $pdo->prepare("
                UPDATE Inventar 
                SET Barcode = ?, SID = ?, BID = ?
                WHERE InvID = ?
            ");
            $stmt->execute([
                $barcode, 
                $sid, 
                $user['id'], 
                $invID
            ]);

            $pdo->commit();
            $success = 'Die Änderungen wurden erfolgreich gespeichert.';
            
            // Item neu laden
            $stmt = $pdo->prepare("SELECT i.*, g.* FROM Inventar i JOIN Geraete g ON i.GID = g.GID WHERE i.InvID = ?");
            $stmt->execute([$invID]);
            $item = $stmt->fetch();
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Inventar</div>
    <h1 class="page-header__title">Artikel bearbeiten</h1>
    <p class="page-header__sub">Details für Gerät <?= htmlspecialchars($item['Barcode']) ?> anpassen.</p>
</div>

<?php if ($success): ?>
<div class="alert alert--success fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <div><?= $success ?> <a href="/pages/inventar_liste.php" style="font-weight:700;text-decoration:underline;margin-left:6px;">→ Zurück zur Liste</a></div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert--error fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="card fade-in">
    <div class="card__header">
        <span class="card__title">Geräteinformationen</span>
        <span class="badge badge--navy">* Pflichtfelder</span>
    </div>
    <div class="card__body">
        <form method="POST">
            <!-- Stammdaten -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <span class="form-section__title">Identifikation & Standort</span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="barcode">Barcode <span>*</span></label>
                        <input class="form-control" type="text" id="barcode" name="barcode"
                            value="<?= htmlspecialchars($item['Barcode']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_name">Gerätename <span>*</span></label>
                        <input class="form-control" type="text" id="g_name" name="g_name"
                            value="<?= htmlspecialchars($item['G_Name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="kid">Kategorie <span>*</span></label>
                        <select class="form-control" id="kid" name="kid" required>
                            <?php foreach ($kategorien as $k): ?>
                            <option value="<?= $k['KID'] ?>" <?= ($item['KID'] == $k['KID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['K_Name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sid">Standort <span>*</span></label>
                        <select class="form-control" id="sid" name="sid" required>
                            <?php foreach ($standorte as $s): ?>
                            <option value="<?= $s['SID'] ?>" <?= ($item['SID'] == $s['SID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['S_Name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Details -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                    </div>
                    <span class="form-section__title">Kaufmännische Details</span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="hid">Hersteller</label>
                        <select class="form-control" id="hid" name="hid">
                            <option value="">– Keiner –</option>
                            <?php foreach ($hersteller as $h): ?>
                            <option value="<?= $h['HID'] ?>" <?= ($item['HID'] == $h['HID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($h['H_Name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="lid">Lieferant</label>
                        <select class="form-control" id="lid" name="lid">
                            <option value="">– Keiner –</option>
                            <?php foreach ($lieferanten as $l): ?>
                            <option value="<?= $l['LID'] ?>" <?= ($item['LID'] == $l['LID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($l['L_Name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_kaufdatum">Kaufdatum</label>
                        <input class="form-control" type="date" id="g_kaufdatum" name="g_kaufdatum"
                            value="<?= htmlspecialchars($item['G_Kaufdatum'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_kosten">Anschaffungskosten (€)</label>
                        <input class="form-control" type="number" id="g_kosten" name="g_kosten"
                            step="0.01" min="0" placeholder="0.00"
                            value="<?= htmlspecialchars($item['G_Kosten'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_garantieende">Garantieende</label>
                        <input class="form-control" type="date" id="g_garantieende" name="g_garantieende"
                            value="<?= htmlspecialchars($item['G_Garantieende'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:12px;padding-top:8px;border-top:1px solid var(--gray-6);margin-top:8px;">
                <button type="submit" class="btn btn--gold btn--lg">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Änderungen speichern
                </button>
                <a href="/pages/inventar_liste.php" class="btn btn--outline btn--lg">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
