<?php

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function request_csrf_token(): string
{
    return (string)($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
}

function valid_csrf_token(): bool
{
    $submitted = request_csrf_token();
    return $submitted !== '' && hash_equals(csrf_token(), $submitted);
}

function user_has_role(int $userId, string $role): bool
{
    if ($userId <= 0 || $role === '') {
        return false;
    }

    // Fast path: check cached session role first (avoids DB query on every request)
    if (!empty($_SESSION['session_role']) && (int)($_SESSION['session_uid'] ?? 0) === $userId) {
        if ($_SESSION['session_role'] === $role) {
            return true;
        }
        // Admin implicitly has all roles for access purposes
        if ($_SESSION['session_role'] === 'admin' && $role === 'admin') {
            return true;
        }
        // If session role doesn't match, fall through to DB check
        // (user might have multiple roles)
    }

    $db = new Database();
    $db->dbquery(
        'SELECT 1
           FROM user_roles ur
           INNER JOIN roles r ON r.id = ur.role_id
          WHERE ur.user_id = :uid AND r.name = :role
          LIMIT 1'
    );
    $db->dbbind(':uid', $userId, PDO::PARAM_INT);
    $db->dbbind(':role', $role);

    return (bool)$db->getsingledata();
}
