<?php
// ============================================================
// Auth-Helfer – Session & Login-Prüfung
// ============================================================

require_once __DIR__ . '/../config/app.php';

function startSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_name']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: /RST-Inventar/app/index.php');
        exit;
    }
}

function loginUser(int $id, string $name): void {
    startSession();
    session_regenerate_id(true);
    $_SESSION['user_id']   = $id;
    $_SESSION['user_name'] = $name;
    $_SESSION['logged_in_at'] = time();
}

function logoutUser(): void {
    startSession();
    session_unset();
    session_destroy();
}

function getCurrentUser(): array {
    startSession();
    return [
        'id'   => $_SESSION['user_id']   ?? 0,
        'name' => $_SESSION['user_name'] ?? '',
    ];
}
