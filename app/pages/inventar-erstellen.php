<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

requireLogin();
$pdo     = getDB();
$user    = getCurrentUser();
$success = '';
$error   = '';

$kategorien = $pdo->query('SELECT KID, K_Name FROM Kategorie ORDER BY K_Name')->fetchAll();
$standorte  = $pdo->query('SELECT SID, S_Name FROM Standorte ORDER BY S_Name')->fetchAll();

$maxInv      = $pdo->query('SELECT MAX(InvID) FROM Inventar')->fetchColumn();
$nextNr      = str_pad((int) $maxInv + 1, 4, '0', STR_PAD_LEFT);
$nextBarcode = BARCODE_PREFIX . $nextNr;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'g_name'        => trim($_POST['g_name']        ?? ''),
        'g_hersteller'  => trim($_POST['g_hersteller']  ?? ''),
        'g_lieferant'   => trim($_POST['g_lieferant']   ?? ''),
        'g_kaufdatum'   => trim($_POST['g_kaufdatum']   ?? ''),
        'g_kosten'      => trim($_POST['g_kosten']      ?? ''),
        'g_garantieende'=> trim($_POST['g_garantieende']?? ''),
        'kid'           => (int) ($_POST['kid']         ?? 0),
        'sid'           => (int) ($_POST['sid']         ?? 0),
        'barcode'       => trim($_POST['barcode']       ?? $nextBarcode),
    ];

    if (empty($fields['g_name'])) {
        $error = 'Gerätename ist ein Pflichtfeld.';
    } elseif ($fields['kid'] === 0) {
        $error = 'Bitte eine Kategorie auswählen.';
    } elseif ($fields['sid'] === 0) {
        $error = 'Bitte einen Standort auswählen.';
    } else {
        try {
            $stmt = $pdo->prepare('CALL sp_GeraeteAnlegen(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $fields['g_name'],
                $fields['g_hersteller'] ?: null,
                $fields['g_lieferant']  ?: null,
                $fields['g_kaufdatum']  ?: null,
                $fields['g_kosten'] !== '' ? str_replace(',', '.', $fields['g_kosten']) : null,
                $fields['g_garantieende'] ?: null,
                $fields['kid'],
                $fields['sid'],
                $user['id'],
                $fields['barcode'],
            ]);
            $success = 'Gerät "' . htmlspecialchars($fields['g_name']) . '" wurde erfolgreich angelegt.';
            $maxInv      = $pdo->query('SELECT MAX(InvID) FROM Inventar')->fetchColumn();
            $nextNr      = str_pad((int) $maxInv + 1, 4, '0', STR_PAD_LEFT);
            $nextBarcode = BARCODE_PREFIX . $nextNr;
            $fields      = array_fill_keys(array_keys($fields), '');
            $fields['barcode'] = $nextBarcode;
        } catch (PDOException $e) {
            $error = str_contains($e->getMessage(), 'Duplicate entry')
                ? 'Dieser Barcode existiert bereits. Bitte einen anderen verwenden.'
                : 'Fehler beim Anlegen: ' . $e->getMessage();
        }
    }
}

renderHeader('Artikel anlegen', 'erstellen');
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Stammdaten</div>
    <h1 class="page-header__title">Neuen Artikel anlegen</h1>
    <p class="page-header__sub">Erfassen Sie alle relevanten Informationen zum neuen Inventarartikel.</p>
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

<!-- Barcode-Vorschau-Box -->
<div class="barcode-preview-box fade-in">
    <div class="barcode-preview-box__label">Nächster Barcode</div>
    <div class="barcode-preview-box__code"><?= htmlspecialchars($nextBarcode) ?></div>
    <div class="barcode-preview-box__hint">Wird automatisch beim Speichern zugewiesen</div>
</div>

<div class="card fade-in">
    <div class="card__header">
        <span class="card__title">Geräteinformationen</span>
        <span class="badge badge--navy">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            * Pflichtfelder
        </span>
    </div>
    <div class="card__body">
        <form method="POST" action="/pages/inventar-erstellen.php">

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
                            value="<?= htmlspecialchars($fields['g_name'] ?? '') ?>" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="kid">Kategorie <span>*</span></label>
                        <select class="form-control" id="kid" name="kid" required>
                            <option value="">– Kategorie wählen –</option>
                            <?php foreach ($kategorien as $k): ?>
                            <option value="<?= $k['KID'] ?>" <?= (isset($fields['kid']) && $fields['kid'] == $k['KID']) ? 'selected' : '' ?>>
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
                            <option value="<?= $s['SID'] ?>" <?= (isset($fields['sid']) && $fields['sid'] == $s['SID']) ? 'selected' : '' ?>>
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
                            placeholder="z.B. Dell"
                            value="<?= htmlspecialchars($fields['g_hersteller'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_lieferant">Lieferant</label>
                        <input class="form-control" type="text" id="g_lieferant" name="g_lieferant"
                            placeholder="z.B. Bechtle"
                            value="<?= htmlspecialchars($fields['g_lieferant'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_kaufdatum">Kaufdatum</label>
                        <input class="form-control" type="date" id="g_kaufdatum" name="g_kaufdatum"
                            value="<?= htmlspecialchars($fields['g_kaufdatum'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_kosten">Anschaffungskosten (€)</label>
                        <input class="form-control" type="text" id="g_kosten" name="g_kosten"
                            placeholder="z.B. 549,00"
                            value="<?= htmlspecialchars($fields['g_kosten'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="g_garantieende">Garantieende</label>
                        <input class="form-control" type="date" id="g_garantieende" name="g_garantieende"
                            value="<?= htmlspecialchars($fields['g_garantieende'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="barcode">Barcode</label>
                        <input class="form-control" type="text" id="barcode" name="barcode"
                            value="<?= htmlspecialchars($fields['barcode'] ?? $nextBarcode) ?>"
                            style="font-family:monospace;">
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:12px;padding-top:8px;border-top:1px solid var(--gray-6);margin-top:8px;">
                <button type="submit" class="btn btn--gold btn--lg">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    Artikel anlegen
                </button>
                <a href="/pages/dashboard.php" class="btn btn--outline btn--lg">Abbrechen</a>
            </div>

        </form>
    </div>
</div>

<?php renderFooter(); ?>
