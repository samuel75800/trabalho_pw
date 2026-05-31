<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

auth_require();

$user = auth_user();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM owners');
$stmt->execute();
$total_owners = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM pets');
$stmt->execute();
$total_pets = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments');
$stmt->execute();
$total_appointments = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM appointments WHERE DATE(appointment_date) = CURDATE()');
$stmt->execute();
$today_appointments = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT a.*, p.name AS pet_name, o.name AS owner_name 
    FROM appointments a
    JOIN pets p ON a.pet_id = p.id
    JOIN owners o ON p.owner_id = o.id
    WHERE a.status = 'scheduled'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 5
");
$stmt->execute();
$upcoming_appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT p.*, o.name AS owner_name 
    FROM pets p
    JOIN owners o ON p.owner_id = o.id
    ORDER BY p.id DESC
    LIMIT 4
");
$stmt->execute();
$recent_pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

  .dashboard-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 28px;
    margin-bottom: 36px;
  }

  .dashboard-section {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--r-xl);
    padding: 24px;
    box-shadow: var(--shadow-sm);
  }

  .section-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 36px;
  }

  .action-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: var(--text);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-sm);
    transition: transform var(--t-normal), border-color var(--t-normal), box-shadow var(--t-normal);
  }

  .action-card:hover {
    transform: translateY(-2px);
    border-color: var(--accent);
    box-shadow: var(--shadow-md);
  }

  .action-icon {
    font-size: 1.5rem;
  }

  .action-title {
    font-family: 'Syne', sans-serif;
    font-size: 0.9rem;
    font-weight: 700;
  }

  .dashboard-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
  }

  .dashboard-table th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    padding: 12px 8px;
    border-bottom: 1px solid var(--border);
  }

  .dashboard-table td {
    padding: 14px 8px;
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
    color: var(--text);
  }

  .dashboard-table tr:last-child td {
    border-bottom: none;
  }

  .empty-state {
    text-align: center;
    padding: 32px 16px;
    color: var(--text-muted);
    font-size: 0.875rem;
  }

  .pet-mini-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .pet-mini-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px;
    border-radius: var(--r-md);
    background: var(--bg-input);
    border: 1px solid var(--border);
  }

  .pet-mini-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--accent-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
  }

  .pet-mini-info {
    flex-grow: 1;
  }

  .pet-mini-name {
    font-family: 'Syne', sans-serif;
    font-size: 0.875rem;
    font-weight: 700;
    color: var(--text);
  }

  .pet-mini-owner {
    font-size: 0.75rem;
    color: var(--text-muted);
  }

  .badge-status {
    display: inline-block;
    padding: 4px 8px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: var(--r-sm);
    background: var(--accent-soft);
    color: var(--accent);
  }

  @media (max-width: 1024px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .dashboard-layout { grid-template-columns: 1fr; }
    .quick-actions-grid { grid-template-columns: 1fr; }
  }

  @media (max-width: 600px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
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
      Olá, <em><?= htmlspecialchars($user['username'] ?? '') ?></em> 👋
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
      <span>agendamento<?= $today_appointments !== 1 ? 's' : '' ?></span>
    </div>
  </div>
</div>

<h3 class="section-title">⚡ Acesso Rápido</h3>
<div class="quick-actions-grid">
  <a href="owners.php" class="action-card">
    <div class="action-icon">👤</div>
    <div class="action-title">Gerenciar Tutores</div>
  </a>
  <a href="pets.php" class="action-card">
    <div class="action-icon">🐾</div>
    <div class="action-title">Gerenciar Pets</div>
  </a>
  <a href="appointments.php" class="action-card">
    <div class="action-icon">📅</div>
    <div class="action-title">Agendamentos</div>
  </a>
</div>

<div class="dashboard-layout">
  <div class="dashboard-section">
    <h3 class="section-title">📅 Próximos 5 Agendamentos</h3>
    <?php if (empty($upcoming_appointments)): ?>
      <div class="empty-state">Nenhum agendamento ativo marcado para os próximos dias.</div>
    <?php else: ?>
      <table class="dashboard-table">
        <thead>
          <tr>
            <th>Pet</th>
            <th>Tutor</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($upcoming_appointments as $app): ?>
            <tr>
              <td><strong><?= htmlspecialchars($app['pet_name']) ?></strong></td>
              <td><?= htmlspecialchars($app['owner_name']) ?></td>
              <td><?= date('d/m/Y', strtotime($app['appointment_date'])) ?></td>
              <td><?= date('H:i', strtotime($app['appointment_time'])) ?></td>
              <td><span class="badge-status">Agendado</span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="dashboard-section">
    <h3 class="section-title">🐾 Pets Recentes</h3>
    <?php if (empty($recent_pets)): ?>
      <div class="empty-state">Nenhum pet cadastrado.</div>
    <?php else: ?>
      <div class="pet-mini-list">
        <?php foreach ($recent_pets as $pet): ?>
          <div class="pet-mini-item">
            <div class="pet-mini-avatar">🐾</div>
            <div class="pet-mini-info">
              <div class="pet-mini-name"><?= htmlspecialchars($pet['name']) ?></div>
              <div class="pet-mini-owner">Tutor: <?= htmlspecialchars($pet['owner_name']) ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>