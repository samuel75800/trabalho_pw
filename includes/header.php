<?php
/* ============================================================
   puppy.co — Header Component
   includes/header.php
   Usage: require_once __DIR__ . '/includes/header.php';

   Variables you can set before including:
   $page_title  — string shown in <title> and page heading  (default: 'puppy.co')
   $page_icon   — emoji/svg shown beside the h1             (default: '🐾')
   ============================================================ */

$page_title = $page_title ?? 'puppy.co';
$page_icon  = $page_icon  ?? '🐾';

// Active page detection
$current = basename($_SERVER['PHP_SELF']);

// Nav items: [label, href, icon SVG path]
$nav_items = [
  ['Dashboard',    'dashboard.php',    '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
  ['Owners',       'owners.php',       '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
  ['Pets',         'pets.php',         '<path d="M10 5.172C10 3.782 8.423 2.679 6.5 3c-2.823.47-4.113 6.006-4 7 .08.703 1.725 1.722 3.656 1 1.261-.472 1.96-1.45 2.344-2.5"/><path d="M14.267 5.172c0-1.39 1.577-2.493 3.5-2.172 2.823.47 4.113 6.006 4 7-.08.703-1.725 1.722-3.656 1-1.261-.472-1.96-1.45-2.344-2.5"/><path d="M8 14v.5"/><path d="M16 14v.5"/><path d="M11.25 16.25h1.5L12 17l-.75-.75z"/><path d="M4.42 11.247A13.152 13.152 0 0 0 4 14.556C4 18.728 7.582 21 12 21s8-2.272 8-6.444c0-1.061-.162-2.2-.493-3.309m-9.243-6.082A8.801 8.801 0 0 1 12 5c.78 0 1.5.108 2.161.306"/>'],
  ['Appointments', 'appointments.php', '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($page_title) ?> — puppy.co</title>

  <!-- Fonts preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <!-- Global styles -->
 <link rel="stylesheet" href="/puppy.co/assets/css/style.css">

  <!-- Inline: apply saved theme before paint (no flash) -->
  <script>
    (function () {
      var t = localStorage.getItem('puppyco_theme') ||
              (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-theme', t);
    })();
  </script>
</head>
<body>

<!-- ── Custom Cursor ──────────────────────────────────────── -->
<div id="cursor-dot"></div>
<div id="cursor-ring"></div>

<!-- ── Toast Container ────────────────────────────────────── -->
<div id="toast-container" aria-live="polite"></div>

<!-- ══════════════════════════════════════════════════════════
     NAVBAR
══════════════════════════════════════════════════════════ -->
<header class="navbar" role="banner">

  <!-- Brand -->
  <a href="dashboard.php" class="navbar-brand" aria-label="puppy.co — ir para dashboard">
    <span class="dot" aria-hidden="true"></span>
    puppy.co
  </a>

  <!-- Nav links -->
  <nav aria-label="Navegação principal">
    <ul class="navbar-nav">
      <?php foreach ($nav_items as [$label, $href, $icon_path]): ?>
        <?php $active = ($current === $href) ? ' active' : ''; ?>
        <li>
          <a href="<?= $href ?>" class="<?= $active ?>" <?= $active ? 'aria-current="page"' : '' ?>>
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 aria-hidden="true">
              <?= $icon_path ?>
            </svg>
            <span><?= $label ?></span>
          </a>
        </li>
      <?php endforeach; ?>

      <!-- Divider -->
      <li aria-hidden="true" style="width:1px; height:20px; background:var(--border); margin:0 6px;"></li>

      <!-- Theme toggle -->
      <li>
        <button
          class="theme-toggle"
          data-theme-toggle
          aria-label="Alternar modo claro/escuro"
          title="Alternar tema"
        ></button>
      </li>

      <!-- Logout -->
      <li>
        <a href="logout.php" title="Sair"
           style="color:var(--text-muted);"
           onclick="return confirm('Deseja encerrar a sessão?')">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
               viewBox="0 0 24 24" fill="none" stroke="currentColor"
               stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
               aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span>Sair</span>
        </a>
      </li>
    </ul>
  </nav>

</header>

<!-- ══════════════════════════════════════════════════════════
     PAGE HEADER  (title bar below navbar)
══════════════════════════════════════════════════════════ -->
<?php if ($current !== 'login.php'): ?>
<div class="page-header">
  <div class="container">
    <h1>
      <span class="accent-bar" aria-hidden="true"></span>
      <span aria-hidden="true"><?= $page_icon ?></span>
      <?= htmlspecialchars($page_title) ?>
    </h1>
  </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     MAIN CONTENT STARTS
══════════════════════════════════════════════════════════ -->
<main class="container" style="padding-top: 32px; padding-bottom: 60px;" role="main">