<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';

$autoloadPath = __DIR__ . '/../vendor/picqer/vendor/autoload.php';
$barcodeAvailable = file_exists($autoloadPath);
if ($barcodeAvailable) require_once $autoloadPath;

requireLogin();
$pdo = getDB();

$inventar = $pdo->query(
    'SELECT i.InvID, i.Barcode,
            g.GID, g.G_Name, h.H_Name AS G_Hersteller,
            k.K_Name AS Kategorie,
            s.S_Name AS Standort
      FROM Inventar i
      JOIN Geraete   g ON i.GID = g.GID
      JOIN Kategorie k ON g.KID = k.KID
      JOIN Standorte s ON i.SID = s.SID
      LEFT JOIN Hersteller h ON g.HID = h.HID
      ORDER BY i.InvID DESC'
)->fetchAll();

$hatArtikel = !empty($inventar);
$anzahl     = count($inventar);

$pageTitle = 'Barcodes';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Inventar</div>
    <h1 class="page-header__title">Barcodes</h1>
    <p class="page-header__sub">Automatisch generierte Barcodes für alle Inventarartikel.</p>
</div>

<?php if (!$barcodeAvailable): ?>
<div class="alert alert--warning fade-in">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <div>
        <strong>Barcode-Bibliothek nicht gefunden.</strong>
        Bitte in XAMPP ausführen:
        <code style="display:inline-block;margin-top:6px;background:rgba(0,0,0,.08);padding:2px 8px;border-radius:4px;font-family:monospace;font-size:.85em;">
            cd rst-inventar/app/vendor &amp;&amp; git clone https://github.com/picqer/php-barcode-generator.git picqer
        </code>
    </div>
</div>
<?php endif; ?>

<?php if (!$hatArtikel): ?>
<div class="card fade-in">
    <div class="card__body">
        <div class="empty-state">
            <div class="empty-state__icon">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14M3 5h2M7 5h2"/></svg>
            </div>
            <div class="empty-state__title">Noch keine Artikel vorhanden</div>
            <div class="empty-state__text">Legen Sie zuerst mindestens einen Artikel an, um Barcodes zu sehen.</div>
            <a href="/pages/inventar_erstellen.php" class="btn btn--gold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Ersten Artikel anlegen
            </a>
        </div>
    </div>
</div>

<?php else: ?>

<!-- Stats + Aktions-Leiste -->
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:28px;" class="fade-in">
    <div class="stats-row" style="margin:0;">
        <span class="stats-pill">
            <span class="stats-pill__dot stats-pill__dot--navy"></span>
            <?= $anzahl ?> <?= $anzahl === 1 ? 'Artikel' : 'Artikel' ?> gesamt
        </span>
    </div>
    <div style="display:flex;gap:10px;">
        <button id="printAll" class="btn btn--primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Alle drucken
        </button>
        <a href="/pages/inventar_erstellen.php" class="btn btn--outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            Neuer Artikel
        </a>
    </div>
</div>

<!-- Barcode-Grid -->
<div class="barcode-grid fade-in">
<?php foreach ($inventar as $item):
    $svgBarcode = '';
    if ($barcodeAvailable) {
        try {
            $generator = new Picqer\Barcode\BarcodeGeneratorSVG();
            $svgBarcode = $generator->getBarcode($item['Barcode'], $generator::TYPE_CODE_128, 2, 60);
        } catch (Exception $e) { $svgBarcode = ''; }
    }
?>
    <div class="barcode-card fade-in">

        <!-- Kartenheader: Name -->
        <div style="width:100%;text-align:center;padding-bottom:2px;">
            <div class="barcode-card__name"><?= htmlspecialchars($item['G_Name']) ?></div>
            <?php if ($item['G_Hersteller']): ?>
                <div style="font-size:.75rem;color:var(--gray-4);margin-top:2px;"><?= htmlspecialchars($item['G_Hersteller']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Badges -->
        <div style="display:flex;gap:6px;flex-wrap:wrap;justify-content:center;">
            <span class="badge badge--gold"><?= htmlspecialchars($item['Kategorie']) ?></span>
            <span class="badge badge--navy"><?= htmlspecialchars($item['Standort']) ?></span>
        </div>

        <!-- Barcode-Bild mit Zoom-Overlay -->
        <div class="barcode-card__img"
             onclick="bcShowModal(this)"
             data-name="<?= htmlspecialchars($item['G_Name'], ENT_QUOTES) ?>"
             data-code="<?= htmlspecialchars($item['Barcode'], ENT_QUOTES) ?>"
             title="Zum Vergrößern klicken">
            <?php if ($svgBarcode): ?>
                <?= $svgBarcode ?>
            <?php else: ?>
                <div style="padding:16px 24px;font-family:monospace;font-size:.85rem;color:var(--gray-3);text-align:center;">
                    <?= htmlspecialchars($item['Barcode']) ?>
                </div>
            <?php endif; ?>
            <!-- Zoom-Hint -->
            <div class="barcode-card__zoom">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
            </div>
        </div>

        <!-- Barcode-Nummer -->
        <div class="barcode-card__id" style="background:var(--gray-5);padding:5px 12px;border-radius:6px;font-size:.78rem;">
            <?= htmlspecialchars($item['Barcode']) ?>
        </div>

        <!-- Aktionen -->
        <div class="barcode-card__actions">
            <button class="btn btn--outline btn--sm" data-print-barcode>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Drucken
            </button>
            <button class="btn btn--outline btn--sm" onclick="bcShowModal(this.closest('.barcode-card').querySelector('.barcode-card__img'))">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                Vergrößern
            </button>
        </div>

    </div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<script>
function bcPrint(name, code, svgHtml) {
    var win = window.open('', '_blank');
    if (!win) { alert('Bitte den Popup-Blocker fuer diese Seite deaktivieren.'); return; }
    win.document.write(
        '<!DOCTYPE html><html><head><title>Barcode</title>' +
        '<style>body{font-family:sans-serif;text-align:center;padding:40px;}' +
        'h3{margin-bottom:4px;color:#0F2D52;font-size:1.1rem;}' +
        'p{font-size:.8rem;color:#6B7280;margin-bottom:24px;font-family:monospace;}' +
        'svg{width:300px;height:110px;}</style></head><body>' +
        '<h3>' + name + '</h3><p>' + code + '</p>' + svgHtml +
        '<scr' + 'ipt>window.onload=function(){window.print();window.close();}</scr' + 'ipt>' +
        '</body></html>'
    );
    win.document.close();
}

function bcShowModal(imgDiv) {
    var name  = imgDiv.getAttribute('data-name') || '';
    var code  = imgDiv.getAttribute('data-code') || '';
    var svgEl = imgDiv.querySelector('svg');

    document.getElementById('barcodeModalName').textContent = name;
    document.getElementById('barcodeModalId').textContent   = code;

    var container = document.getElementById('barcodeModalSvg');
    container.innerHTML = '';
    if (svgEl) {
        var clone = svgEl.cloneNode(true);
        clone.removeAttribute('width');
        clone.removeAttribute('height');
        clone.style.width  = '100%';
        clone.style.height = '120px';
        container.appendChild(clone);
    } else {
        container.innerHTML = '<div style="font-family:monospace;font-size:1rem;padding:20px;background:#f3f4f6;border-radius:8px;">' + code + '</div>';
    }

    document.getElementById('barcodeModalPrint').onclick = function() {
        bcPrint(name, code, svgEl ? svgEl.outerHTML : '');
    };
    document.getElementById('barcodeModal').style.display = 'flex';
}

function bcCloseModal() {
    document.getElementById('barcodeModal').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-print-barcode]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var imgDiv = btn.closest('.barcode-card').querySelector('.barcode-card__img');
            var name   = imgDiv ? (imgDiv.getAttribute('data-name') || '') : '';
            var code   = imgDiv ? (imgDiv.getAttribute('data-code') || '') : '';
            var svgEl  = imgDiv ? imgDiv.querySelector('svg') : null;
            bcPrint(name, code, svgEl ? svgEl.outerHTML : '');
        });
    });

    var m = document.getElementById('barcodeModal');
    if (m) {
        document.getElementById('barcodeModalClose').addEventListener('click', bcCloseModal);
        document.getElementById('barcodeModalCloseBtn').addEventListener('click', bcCloseModal);
        m.addEventListener('click', function(e) { if (e.target === m) bcCloseModal(); });
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') bcCloseModal();
    });
});
</script>

<!-- Barcode Vergroesserungs-Modal -->
<div id="barcodeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:20px;padding:40px 36px 32px;max-width:500px;width:92%;box-shadow:0 32px 80px rgba(0,0,0,.3);text-align:center;position:relative;animation:fadeIn .2s ease;">
        <button id="barcodeModalClose" title="Schliessen" style="position:absolute;top:14px;right:16px;background:none;border:none;font-size:1.6rem;line-height:1;cursor:pointer;color:#9CA3AF;transition:color .15s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9CA3AF'">&times;</button>
        <div style="width:44px;height:44px;background:var(--navy-light);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0F2D52" stroke-width="2"><path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14M3 5h2M7 5h2"/></svg>
        </div>
        <div id="barcodeModalName" style="font-size:1.15rem;font-weight:700;color:#0F2D52;margin-bottom:4px;"></div>
        <div id="barcodeModalId" style="font-size:.82rem;color:#9CA3AF;margin-bottom:24px;font-family:monospace;letter-spacing:.06em;background:#F9FAFB;display:inline-block;padding:4px 14px;border-radius:20px;"></div>
        <div id="barcodeModalSvg" style="margin-bottom:28px;padding:20px 16px;background:#F9FAFB;border-radius:12px;border:1px solid #E5E7EB;"></div>
        <div style="display:flex;gap:10px;justify-content:center;">
            <button id="barcodeModalPrint" class="btn btn--primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Drucken
            </button>
            <button id="barcodeModalCloseBtn" class="btn btn--outline">Schliessen</button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
