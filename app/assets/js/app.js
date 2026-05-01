//Verstecken von Warung Alerts nach einer Dauer von 4 Sekunden
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity .4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 4000);
    });

    // -------------------------------------------------------
    // Formular: Kosten-Feld – nur Zahlen erlaubt
    // -------------------------------------------------------
    var kostenInput = document.getElementById('g_kosten');
    if (kostenInput) {
        kostenInput.addEventListener('input', function() {
            kostenInput.value = kostenInput.value.replace(/[^0-9.,]/g, '');
        });
    }
});
