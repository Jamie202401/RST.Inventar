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
        <div style="margin-bottom: 24px;">
            <div style="width: 80px; height: 80px; background: var(--navy-light); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--navy)" stroke-width="2">
                    <path d="M3 5v14M7 5v14M11 5v14M15 5v10M19 5v14M3 5h2M7 5h2"/>
                </svg>
            </div>
            <h2 style="font-family: 'Syne', sans-serif; font-size: 1.5rem; color: var(--navy); margin-bottom: 8px;">Bereit zum Scannen</h2>
            <p style="color: var(--gray-3); font-size: 0.95rem;">Bitte klicken Sie in das Feld unten und scannen Sie den Barcode mit Ihrem Hardware-Scanner.</p>
        </div>

        <form action="/pages/inventar_liste.php" method="GET">
            <input type="text" 
                   name="suche" 
                   id="barcodeInput" 
                   class="form-control" 
                   style="font-size: 1.5rem; padding: 20px; text-align: center; width: 100%; border: 2px solid var(--navy-mid); box-shadow: var(--shadow-lg);" 
                   placeholder="Barcode hier einscannen..." 
                   autofocus 
                   autocomplete="off">
            
            <button type="submit" class="btn btn--gold btn--lg" style="margin-top: 24px; width: 100%; justify-content: center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Artikel suchen
            </button>
        </form>
    </div>
</div>

<script>
    // Keep focus on the input field so hardware scanners always work
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.getElementById('barcodeInput');
        input.focus();
        
        // Refocus if user clicks outside
        document.body.addEventListener('click', function(e) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'A') {
                input.focus();
            }
        });
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
