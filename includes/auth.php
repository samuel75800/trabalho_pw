<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

define('SESSION_KEY',      'puppyco_user');
define('SESSION_LAST_ACT', 'puppyco_last_activity');
define('SESSION_TIMEOUT',  60 * 60);
define('LOGIN_PAGE',      '/pages/login.php');
define('DASHBOARD_PAGE', '/pages/dashboard.php');

if (isset($_SESSION[SESSION_LAST_ACT])) {
    if (time() - $_SESSION[SESSION_LAST_ACT] > SESSION_TIMEOUT) {
        auth_logout();
        _auth_redirect(LOGIN_PAGE . '?reason=timeout');
    }
}
if (isset($_SESSION[SESSION_KEY])) {
    $_SESSION[SESSION_LAST_ACT] = time();
}

function auth_require(): void
{
    if (!auth_check()) {
        _auth_redirect(LOGIN_PAGE . '?reason=unauthorized');
    }
}

function auth_guest_only(): void
{
    if (auth_check()) {
        _auth_redirect(DASHBOARD_PAGE);
    }
}

function auth_check(): bool
{
    return isset($_SESSION[SESSION_KEY]) && !empty($_SESSION[SESSION_KEY]['id']);
}

function auth_user(): ?array
{
    return $_SESSION[SESSION_KEY] ?? null;
}

function auth_login(PDO $pdo, string $username, string $password)
{
    $username = trim($username);

    if ($username === '' || $password === '') {
        return 'Preencha usuário e senha.';
    }

    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return 'Usuário ou senha incorretos.';
    }

    session_regenerate_id(true);

    $_SESSION[SESSION_KEY] = [
        'id'       => (int) $user['id'],
        'username' => $user['username'],
    ];
    $_SESSION[SESSION_LAST_ACT] = time();

    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $upd = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $upd->execute([$newHash, $user['id']]);
    }

    return true;
}

function auth_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
}

function _auth_redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}