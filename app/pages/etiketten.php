<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$autoloadPath = __DIR__ . '/../vendor/picqer/vendor/autoload.php';
$barcodeAvailable = file_exists($autoloadPath);
if ($barcodeAvailable) require_once $autoloadPath;

$pdo = getDB();
$inventar = $pdo->query(
    'SELECT i.InvID, i.Barcode, g.G_Name AS Geraet, k.K_Name AS Kategorie 
     FROM Inventar i 
     JOIN Geraete g ON i.GID = g.GID 
     JOIN Kategorie k ON g.KID = k.KID 
     ORDER BY i.InvID DESC'
)->fetchAll();

$pageTitle = 'Etiketten Generator';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Inventar</div>
    <h1 class="page-header__title">Etiketten Generator</h1>
    <p class="page-header__sub">Erstellen Sie individuelle Barcode-Etiketten mit Logo und Text für den Ausdruck.</p>
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

<div class="dashboard-grid fade-in">
    <!-- Konfiguration -->
    <div class="card" style="grid-column: span 1;">
        <div class="card__header">
            <span class="card__title">Etikett konfigurieren</span>
        </div>
        <div class="card__body">
            <div class="form-grid form-grid--full">
                
                <div class="form-group">
                    <label class="form-label" for="itemSelect">Artikel auswählen</label>
                    <select class="form-control" id="itemSelect">
                        <option value="">– Benutzerdefinierter Text –</option>
                        <?php foreach ($inventar as $item): ?>
                            <option value="<?= htmlspecialchars($item['Barcode']) ?>" data-name="<?= htmlspecialchars($item['Geraet']) ?>">
                                <?= htmlspecialchars($item['Barcode']) ?> - <?= htmlspecialchars($item['Geraet']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="customBarcode">Barcode Nummer</label>
                    <input class="form-control" type="text" id="customBarcode" placeholder="z.B. RST-2026-ABCDE" value="RST-2026-DEMO1">
                </div>

                <div class="form-group">
                    <label class="form-label" for="customText">Beschriftung (Artikelname)</label>
                    <input class="form-control" type="text" id="customText" placeholder="z.B. Dell Monitor" value="Demo Gerät">
                </div>

                <div class="form-group">
                    <label class="form-label" for="logoUpload">Eigenes Logo hochladen (optional)</label>
                    <input class="form-control" type="file" id="logoUpload" accept="image/png, image/jpeg, image/svg+xml" style="padding: 8px;">
                    <small style="color: var(--gray-4); margin-top: 4px; display: block;">Das Logo wird nur lokal für die Vorschau verwendet.</small>
                </div>

                <div class="form-group" style="margin-top: 10px;">
                    <label class="form-label">Layout-Stil</label>
                    <select class="form-control" id="layoutStyle">
                        <option value="horizontal">Horizontal (Logo links, Barcode rechts)</option>
                        <option value="vertical">Vertikal (Logo oben, Barcode unten)</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <!-- Vorschau -->
    <div class="card" style="grid-column: span 2;">
        <div class="card__header">
            <span class="card__title">Vorschau & Druck</span>
            <div style="display:flex; gap:10px;">
                <a href="/pages/export_ptouch.php" class="btn btn--outline btn--sm" title="CSV-Datei für P-Touch Editor herunterladen">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    P-Touch Export (CSV)
                </a>
                <button id="btnPrintLabel" class="btn btn--primary btn--sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Etikett drucken
                </button>
            </div>
        </div>
        <div class="card__body" style="background: var(--gray-5); display: flex; align-items: center; justify-content: center; min-height: 300px; padding: 40px;">
            
            <!-- Etiketten Container (Das was gedruckt wird) -->
            <div id="labelPreviewContainer" style="background: white; border: 1px dashed var(--gray-4); padding: 20px; border-radius: 8px; box-shadow: var(--shadow); transition: all 0.3s ease;">
                
                <div id="labelLayout" style="display: flex; gap: 20px; align-items: center;">
                    
                    <!-- Logo Area -->
                    <div id="logoArea" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 80px; max-width: 120px;">
                        <img id="logoPreview" src="/assets/img/logo_placeholder.svg" style="max-width: 100%; max-height: 80px; object-fit: contain; display: none;" alt="Logo">
                        <div id="logoPlaceholderText" style="font-weight: 800; font-family: 'Syne', sans-serif; font-size: 1.2rem; color: var(--navy); text-align: center; border: 2px solid var(--navy); padding: 8px; border-radius: 6px;">RST<br>VEOLIA</div>
                    </div>

                    <!-- Barcode Area -->
                    <div id="barcodeArea" style="display: flex; flex-direction: column; align-items: center;">
                        <div id="labelItemName" style="font-weight: 700; font-size: 1.1rem; color: var(--gray-1); margin-bottom: 8px; text-align: center;">Demo Gerät</div>
                        <div id="svgContainer" style="margin-bottom: 4px;">
                            <!-- SVG is injected here via JS -->
                        </div>
                        <div id="labelBarcodeText" style="font-family: monospace; font-size: 0.95rem; color: var(--gray-2); letter-spacing: 2px;">RST-2026-DEMO1</div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const itemSelect = document.getElementById('itemSelect');
    const customBarcode = document.getElementById('customBarcode');
    const customText = document.getElementById('customText');
    const logoUpload = document.getElementById('logoUpload');
    const layoutStyle = document.getElementById('layoutStyle');
    
    const labelLayout = document.getElementById('labelLayout');
    const labelItemName = document.getElementById('labelItemName');
    const labelBarcodeText = document.getElementById('labelBarcodeText');
    const svgContainer = document.getElementById('svgContainer');
    const logoPreview = document.getElementById('logoPreview');
    const logoPlaceholderText = document.getElementById('logoPlaceholderText');

    // Simple JS Barcode Generator (Code128) - We use an API to fetch the SVG to avoid needing to do it in pure JS if picqer is not available in JS, but actually we can just call a tiny PHP endpoint or use a JS library. 
    // To keep it dependency-free on the frontend, let's use JsBarcode via CDN or just fetch from backend.
    // For now, let's include JsBarcode from CDN just for this page to make real-time generation smooth.
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
    script.onload = updatePreview;
    document.head.appendChild(script);

    function updatePreview() {
        const barcodeValue = customBarcode.value || 'RST-0000-00000';
        const textValue = customText.value || '';
        
        labelItemName.textContent = textValue;
        labelItemName.style.display = textValue ? 'block' : 'none';
        labelBarcodeText.textContent = barcodeValue;

        if (window.JsBarcode) {
            svgContainer.innerHTML = '<svg id="jsbarcode-svg"></svg>';
            JsBarcode("#jsbarcode-svg", barcodeValue, {
                format: "CODE128",
                displayValue: false,
                width: 2,
                height: 60,
                margin: 0,
                lineColor: "#0F2D52"
            });
        }

        // Layout Update
        if (layoutStyle.value === 'vertical') {
            labelLayout.style.flexDirection = 'column';
            labelLayout.style.textAlign = 'center';
        } else {
            labelLayout.style.flexDirection = 'row';
            labelLayout.style.textAlign = 'left';
        }
    }

    // Event Listeners
    itemSelect.addEventListener('change', function() {
        if (this.value) {
            customBarcode.value = this.value;
            customText.value = this.options[this.selectedIndex].getAttribute('data-name');
        } else {
            customBarcode.value = '';
            customText.value = '';
        }
        updatePreview();
    });

    customBarcode.addEventListener('input', updatePreview);
    customText.addEventListener('input', updatePreview);
    layoutStyle.addEventListener('change', updatePreview);

    // Handle Logo Upload Preview
    logoUpload.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                logoPreview.src = event.target.result;
                logoPreview.style.display = 'block';
                logoPlaceholderText.style.display = 'none';
            }
            reader.readAsDataURL(file);
        } else {
            logoPreview.style.display = 'none';
            logoPreview.src = '';
            logoPlaceholderText.style.display = 'block';
        }
    });

    // Print functionality
    document.getElementById('btnPrintLabel').addEventListener('click', function() {
        const printContent = document.getElementById('labelPreviewContainer').innerHTML;
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Etikett Drucken</title>
                    <style>
                        @page { margin: 0; size: auto; }
                        body { 
                            margin: 0; 
                            padding: 20px; 
                            display: flex; 
                            justify-content: center; 
                            align-items: flex-start;
                            font-family: sans-serif;
                        }
                        /* Reset print specific styles */
                        #labelLayout { 
                            display: flex; 
                            gap: 20px; 
                            align-items: center; 
                        }
                        /* Inject specific styles from our preview */
                        ${layoutStyle.value === 'vertical' ? '#labelLayout { flex-direction: column; text-align: center; }' : ''}
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() { window.close(); }, 500);
                        }
                    <\/script>
                </body>
            </html>
        `);
        printWindow.document.close();
    });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
