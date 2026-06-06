<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

auth_require();

$user = auth_user();

$total_owners       = $pdo->query('SELECT COUNT(*) FROM owners')->fetchColumn();
$total_pets         = $pdo->query('SELECT COUNT(*) FROM pets')->fetchColumn();
$total_appointments = $pdo->query('SELECT COUNT(*) FROM appointments')->fetchColumn();
$today_appointments = $pdo->query(
    "SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()"
)->fetchColumn();

$page_title = 'Central de gestão da puppy.co';
$page_icon  = '🏠';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 36px;
  }

  .stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 22px 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: var(--shadow-sm);
    transition: box-shadow var(--t-normal), border-color var(--t-normal),
                transform var(--t-normal), background-color var(--t-slow);
    cursor: default;
    position: relative;
    overflow: hidden;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--accent);
    opacity: 0;
    transition: opacity var(--t-normal);
  }

  .stat-card:hover {
    box-shadow: var(--shadow-md);
    border-color: var(--accent);
    transform: translateY(-2px);
  }

  .stat-card:hover::before { opacity: 1; }

  .stat-icon {
    width: 40px; height: 40px;
    border-radius: var(--r-md);
    background: var(--accent-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
  }

  .stat-label {
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--text-muted);
    letter-spacing: 0.04em;
    text-transform: uppercase;
  }

  .stat-value {
    font-family: 'Syne', sans-serif;
    font-size: 2rem;
    font-weight: 800;
    color: var(--text);
    line-height: 1;
  }

  .stat-value span {
    font-size: 0.8rem;
    font-weight: 400;
    color: var(--text-muted);
    font-family: 'Inter', sans-serif;
    margin-left: 4px;
  }

  .welcome-banner {
    background: var(--grad-hero);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    padding: 28px 32px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
  }

  .welcome-greeting {
    font-family: 'Syne', sans-serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--text);
  }

  .welcome-greeting em {
    font-style: normal;
    color: var(--accent);
  }

  .welcome-sub {
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-top: 4px;
  }

  .welcome-date {
    font-family: 'Syne', sans-serif;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-muted);
    background: var(--bg-input);
    padding: 8px 16px;
    border-radius: var(--r-md);
    border: 1px solid var(--border);
    white-space: nowrap;
  }

  @media (max-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
  }

  @media (max-width: 600px) {
    .stats-grid  { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .welcome-banner { padding: 20px; }
  }
</style>

<?php
$months_pt  = ['','Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
$days_pt    = ['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
$hoje       = new DateTime();
$dia_semana = $days_pt[(int)$hoje->format('w')];
?>

<div class="welcome-banner">
  <div>
    <div class="welcome-greeting">
      Olá, <em><?= htmlspecialchars($user['username']) ?></em> 👋
    </div>
    <p class="welcome-sub">Aqui está o resumo de hoje no puppy.co.</p>
  </div>
  <div class="welcome-date">
    <?= $dia_semana ?>, <?= $hoje->format('d') ?> de <?= $months_pt[(int)$hoje->format('n')] ?> de <?= $hoje->format('Y') ?>
  </div>
</div>

<div class="stats-grid">

  <div class="stat-card">
    <div class="stat-icon">👤</div>
    <div class="stat-label">Tutores</div>
    <div class="stat-value"><?= $total_owners ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-icon">🐾</div>
    <div class="stat-label">Pets</div>
    <div class="stat-value"><?= $total_pets ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-icon">📅</div>
    <div class="stat-label">Agendamentos</div>
    <div class="stat-value"><?= $total_appointments ?></div>
  </div>

  <div class="stat-card">
    <div class="stat-icon">✅</div>
    <div class="stat-label">Hoje</div>
    <div class="stat-value">
      <?= $today_appointments ?>
      <span>agendamento<?= $today_appointments !== '1' ? 's' : '' ?></span>
    </div>
  </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>