<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo     = getDB();
$user    = getCurrentUser();
$success = '';
$error   = '';
$pageTitle = 'Artikel anlegen';

$kategorien = $pdo->query('SELECT KID, K_Name FROM Kategorie ORDER BY K_Name')->fetchAll();
$standorte  = $pdo->query('SELECT SID, S_Name FROM Standorte ORDER BY S_Name')->fetchAll();

$barcode = 'RST-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['g_name']         ?? '');
    $hersteller   = trim($_POST['g_hersteller']   ?? '');
    $lieferant    = trim($_POST['g_lieferant']     ?? '');
    $kaufdatum    = $_POST['g_kaufdatum']   ?: null;
    $kosten       = $_POST['g_kosten'] !== '' ? (float) $_POST['g_kosten'] : null;
    $garantieende = $_POST['g_garantieende'] ?: null;
    $kid          = (int) ($_POST['kid'] ?? 0);
    $sid          = (int) ($_POST['sid'] ?? 0);

    if (empty($name)) {
        $error = 'Der Gerätename ist ein Pflichtfeld.';
    } elseif ($kid === 0) {
        $error = 'Bitte eine Kategorie auswählen.';
    } elseif ($sid === 0) {
        $error = 'Bitte einen Standort auswählen.';
    } else {
        try {
            $barcode = 'RST-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
            $stmt = $pdo->prepare('CALL sp_GeraetAnlegen(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $hersteller ?: null, $lieferant ?: null,
                $kaufdatum, $kosten, $garantieende, $kid, $sid, $user['id'], $barcode]);
            $success = 'Gerät "' . htmlspecialchars($name) . '" wurde erfolgreich angelegt. Barcode: ' . $barcode;
            $_POST = [];
            $barcode = 'RST-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));
        } catch (PDOException $e) {
            $error = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Stammdaten</div>
    <h1 class="page-header__title">Neuen Artikel anlegen</h1>
    <p class="page-header__sub">Neues Gerät erfassen und dem Inventar hinzufügen.</p>
</div>

<?php if ($success): ?>
<div class="alert alert--success fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
    <div><?= $success ?> <a href="/pages/barcodes.php" style="font-weight:700;text-decoration:underline;margin-left:6px;">→ Barcode anzeigen</a></div>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert--error fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<?php if (empty($kategorien) || empty($standorte)): ?>
<div class="alert alert--warning fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    Bitte zuerst mindestens eine <strong>Kategorie</strong> und einen <strong>Standort</strong> anlegen.
</div>
<?php else: ?>

<!-- Barcode-Vorschau -->
<div class="barcode-preview-box fade-in">
    <div class="barcode-preview-box__label">Nächster Barcode</div>
    <div class="barcode-preview-box__code"><?= htmlspecialchars($barcode) ?></div>
    <div class="barcode-preview-box__hint">Wird automatisch beim Speichern zugewiesen</div>
</div>

<div class="card fade-in">
    <div class="card__header">
        <span class="card__title">Geräteinformationen</span>
        <span class="badge badge--navy">* Pflichtfelder</span>
    </div>
    <div class="card__body">
        <form method="POST" action="/pages/inventar_erstellen.php">

            <!-- Pflichtfelder -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    </div>
                    <span class="form-section__title">Pflichtangaben</span>
                    <span class="form-section__hint">Alle Felder erforderlich</span>
                </div>
                <div class="form-grid">
                    <div class="form-group form-group--full">
                        <label class="form-label" for="g_name">Gerätename <span>*</span></label>
                        <input class="form-control" type="text" id="g_name" name="g_name"
                            placeholder="z.B. Dell UltraSharp 27&quot;"
                            value="<?= htmlspecialchars($_POST['g_name'] ?? '') ?>"
                            required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="kid">Kategorie <span>*</span></label>
                        <select class="form-control" id="kid" name="kid" required>
                            <option value="">– Kategorie wählen –</option>
                            <?php foreach ($kategorien as $k): ?>
                            <option value="<?= $k['KID'] ?>" <?= (($_POST['kid'] ?? '') == $k['KID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['K_Name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="sid">Standort <span>*</span></label>
                        <select class="form-control" id="sid" name="sid" required>
                            <option value="">– Standort wählen –</option>
                            <?php foreach ($standorte as $s): ?>
                            <option value="<?= $s['SID'] ?>" <?= (($_POST['sid'] ?? '') == $s['SID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($s['S_Name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Optionale Felder -->
            <div class="form-section">
                <div class="form-section__header">
                    <div class="form-section__icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                    </div>
                    <span class="form-section__title">Weitere Details</span>
                    <span class="form-section__hint">Optional</span>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="g_hersteller">Hersteller</label>
                        <input class="form-control" type="text" id="g_hersteller" name="g_hersteller"
                            placeholder="z.B. Dell, HP, Lenovo"
                            value="<?= htmlspecialchars($_POST['g_hersteller'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_lieferant">Lieferant</label>
                        <input class="form-control" type="text" id="g_lieferant" name="g_lieferant"
                            placeholder="z.B. Bechtle, Amazon"
                            value="<?= htmlspecialchars($_POST['g_lieferant'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_kaufdatum">Kaufdatum</label>
                        <input class="form-control" type="date" id="g_kaufdatum" name="g_kaufdatum"
                            value="<?= htmlspecialchars($_POST['g_kaufdatum'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_kosten">Anschaffungskosten (€)</label>
                        <input class="form-control" type="number" id="g_kosten" name="g_kosten"
                            step="0.01" min="0" placeholder="0.00"
                            value="<?= htmlspecialchars($_POST['g_kosten'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_garantieende">Garantieende</label>
                        <input class="form-control" type="date" id="g_garantieende" name="g_garantieende"
                            value="<?= htmlspecialchars($_POST['g_garantieende'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:12px;padding-top:8px;border-top:1px solid var(--gray-6);margin-top:8px;">
                <button type="submit" class="btn btn--gold btn--lg">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Artikel speichern
                </button>
                <a href="/pages/dashboard.php" class="btn btn--outline btn--lg">Abbrechen</a>
            </div>

        </form>
    </div>
</div>

<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
