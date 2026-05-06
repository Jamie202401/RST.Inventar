<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

startSession();

// ── State-Prüfung ──────────────────────────────────────────────
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
    http_response_code(400);
    $expected = $_SESSION['oauth_state'] ?? 'LEER';
    $received = $_GET['state'] ?? 'LEER';
    $cookie = isset($_COOKIE['PHPSESSID']) ? 'Ja' : 'Nein';
    die("Ungültiger State-Parameter.<br>Erwartet: {$expected}<br>Erhalten: {$received}<br>Cookie empfangen: {$cookie}<br><br><a href='/'>Zurück zur Anmeldung</a>");
}
unset($_SESSION['oauth_state']);

if (isset($_GET['error'])) {
    http_response_code(401);
    $msg = htmlspecialchars($_GET['error_description'] ?? $_GET['error']);
    die("Keycloak-Fehler: {$msg}. <a href='/'>Zurück</a>");
}

$code = $_GET['code'] ?? '';
if (!$code) {
    http_response_code(400);
    die('Kein Authorization-Code empfangen. <a href="/">Zurück</a>');
}

// ── Token-Exchange (PHP-Container → Keycloak intern) ───────────
$tokenUrl    = KC_BASE_INTERNAL . '/realms/' . KC_REALM . '/protocol/openid-connect/token';
$redirectUri = APP_URL . '/callback.php';

$ctx = stream_context_create(['http' => [
    'method'        => 'POST',
    'header'        => "Content-Type: application/x-www-form-urlencoded\r\n",
    'content'       => http_build_query([
        'grant_type'    => 'authorization_code',
        'code'          => $code,
        'redirect_uri'  => $redirectUri,
        'client_id'     => KC_CLIENT_ID,
        'client_secret' => KC_CLIENT_SECRET,
    ]),
    'ignore_errors' => true,
]]);

$response = file_get_contents($tokenUrl, false, $ctx);
if ($response === false) {
    http_response_code(502);
    die('Token-Austausch fehlgeschlagen. Keycloak nicht erreichbar.');
}

$tokens = json_decode($response, true);
if (empty($tokens['access_token'])) {
    http_response_code(401);
    die('Kein Access-Token erhalten: ' . htmlspecialchars($response));
}

// ── JWT-Payload lesen ─────────────────────────────────────────
$parts      = explode('.', $tokens['id_token'] ?? '');
$payloadB64 = $parts[1] ?? '';
$payload    = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true) ?? [];

$sub      = $payload['sub']                ?? '';
$username = $payload['preferred_username'] ?? ($payload['name'] ?? 'Unbekannt');
$email    = $payload['email']              ?? '';

// ── Benutzer in DB synchronisieren (Upsert via KeycloakSub) ───
$uid = 0;
try {
    $pdo = getDB();
    $pdo->prepare("
        INSERT INTO Benutzer (B_Name, B_Email, B_KeycloakSub)
        VALUES (:name, :email, :sub)
        ON DUPLICATE KEY UPDATE B_Name = VALUES(B_Name), B_Email = VALUES(B_Email)
    ")->execute(['name' => $username, 'email' => $email, 'sub' => $sub]);

    $uid = (int) $pdo
        ->prepare("SELECT BID FROM Benutzer WHERE B_KeycloakSub = ?")
        ->execute([$sub]) && false ?: $pdo
        ->query("SELECT BID FROM Benutzer WHERE B_KeycloakSub = " . $pdo->quote($sub))
        ->fetchColumn();
} catch (Throwable) {
    // Nicht-fatal: App läuft auch ohne DB-Sync
}

loginUser($uid, $username);
header('Location: /pages/dashboard.php');
exit;
