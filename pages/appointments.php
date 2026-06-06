<?php
/* ============================================================
   puppy.co — Appointments CRUD
   pages/appointments.php
   ============================================================ */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

auth_require();

$error   = '';
$success = '';

// ── CREATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $pet_id  = (int)($_POST['pet_id']           ?? 0);
    $date    = trim($_POST['appointment_date']   ?? '');
    $time    = trim($_POST['appointment_time']   ?? '');
    $service = trim($_POST['service']            ?? '');
    $status  = trim($_POST['status']             ?? 'scheduled');
    $notes   = trim($_POST['notes']              ?? '');

    if (!$pet_id || !$date || !$time || !$service) {
        $error = 'Pet, data, hora e serviço são obrigatórios.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO appointments (pet_id, appointment_date, appointment_time, service, status, notes)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$pet_id, $date, $time, $service, $status, $notes ?: null]);
        $success = 'Agendamento criado com sucesso!';
    }
}

// ── UPDATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update') {
    $id      = (int)($_POST['id']               ?? 0);
    $pet_id  = (int)($_POST['pet_id']           ?? 0);
    $date    = trim($_POST['appointment_date']   ?? '');
    $time    = trim($_POST['appointment_time']   ?? '');
    $service = trim($_POST['service']            ?? '');
    $status  = trim($_POST['status']             ?? 'scheduled');
    $notes   = trim($_POST['notes']              ?? '');

    if (!$id || !$pet_id || !$date || !$time || !$service) {
        $error = 'Pet, data, hora e serviço são obrigatórios.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE appointments
             SET pet_id=?, appointment_date=?, appointment_time=?, service=?, status=?, notes=?
             WHERE id=?'
        );
        $stmt->execute([$pet_id, $date, $time, $service, $status, $notes ?: null, $id]);
        $success = 'Agendamento atualizado com sucesso!';
    }
}

// ── DELETE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare('DELETE FROM appointments WHERE id = ?')->execute([$id]);
        $success = 'Agendamento removido com sucesso!';
    }
}

// ── READ (com filtro de status) ──────────────────────────────
$filter_status = $_GET['status'] ?? 'all';
$search        = trim($_GET['q'] ?? '');

$where  = [];
$params = [];

if ($filter_status !== 'all') {
    $where[]  = 'a.status = ?';
    $params[] = $filter_status;
}

if ($search) {
    $where[]  = '(p.name LIKE ? OR a.service LIKE ? OR o.name LIKE ?)';
    $like     = "%$search%";
    $params   = array_merge($params, [$like, $like, $like]);
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare(
    "SELECT a.*, p.name AS pet_name, p.species, o.name AS owner_name
     FROM appointments a
     JOIN pets   p ON a.pet_id    = p.id
     JOIN owners o ON p.owner_id  = o.id
     $where_sql
     ORDER BY a.appointment_date DESC, a.appointment_time DESC"
);
$stmt->execute($params);
$appointments = $stmt->fetchAll();

// Pets para os selects
$pets = $pdo->query(
    "SELECT p.id, p.name, p.species, o.name AS owner_name
     FROM pets p JOIN owners o ON p.owner_id = o.id
     ORDER BY p.name"
)->fetchAll();

$services = ['Banho', 'Banho & Tosa', 'Tosa', 'Consulta Veterinária', 'Vacinação', 'Outros'];

$status_map = [
    'scheduled'  => ['label' => 'Agendado',   'badge' => 'badge-blue'],
    'completed'  => ['label' => 'Concluído',  'badge' => 'badge-green'],
    'cancelled'  => ['label' => 'Cancelado',  'badge' => 'badge-rose'],
];

$species_emoji = [
    'Dog'   => '🐶', 'Cat' => '🐱',
    'Bird'  => '🐦', 'Fish' => '🐠', 'Other' => '🐾',
];

$page_title = 'Agendamentos';
$page_icon  = '📅';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
  .toolbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
  }

  .search-wrap {
    position: relative;
    flex: 1;
    max-width: 320px;
  }

  .search-wrap svg {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
  }

  .search-wrap .input { padding-left: 40px; }

  .filter-tabs {
    display: flex;
    gap: 4px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: var(--r-md);
    padding: 3px;
  }

  .filter-tab {
    padding: 6px 14px;
    border-radius: calc(var(--r-md) - 2px);
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--text-muted);
    text-decoration: none;
    transition: all var(--t-fast);
    white-space: nowrap;
  }

  .filter-tab:hover { color: var(--accent); }

  .filter-tab.active {
    background: var(--bg-card);
    color: var(--accent);
    font-weight: 600;
    box-shadow: var(--shadow-sm);
  }

  .ml-auto { margin-left: auto; }

  .pet-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
  }

  .actions-cell {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  .form-grid .full { grid-column: 1 / -1; }

  @media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
    .form-grid .full { grid-column: 1; }
    .filter-tabs { width: 100%; justify-content: center; }
  }
</style>

<?php if ($success): ?>
  <script>document.addEventListener('DOMContentLoaded',()=> showToast('<?= addslashes($success) ?>', 'success'));</script>
<?php endif; ?>
<?php if ($error): ?>
  <script>document.addEventListener('DOMContentLoaded',()=> showToast('<?= addslashes($error) ?>', 'error'));</script>
<?php endif; ?>

<!-- ── Toolbar ─────────────────────────────────────────────── -->
<div class="toolbar">

  <!-- Busca -->
  <form method="GET" action="" class="search-wrap">
    <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" name="q" class="input" placeholder="Buscar..."
           value="<?= htmlspecialchars($search) ?>">
  </form>

  <!-- Filtro de status -->
  <div class="filter-tabs">
    <?php
    $tabs = ['all' => 'Todos', 'scheduled' => 'Agendados', 'completed' => 'Concluídos', 'cancelled' => 'Cancelados'];
    foreach ($tabs as $val => $label):
      $q_str = $search ? '&q=' . urlencode($search) : '';
    ?>
      <a href="?status=<?= $val ?><?= $q_str ?>"
         class="filter-tab <?= $filter_status === $val ? 'active' : '' ?>">
        <?= $label ?>
      </a>
    <?php endforeach; ?>
  </div>

  <button class="btn btn-primary ml-auto" onclick="openModal('modal-create')">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Novo agendamento
  </button>
</div>

<!-- ── Tabela ──────────────────────────────────────────────── -->
<div class="table-wrap">
  <table id="appt-table">
    <thead>
      <tr>
        <th>Pet</th>
        <th>Tutor</th>
        <th>Data</th>
        <th>Hora</th>
        <th>Serviço</th>
        <th>Status</th>
        <th>Observações</th>
        <th style="text-align:right">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($appointments)): ?>
        <tr>
          <td colspan="8" style="text-align:center; padding:48px; color:var(--text-muted);">
            Nenhum agendamento encontrado.
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($appointments as $a):
          $emoji      = $species_emoji[$a['species']] ?? '🐾';
          $status_cfg = $status_map[$a['status']] ?? ['label' => $a['status'], 'badge' => 'badge-muted'];
          $date_fmt   = (new DateTime($a['appointment_date']))->format('d/m/Y');
          $time_fmt   = substr($a['appointment_time'], 0, 5);
        ?>
        <tr>
          <td>
            <div class="pet-cell">
              <span><?= $emoji ?></span>
              <?= htmlspecialchars($a['pet_name']) ?>
            </div>
          </td>
          <td><?= htmlspecialchars($a['owner_name']) ?></td>
          <td><?= $date_fmt ?></td>
          <td><?= $time_fmt ?></td>
          <td><?= htmlspecialchars($a['service']) ?></td>
          <td>
            <span class="badge <?= $status_cfg['badge'] ?>">
              <?= $status_cfg['label'] ?>
            </span>
          </td>
          <td style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <?= htmlspecialchars($a['notes'] ?? '—') ?>
          </td>
          <td>
            <div class="actions-cell">
              <button class="btn btn-ghost btn-icon btn-sm"
                title="Editar"
                onclick="openEditModal(<?= htmlspecialchars(json_encode($a)) ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
              <button class="btn btn-danger btn-icon btn-sm"
                title="Remover"
                onclick="confirmDelete(<?= $a['id'] ?>, '<?= addslashes($a['pet_name']) ?>')">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                  <path d="M10 11v6"/><path d="M14 11v6"/>
                  <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL — CRIAR
══════════════════════════════════════════════════════════ -->
<div id="modal-create" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3>Novo agendamento</h3>
      <button class="modal-close" onclick="closeModal('modal-create')" aria-label="Fechar">✕</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="_action" value="create">
      <div class="form-grid">
        <div class="form-group full">
          <label for="c-pet">Pet *</label>
          <select id="c-pet" name="pet_id" class="input" required>
            <option value="">Selecione o pet</option>
            <?php foreach ($pets as $p): ?>
              <option value="<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?> — <?= htmlspecialchars($p['owner_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="c-date">Data *</label>
          <input type="date" id="c-date" name="appointment_date" class="input" required>
        </div>
        <div class="form-group">
          <label for="c-time">Hora *</label>
          <input type="time" id="c-time" name="appointment_time" class="input" required>
        </div>
        <div class="form-group">
          <label for="c-service">Serviço *</label>
          <select id="c-service" name="service" class="input" required>
            <option value="">Selecione</option>
            <?php foreach ($services as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="c-status">Status</label>
          <select id="c-status" name="status" class="input">
            <option value="scheduled">Agendado</option>
            <option value="completed">Concluído</option>
            <option value="cancelled">Cancelado</option>
          </select>
        </div>
        <div class="form-group full">
          <label for="c-notes">Observações</label>
          <textarea id="c-notes" name="notes" class="input" placeholder="Vacinas, instruções especiais..."></textarea>
        </div>
      </div>
      <div class="flex gap-sm" style="justify-content:flex-end; margin-top:24px;">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-create')">Cancelar</button>
        <button type="submit" class="btn btn-primary">Criar agendamento</button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     MODAL — EDITAR
══════════════════════════════════════════════════════════ -->
<div id="modal-edit" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3>Editar agendamento</h3>
      <button class="modal-close" onclick="closeModal('modal-edit')" aria-label="Fechar">✕</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="_action" value="update">
      <input type="hidden" name="id" id="e-id">
      <div class="form-grid">
        <div class="form-group full">
          <label for="e-pet">Pet *</label>
          <select id="e-pet" name="pet_id" class="input" required>
            <?php foreach ($pets as $p): ?>
              <option value="<?= $p['id'] ?>">
                <?= htmlspecialchars($p['name']) ?> — <?= htmlspecialchars($p['owner_name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="e-date">Data *</label>
          <input type="date" id="e-date" name="appointment_date" class="input" required>
        </div>
        <div class="form-group">
          <label for="e-time">Hora *</label>
          <input type="time" id="e-time" name="appointment_time" class="input" required>
        </div>
        <div class="form-group">
          <label for="e-service">Serviço *</label>
          <select id="e-service" name="service" class="input" required>
            <?php foreach ($services as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="e-status">Status</label>
          <select id="e-status" name="status" class="input">
            <option value="scheduled">Agendado</option>
            <option value="completed">Concluído</option>
            <option value="cancelled">Cancelado</option>
          </select>
        </div>
        <div class="form-group full">
          <label for="e-notes">Observações</label>
          <textarea id="e-notes" name="notes" class="input"></textarea>
        </div>
      </div>
      <div class="flex gap-sm" style="justify-content:flex-end; margin-top:24px;">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit')">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

<!-- ── Form oculto para DELETE ────────────────────────────── -->
<form id="form-delete" method="POST" action="" style="display:none">
  <input type="hidden" name="_action" value="delete">
  <input type="hidden" name="id" id="delete-id">
</form>

<script>
  function openEditModal(a) {
    document.getElementById('e-id').value    = a.id;
    document.getElementById('e-date').value  = a.appointment_date;
    document.getElementById('e-time').value  = a.appointment_time.substring(0, 5);
    document.getElementById('e-notes').value = a.notes ?? '';

    // Select pet
    const petSel = document.getElementById('e-pet');
    for (let opt of petSel.options) opt.selected = opt.value == a.pet_id;

    // Select serviço
    const svcSel = document.getElementById('e-service');
    for (let opt of svcSel.options) opt.selected = opt.value === a.service;

    // Select status
    const stsSel = document.getElementById('e-status');
    for (let opt of stsSel.options) opt.selected = opt.value === a.status;

    openModal('modal-edit');
  }

  function confirmDelete(id, name) {
    confirmAction(`Remover o agendamento de "${name}"?`, () => {
      document.getElementById('delete-id').value = id;
      document.getElementById('form-delete').submit();
    });
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>