<?php

const REMEMBER_ME_COOKIE = 'gp_remember';
const REMEMBER_ME_LIFETIME = 60 * 60 * 24 * 30;

function rememberMeCookiePath()
{
    $path = parse_url(URLROOT, PHP_URL_PATH);

    return $path ?: '/';
}

function rememberMeCookieOptions($expires)
{
    return [
        'expires' => $expires,
        'path' => rememberMeCookiePath(),
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function forgetRememberMeCookie()
{
    setcookie(REMEMBER_ME_COOKIE, '', rememberMeCookieOptions(time() - 3600));
    unset($_COOKIE[REMEMBER_ME_COOKIE]);
}

function issueRememberMeCookie($userModel, $userId)
{
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    if (!$userModel->setRememberToken($userId, $tokenHash)) {
        return false;
    }

    $cookieValue = (int)$userId . ':' . $token;
    setcookie(REMEMBER_ME_COOKIE, $cookieValue, rememberMeCookieOptions(time() + REMEMBER_ME_LIFETIME));
    $_COOKIE[REMEMBER_ME_COOKIE] = $cookieValue;

    return true;
}

function restoreRememberedUserSession()
{
    if (!empty($_SESSION['session_uid']) || empty($_COOKIE[REMEMBER_ME_COOKIE])) {
        return;
    }

    $cookieParts = explode(':', $_COOKIE[REMEMBER_ME_COOKIE], 2);
    $userId = $cookieParts[0] ?? '';
    $token = $cookieParts[1] ?? '';

    if (!ctype_digit((string)$userId) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
        forgetRememberMeCookie();
        return;
    }

    require_once APPROOT . '/models/User.php';

    try {
        $userModel = new User();
        $user = $userModel->getRememberedUser((int)$userId, hash('sha256', $token));

        if (!$user) {
            forgetRememberMeCookie();
            return;
        }

        session_regenerate_id(true);
        $_SESSION['session_uid'] = $user['user_id'];
        $_SESSION['session_email'] = $user['email'];

        issueRememberMeCookie($userModel, $user['user_id']);
    } catch (Throwable $e) {
        // A missing database should not break public pages.
    }
}
