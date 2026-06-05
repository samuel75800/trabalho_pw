<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

auth_guest_only();

$error   = '';
$timeout = isset($_GET['reason']) && $_GET['reason'] === 'timeout';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $result = auth_login($pdo, $username, $password);

    if ($result === true) {
        _auth_redirect(DASHBOARD_PAGE);
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entrar — puppy.co</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="/assets/css/style.css">

  <script>
    (function () {
      var t = localStorage.getItem('puppyco_theme') ||
              (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>

  <style>
    body {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      overflow: hidden;
    }

    .login-panel {
      position: relative;
      background: var(--accent);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px;
      overflow: hidden;
      transition: background-color var(--t-slow);
    }

    .login-panel::before,
    .login-panel::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      opacity: 0.12;
      background: #fff;
    }
    .login-panel::before {
      width: 420px; height: 420px;
      top: -120px; left: -120px;
    }
    .login-panel::after {
      width: 280px; height: 280px;
      bottom: -80px; right: -80px;
    }

    .panel-inner {
      position: relative;
      z-index: 1;
      text-align: center;
      color: #fff;
    }

    .panel-paw {
      font-size: 4.5rem;
      line-height: 1;
      margin-bottom: 24px;
      display: block;
      animation: float 4s ease-in-out infinite;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50%       { transform: translateY(-10px); }
    }

    .panel-title {
      font-family: 'Syne', sans-serif;
      font-size: 2.4rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.02em;
      margin-bottom: 8px;
    }

    .panel-sub {
      font-size: 0.95rem;
      color: rgba(255,255,255,0.75);
      line-height: 1.6;
      max-width: 280px;
    }

    .panel-dots {
      display: flex;
      gap: 8px;
      margin-top: 48px;
      justify-content: center;
    }
    .panel-dots span {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: rgba(255,255,255,0.35);
    }
    .panel-dots span:first-child {
      background: rgba(255,255,255,0.9);
      box-shadow: 0 0 12px rgba(255,255,255,0.5);
    }

    .login-form-side {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 48px 56px;
      background: var(--bg);
      transition: background-color var(--t-slow);
      overflow-y: auto;
    }

    .login-box {
      width: 100%;
      max-width: 360px;
    }

    .login-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 36px;
    }
    .login-brand .dot {
      width: 10px; height: 10px;
      background: var(--accent);
      border-radius: 50%;
      box-shadow: 0 0 12px var(--accent-glow);
      animation: pulse-dot 2.5s ease-in-out infinite;
    }
    .login-brand-name {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.3rem;
      color: var(--text);
    }

    .login-heading {
      font-family: 'Syne', sans-serif;
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 6px;
    }

    .login-sub {
      font-size: 0.875rem;
      color: var(--text-muted);
      margin-bottom: 32px;
    }

    .login-fields {
      display: flex;
      flex-direction: column;
      gap: 18px;
      margin-bottom: 24px;
    }

    .login-error {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      background: var(--danger-soft);
      border: 1px solid var(--danger);
      border-radius: var(--r-md);
      font-size: 0.875rem;
      color: var(--danger);
      margin-bottom: 20px;
      animation: fadeSlideIn 0.3s ease both;
    }
    .login-error svg { flex-shrink: 0; }

    .login-timeout {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 16px;
      background: var(--accent-soft);
      border: 1px solid var(--accent);
      border-radius: var(--r-md);
      font-size: 0.875rem;
      color: var(--accent);
      margin-bottom: 20px;
    }

    .btn-login {
      width: 100%;
      padding: 13px;
      font-size: 0.95rem;
      font-weight: 600;
      letter-spacing: 0.01em;
      border-radius: var(--r-md);
      border: none;
      background: var(--accent);
      color: #fff;
      cursor: none;
      transition: all var(--t-normal);
      box-shadow: var(--shadow-accent);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      position: relative;
      overflow: hidden;
    }
    .btn-login:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 28px var(--accent-glow);
    }
    .btn-login:active { transform: scale(0.98); }

    .btn-login .spinner {
      display: none;
      width: 16px; height: 16px;
      border: 2px solid rgba(255,255,255,0.3);
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 0.6s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .btn-login.loading .btn-text { display: none; }
    .btn-login.loading .spinner  { display: block; }

    .theme-wrap {
      position: absolute;
      top: 20px; right: 24px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .theme-wrap span {
      font-size: 0.75rem;
      color: var(--text-muted);
    }

    .input-pass-wrap {
      position: relative;
    }
    .input-pass-wrap .input {
      padding-right: 44px;
    }
    .toggle-pass {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: none;
      color: var(--text-muted);
      padding: 4px;
      display: flex;
      align-items: center;
      transition: color var(--t-fast);
    }
    .toggle-pass:hover { color: var(--accent); }

    .login-footer-text {
      text-align: center;
      margin-top: 24px;
      font-size: 0.78rem;
      color: var(--text-muted);
    }

    @media (max-width: 768px) {
      body { grid-template-columns: 1fr; }
      .login-panel { display: none; }
      .login-form-side { padding: 32px 24px; }
    }
  </style>
</head>
<body>

<div id="cursor-dot"></div>
<div id="cursor-ring"></div>
<div id="toast-container" aria-live="polite"></div>

<div class="login-panel" aria-hidden="true">
  <div class="panel-inner">
    <span class="panel-paw">🐾</span>
    <h1 class="panel-title">puppy.co</h1>
    <p class="panel-sub">Gestão completa do seu petshop, num só lugar.</p>
    <div class="panel-dots">
      <span></span><span></span><span></span>
    </div>
  </div>
</div>

<div class="login-form-side">

  <div class="theme-wrap">
    <span id="theme-label">Claro</span>
    <button class="theme-toggle" data-theme-toggle
            aria-label="Alternar modo claro/escuro"></button>
  </div>

  <div class="login-box page-enter">

    <div class="login-brand">
      <span class="dot"></span>
      <span class="login-brand-name">puppy.co</span>
    </div>

    <h2 class="login-heading">Bem-vindo de volta</h2>
    <p class="login-sub">Entre com suas credenciais para acessar o sistema.</p>

    <?php if ($timeout): ?>
    <div class="login-timeout" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      Sua sessão expirou por inatividade. Entre novamente.
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="login-error" role="alert">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
           fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="10"/>
        <line x1="15" y1="9" x2="9" y2="15"/>
        <line x1="9" y1="9" x2="15" y2="15"/>
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" id="login-form" novalidate autocomplete="off">

      <div class="login-fields">

        <div class="form-group">
          <label for="username">Usuário</label>
          <div class="input-icon-wrap">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <input
              type="text"
              id="username"
              name="username"
              class="input"
              placeholder="seu usuário"
              autocomplete="off"
              required
              autofocus
            >
          </div>
        </div>

        <div class="form-group">
          <label for="password">Senha</label>
          <div class="input-pass-wrap input-icon-wrap">
            <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <input
              type="password"
              id="password"
              name="password"
              class="input"
              placeholder="••••••••"
              autocomplete="new-password"
              required
            >
            <button type="button" class="toggle-pass" id="toggle-pass"
                    aria-label="Mostrar senha">
              <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                   style="display:none">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

      </div>

      <button type="submit" class="btn-login" id="submit-btn">
        <span class="btn-text">Entrar</span>
        <div class="spinner" aria-hidden="true"></div>
      </button>

    </form>

    <p class="login-footer-text">
      Área restrita — acesso somente para funcionários.
    </p>

  </div>
</div>

<script src="/assets/js/main.js"></script>
<script>
  function updateThemeLabel() {
    const label = document.getElementById('theme-label');
    if (label) label.textContent = puppyTheme.get() === 'dark' ? 'Escuro' : 'Claro';
  }
  updateThemeLabel();
  document.querySelector('[data-theme-toggle]')
    ?.addEventListener('click', () => setTimeout(updateThemeLabel, 50));

  const toggleBtn = document.getElementById('toggle-pass');
  const passInput = document.getElementById('password');
  const eyeOpen   = document.getElementById('eye-open');
  const eyeClosed = document.getElementById('eye-closed');

  toggleBtn?.addEventListener('click', () => {
    const isHidden = passInput.type === 'password';
    passInput.type          = isHidden ? 'text'  : 'password';
    eyeOpen.style.display   = isHidden ? 'none'  : '';
    eyeClosed.style.display = isHidden ? ''      : 'none';
    toggleBtn.setAttribute('aria-label', isHidden ? 'Ocultar senha' : 'Mostrar senha');
  });

  document.getElementById('login-form')?.addEventListener('submit', function (e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    if (!username || !password) {
      e.preventDefault();
      if (!username) document.getElementById('username').style.borderColor = 'var(--danger)';
      if (!password) document.getElementById('password').style.borderColor = 'var(--danger)';
      return;
    }

    document.getElementById('submit-btn').classList.add('loading');
  });
</script>

</body>
</html>