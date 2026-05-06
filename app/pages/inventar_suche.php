<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$pdo = getDB();
$barcode = trim($_GET['suche'] ?? '');

if (empty($barcode)) {
    header('Location: /pages/scanner.php');
    exit;
}

// Suche nach dem Artikel
$stmt = $pdo->prepare("SELECT InvID FROM Inventar WHERE Barcode = ?");
$stmt->execute([$barcode]);
$item = $stmt->fetch();

if ($item) {
    // Gefunden -> Direkt zum Bearbeiten
    header('Location: /pages/inventar_bearbeiten.php?id=' . $item['InvID']);
} else {
    // Nicht gefunden -> Zur Liste (zeigt Fehlermeldung/leere Suche)
    header('Location: /pages/inventar_liste.php?suche=' . urlencode($barcode));
}
exit;
