<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user(): ?array
{
    return isset($_SESSION['user']) && is_array($_SESSION['user']) ? $_SESSION['user'] : null;
}

function role_home(string $role): string
{
    if ($role === 'guru') {
        return 'daftarguru.php';
    }
    if ($role === 'siswa') {
        return 'daftarsiswa.php';
    }
    return 'table.php';
}

function redirect_for_current_role(): void
{
    $user = current_user();
    header('Location: ' . role_home($user['role'] ?? ''));
    exit;
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        redirect_for_current_role();
    }
    return $user;
}

function require_data_page(string $pageRole): array
{
    $user = require_login();
    if ($user['role'] !== 'admin' && $user['role'] !== $pageRole) {
        redirect_for_current_role();
    }
    return $user;
}

function require_admin_or_role(string $role): array
{
    $user = require_login();
    if ($user['role'] !== 'admin' && $user['role'] !== $role) {
        redirect_for_current_role();
    }
    return $user;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function valid_csrf_token(): bool
{
    $submittedToken = $_POST['csrf_token'] ?? '';
    return is_string($submittedToken)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $submittedToken);
}
?>