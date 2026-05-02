<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getDB();
$user = getCurrentUser();
$success = '';
$error = '';
$pageTitle = 'Lieferanten';

// ── Lieferant anlegen ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name    = trim($_POST['l_name']    ?? '');
    $kontakt = trim($_POST['l_kontakt'] ?? '');
    $email   = trim($_POST['l_email']   ?? '');
    $telefon = trim($_POST['l_telefon'] ?? '');
    $land    = trim($_POST['l_land']    ?? '');
    $kundennummer = trim($_POST['l_kundennummer'] ?? '');

    if (empty($name)) {
        $error = 'Der Name des Lieferanten ist ein Pflichtfeld.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO Lieferant (L_Name, L_Kundennummer, L_Kontakt, L_Email, L_Telefon, L_Land, L_Creator, L_CreatorID)
                VALUES (:name, :kundennummer, :kontakt, :email, :telefon, :land, :creator, :creator_id)
            ");
            $stmt->execute([
                'name'         => $name,
                'kundennummer' => $kundennummer ?: null,
                'kontakt'      => $kontakt ?: null,
                'email'        => $email ?: null,
                'telefon'      => $telefon ?: null,
                'land'         => $land ?: null,
                'creator'      => $user['name'],
                'creator_id' => $user['id'] ?: null
            ]);
            $success = 'Lieferant "' . htmlspecialchars($name) . '" wurde erfolgreich angelegt.';
            $_POST = [];
        } catch (PDOException $e) {
            $error = 'Fehler beim Speichern: ' . $e->getMessage();
        }
    }
}

// ── Liste laden ───────────────────────────────────────────────
$lieferanten = $pdo->query("
    SELECT l.*, 
           (SELECT COUNT(*) FROM Geraete g WHERE g.LID = l.LID) as GeraeteCount
    FROM Lieferant l
    ORDER BY L_Name ASC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Stammdaten</div>
    <h1 class="page-header__title">Lieferanten</h1>
    <p class="page-header__sub">Verwalten Sie die Lieferanten und Bezugsquellen Ihrer Hardware.</p>
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
            <span class="card__title">Lieferant hinzufügen</span>
        </div>
        <div class="card__body">
            <form method="POST" action="/pages/lieferanten.php">
                <input type="hidden" name="action" value="create">
                <div class="form-grid form-grid--full">
                    <div class="form-group">
                        <label class="form-label" for="l_name">Unternehmen <span>*</span></label>
                        <input class="form-control" type="text" id="l_name" name="l_name" 
                               placeholder="z.B. Bechtle, Amazon Business" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="l_kundennummer">Kundennummer</label>
                        <input class="form-control" type="text" id="l_kundennummer" name="l_kundennummer" 
                               placeholder="Ihre Kd.-Nr. beim Lieferanten">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="l_kontakt">Ansprechpartner</label>
                        <input class="form-control" type="text" id="l_kontakt" name="l_kontakt" 
                               placeholder="Name der Kontaktperson">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="l_email">E-Mail</label>
                        <input class="form-control" type="email" id="l_email" name="l_email" 
                               placeholder="vertrieb@lieferant.de">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="l_telefon">Telefon</label>
                        <input class="form-control" type="tel" id="l_telefon" name="l_telefon" 
                               placeholder="+49 ...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="l_land">Land</label>
                        <input class="form-control" type="text" id="l_land" name="l_land" 
                               placeholder="z.B. Deutschland">
                    </div>
                    <div style="padding-top:12px;">
                        <button type="submit" class="btn btn--gold" style="width:100%; justify-content:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Lieferant speichern
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste Rechts -->
    <div class="card" style="grid-column: span 2;">
        <div class="card__header">
            <span class="card__title"><?= count($lieferanten) ?> Lieferanten registriert</span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Unternehmen</th>
                        <th>Kundennummer</th>
                        <th>Kontakt</th>
                        <th>E-Mail / Tel.</th>
                        <th style="text-align:center;">Geräte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lieferanten)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:var(--gray-3);">Keine Lieferanten gefunden.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($lieferanten as $l): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($l['L_Name']) ?></strong> <br><small style="color:var(--gray-4);"><?= htmlspecialchars($l['L_Land'] ?? '') ?></small></td>
                            <td style="font-family:monospace; color:var(--gray-3);"><?= htmlspecialchars($l['L_Kundennummer'] ?? '–') ?></td>
                            <td><?= htmlspecialchars($l['L_Kontakt'] ?? '–') ?></td>
                            <td style="font-size: .85rem;">
                                <?php if ($l['L_Email']): ?>
                                    <div style="display:flex; align-items:center; gap:4px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        <?= htmlspecialchars($l['L_Email']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($l['L_Telefon']): ?>
                                    <div style="display:flex; align-items:center; gap:4px; margin-top:2px;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                        <?= htmlspecialchars($l['L_Telefon']) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!$l['L_Email'] && !$l['L_Telefon']): ?>
                                    <span style="color:var(--gray-4);">–</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <span class="badge <?= $l['GeraeteCount'] > 0 ? 'badge--gold' : 'badge--navy' ?>">
                                    <?= $l['GeraeteCount'] ?>
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
