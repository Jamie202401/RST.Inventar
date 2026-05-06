<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pageTitle = 'Scanner';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-header fade-in">
    <div class="page-header__eyebrow">Inventar</div>
    <h1 class="page-header__title">Barcode Scanner</h1>
    <p class="page-header__sub">Scannen Sie einen Barcode, um den Artikel direkt aufzurufen.</p>
</div>

<div class="card fade-in" style="max-width: 600px; margin: 0 auto; text-align: center; padding: 40px 20px;">
    <div class="card__body">
        <!-- Header Section -->
        <div style="margin-bottom: 24px;">
            <div style="width: 80px; height: 80px; background: var(--navy-light); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" stroke-width="2">
                    <path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14M3 5h2M7 5h2"/>
                </svg>
            </div>
            <h2 id="scanner-title" style="font-family: 'Syne', sans-serif; font-size: 1.5rem; color: var(--navy); margin-bottom: 8px;">Bereit zum Scannen</h2>
        </div>

        <div id="manual-ui">
            <p style="color: var(--gray-3); font-size: 0.95rem; margin-bottom: 24px;">Barcode mit Hardware-Scanner einlesen oder manuell tippen.</p>
            
            <form action="/pages/inventar_suche.php" method="GET">
                <input type="text" 
                       name="suche" 
                       id="barcodeInput" 
                       class="form-control" 
                       style="font-size: 1.5rem; padding: 20px; text-align: center; width: 100%; border: 2px solid var(--navy-mid); box-shadow: var(--shadow-lg); margin-bottom: 20px;" 
                       placeholder="Barcode..." 
                       autofocus 
                       autocomplete="off">
                
                <button type="submit" class="btn btn--gold btn--lg" style="width: 100%; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    Artikel suchen
                </button>
            </form>

            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px dashed var(--gray-6);">
                <button id="start-camera" class="btn btn--navy" style="width: 100%; justify-content: center; gap: 10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>
                    </svg>
                    Kamera als Scanner nutzen
                </button>
            </div>
        </div>

        <!-- State 2: Camera Live View -->
        <div id="camera-ui" style="display: none;">
            <p style="color: var(--gray-3); font-size: 0.95rem; margin-bottom: 20px;">Halten Sie den Barcode in das Sichtfeld der Kamera.</p>
            <div id="reader" style="width: 100%; border-radius: 12px; overflow: hidden; border: none !important; background: #000;"></div>
            <button id="stop-camera" class="btn btn--outline" style="margin-top: 24px; width: 100%; justify-content: center;">
                Abbrechen & Zurück
            </button>
        </div>
    </div>
</div>

<!-- Library for QR/Barcode Scanning -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('barcodeInput');
        const startBtn = document.getElementById('start-camera');
        const stopBtn = document.getElementById('stop-camera');
        const manualUI = document.getElementById('manual-ui');
        const cameraUI = document.getElementById('camera-ui');
        const scannerTitle = document.getElementById('scanner-title');
        
        let html5QrCode;

        input.focus();
        
        // Refocus if user clicks outside (for hardware scanners)
        document.body.addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A' && !html5QrCode) {
                input.focus();
            }
        });

        // Toggle Camera Scanner
        startBtn.addEventListener('click', function() {
            // Check for Secure Context (HTTPS)
            if (!window.isSecureContext && window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                alert("BROWSER-BLOCKADE:\n\nDie Kamera kann nur über eine verschlüsselte Verbindung (HTTPS) genutzt werden.");
                return;
            }

            manualUI.style.display = 'none';
            cameraUI.style.display = 'block';
            scannerTitle.innerText = "Kamera aktiv";
            
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 280, height: 180 } };

            html5QrCode.start(
                { facingMode: "environment" }, 
                config,
                (decodedText) => {
                    input.value = decodedText;
                    stopScanner();
                    input.form.submit();
                }
            ).catch((err) => {
                console.error("Camera access failed", err);
                alert("Kamera-Fehler: " + err);
                stopScanner();
            });
        });

        stopBtn.addEventListener('click', stopScanner);

        function stopScanner() {
            const cleanup = () => {
                html5QrCode = null;
                cameraUI.style.display = 'none';
                manualUI.style.display = 'block';
                scannerTitle.innerText = "Bereit zum Scannen";
                input.focus();
            };

            if (html5QrCode) {
                html5QrCode.stop().then(cleanup).catch(cleanup);
            } else {
                cleanup();
            }
        }
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
