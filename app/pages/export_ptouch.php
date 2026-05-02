<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Auth check
$user = getCurrentUser();
if (!$user) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Unauthorized');
}

$pdo = getDB();

// Fetch all inventory items for the export
$inventar = $pdo->query(
    'SELECT i.Barcode, g.G_Name AS Geraet, k.K_Name AS Kategorie, s.S_Name AS Standort
     FROM Inventar i
     JOIN Geraete   g ON i.GID = g.GID
     JOIN Kategorie k ON g.KID = k.KID
     JOIN Standorte s ON i.SID = s.SID
     ORDER BY i.InvID DESC'
)->fetchAll(PDO::FETCH_ASSOC);

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="ptouch_barcodes_export_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Add BOM to fix UTF-8 in Excel and P-Touch Editor on Windows
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Output CSV header row
fputcsv($output, ['Barcode', 'Artikelname', 'Kategorie', 'Standort'], ';');

// Output data rows
foreach ($inventar as $row) {
    fputcsv($output, [
        $row['Barcode'],
        $row['Geraet'],
        $row['Kategorie'],
        $row['Standort']
    ], ';');
}

fclose($output);
exit;
