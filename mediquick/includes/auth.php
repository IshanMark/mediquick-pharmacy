<?php
/**
 * Session, login state and role guards.
 * Must be included before any HTML output (it may send a redirect header).
 */
require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function has_role(string ...$roles): bool
{
    $u = current_user();
    return $u !== null && in_array($u['role'], $roles, true);
}

function login_user(array $user): void
{
    // Regenerate the session id on login so a pre-login session can't be reused (session fixation).
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => (int) $user['id'],
        'role'  => $user['role'],
        'name'  => $user['full_name'],
        'email' => $user['email'],
    ];
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}

function require_login(): void
{
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = 'Please log in to continue.';
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? BASE_URL . '/';
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function require_role(string ...$roles): void
{
    require_login();
    if (!has_role(...$roles)) {
        http_response_code(403);
        require APP_ROOT . '/403.php';
        exit;
    }
}

/** Sends a one-time flash message to the next page, then clears it. */
function flash(string $key, ?string $value = null)
{
    if ($value !== null) {
        $_SESSION['flash_' . $key] = $value;
        return null;
    }
    $msg = $_SESSION['flash_' . $key] ?? null;
    unset($_SESSION['flash_' . $key]);
    return $msg;
}
