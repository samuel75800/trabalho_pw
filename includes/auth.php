<?php
/* ============================================================
   puppy.co — Auth Helper
   includes/auth.php

   Responsabilidades:
     - Iniciar sessão de forma segura
     - Proteger páginas restritas (redirect para login)
     - Login com verificação bcrypt
     - Logout com destruição completa da sessão
     - Regeneração de session ID contra session fixation
   ============================================================ */

// ── Configurações de sessão (antes de session_start) ────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');   // JS não acessa o cookie
    ini_set('session.use_strict_mode', '1');   // rejeita IDs não iniciados pelo servidor
    ini_set('session.cookie_samesite', 'Strict');

    // Em produção com HTTPS, ativar:
    // ini_set('session.cookie_secure', '1');

    session_start();
}

// ── Constantes ───────────────────────────────────────────────
define('SESSION_KEY',      'puppyco_user');
define('SESSION_LAST_ACT', 'puppyco_last_activity');
define('SESSION_TIMEOUT',  60 * 60);           // 1 hora de inatividade
define('BASE_PATH', rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'));
define('LOGIN_PAGE',     BASE_PATH . '/pages/login.php');
define('DASHBOARD_PAGE', BASE_PATH . '/pages/dashboard.php');

// ── Timeout por inatividade ──────────────────────────────────
if (isset($_SESSION[SESSION_LAST_ACT])) {
    if (time() - $_SESSION[SESSION_LAST_ACT] > SESSION_TIMEOUT) {
        auth_logout();                          // destrói sessão expirada
        _auth_redirect(LOGIN_PAGE . '?reason=timeout');
    }
}
if (isset($_SESSION[SESSION_KEY])) {
    $_SESSION[SESSION_LAST_ACT] = time();      // renova o timer a cada request
}

// ════════════════════════════════════════════════════════════
//  FUNÇÕES PÚBLICAS
// ════════════════════════════════════════════════════════════

/**
 * Protege uma página: redireciona para login se não estiver autenticado.
 * Uso: auth_require() no topo de qualquer página restrita.
 */
function auth_require(): void
{
    if (!auth_check()) {
        _auth_redirect(LOGIN_PAGE . '?reason=unauthorized');
    }
}

/**
 * Redireciona para o dashboard se já estiver autenticado.
 * Uso: na página de login, para evitar que usuário logado volte a ela.
 */
function auth_guest_only(): void
{
    if (auth_check()) {
        _auth_redirect(DASHBOARD_PAGE);
    }
}

/**
 * Verifica se há uma sessão autenticada ativa.
 */
function auth_check(): bool
{
    return isset($_SESSION[SESSION_KEY]) && !empty($_SESSION[SESSION_KEY]['id']);
}

/**
 * Retorna os dados do usuário logado ou null.
 *
 * @return array{id: int, username: string}|null
 */
function auth_user(): ?array
{
    return $_SESSION[SESSION_KEY] ?? null;
}

/**
 * Tenta fazer login com usuário e senha.
 * Retorna true em sucesso, string de erro em falha.
 *
 * @param  PDO    $pdo
 * @param  string $username
 * @param  string $password  senha em texto puro
 * @return true|string
 */
function auth_login(PDO $pdo, string $username, string $password)
{
    // Sanitização básica
    $username = trim($username);

    if ($username === '' || $password === '') {
        return 'Preencha usuário e senha.';
    }

    // Busca o usuário no banco
    $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verifica hash bcrypt
    if (!$user || !password_verify($password, $user['password'])) {
        // Mensagem genérica — não revelamos qual campo errou
        return 'Usuário ou senha incorretos.';
    }

    // Regenera o ID da sessão contra session fixation
    session_regenerate_id(true);

    // Armazena dados mínimos na sessão (nunca a senha)
    $_SESSION[SESSION_KEY] = [
        'id'       => (int) $user['id'],
        'username' => $user['username'],
    ];
    $_SESSION[SESSION_LAST_ACT] = time();

    // Atualiza o hash se o custo do bcrypt mudou (password rehash)
    if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
        $newHash = password_hash($password, PASSWORD_BCRYPT);
        $upd = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $upd->execute([$newHash, $user['id']]);
    }

    return true;
}

/**
 * Encerra a sessão completamente e limpa o cookie.
 */
function auth_logout(): void
{
    $_SESSION = [];

    // Remove o cookie de sessão do navegador
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

// ════════════════════════════════════════════════════════════
//  HELPER INTERNO
// ════════════════════════════════════════════════════════════

/**
 * Redireciona e encerra a execução.
 * Centralizado para facilitar testes e evitar headers duplicados.
 */
function _auth_redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}