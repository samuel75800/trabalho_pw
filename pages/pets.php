<?php
/* ============================================================
   puppy.co — Pets CRUD
   pages/pets.php
   ============================================================ */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

auth_require();

$error   = '';
$success = '';

// ── CREATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $owner_id  = (int)($_POST['owner_id']  ?? 0);
    $name      = trim($_POST['name']       ?? '');
    $species   = trim($_POST['species']    ?? '');
    $breed     = trim($_POST['breed']      ?? '');
    $birthdate = trim($_POST['birthdate']  ?? '');
    $notes     = trim($_POST['notes']      ?? '');

    if (!$owner_id || !$name || !$species) {
        $error = 'Tutor, nome e espécie são obrigatórios.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO pets (owner_id, name, species, breed, birthdate, notes)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $owner_id, $name, $species,
            $breed     ?: null,
            $birthdate ?: null,
            $notes     ?: null,
        ]);
        $success = 'Pet cadastrado com sucesso!';
    }
}

// ── UPDATE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update') {
    $id        = (int)($_POST['id']        ?? 0);
    $owner_id  = (int)($_POST['owner_id']  ?? 0);
    $name      = trim($_POST['name']       ?? '');
    $species   = trim($_POST['species']    ?? '');
    $breed     = trim($_POST['breed']      ?? '');
    $birthdate = trim($_POST['birthdate']  ?? '');
    $notes     = trim($_POST['notes']      ?? '');

    if (!$id || !$owner_id || !$name || !$species) {
        $error = 'Tutor, nome e espécie são obrigatórios.';
    } else {
        $stmt = $pdo->prepare(
            'UPDATE pets SET owner_id=?, name=?, species=?, breed=?, birthdate=?, notes=?
             WHERE id=?'
        );
        $stmt->execute([
            $owner_id, $name, $species,
            $breed     ?: null,
            $birthdate ?: null,
            $notes     ?: null,
            $id,
        ]);
        $success = 'Pet atualizado com sucesso!';
    }
}

// ── DELETE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare('DELETE FROM pets WHERE id = ?')->execute([$id]);
        $success = 'Pet removido com sucesso!';
    }
}

// ── READ (com busca) ─────────────────────────────────────────
$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $pdo->prepare(
        "SELECT p.*, o.name AS owner_name
         FROM pets p
         JOIN owners o ON p.owner_id = o.id
         WHERE p.name LIKE ? OR p.species LIKE ? OR p.breed LIKE ? OR o.name LIKE ?
         ORDER BY p.name"
    );
    $like = "%$search%";
    $stmt->execute([$like, $like, $like, $like]);
} else {
    $stmt = $pdo->query(
        "SELECT p.*, o.name AS owner_name
         FROM pets p
         JOIN owners o ON p.owner_id = o.id
         ORDER BY p.name"
    );
}
$pets = $stmt->fetchAll();

// Lista de tutores para os selects dos modais
$owners = $pdo->query('SELECT id, name FROM owners ORDER BY name')->fetchAll();

$species_emoji = [
    'Dog'   => '🐶',
    'Cat'   => '🐱',
    'Bird'  => '🐦',
    'Fish'  => '🐠',
    'Other' => '🐾',
];

$species_options = ['Dog', 'Cat', 'Bird', 'Fish', 'Other'];

$page_title = 'Pets';
$page_icon  = '🐾';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
  .toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
  }

  .search-wrap {
    position: relative;
    flex: 1;
    max-width: 360px;
  }

  .search-wrap svg {
    position: absolute;
    left: 13px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
  }

  .search-wrap .input { padding-left: 40px; }

  .pet-name-cell {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
  }

  .pet-emoji-badge {
    font-size: 1.3rem;
    line-height: 1;
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
  <form method="GET" action="" class="search-wrap">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" name="q" class="input" placeholder="Buscar pet..."
           value="<?= htmlspecialchars($search) ?>">
  </form>
  <button class="btn btn-primary" onclick="openModal('modal-create')">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Novo pet
  </button>
</div>

<!-- ── Tabela ──────────────────────────────────────────────── -->
<div class="table-wrap">
  <table id="pets-table">
    <thead>
      <tr>
        <th>Pet</th>
        <th>Espécie</th>
        <th>Raça</th>
        <th>Nascimento</th>
        <th>Tutor</th>
        <th>Observações</th>
        <th style="text-align:right">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($pets)): ?>
        <tr>
          <td colspan="7" style="text-align:center; padding:48px; color:var(--text-muted);">
            <?= $search ? 'Nenhum pet encontrado para "' . htmlspecialchars($search) . '".' : 'Nenhum pet cadastrado ainda.' ?>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($pets as $p):
          $emoji = $species_emoji[$p['species']] ?? '🐾';
        ?>
        <tr>
          <td>
            <div class="pet-name-cell">
              <span class="pet-emoji-badge"><?= $emoji ?></span>
              <?= htmlspecialchars($p['name']) ?>
            </div>
          </td>
          <td><?= htmlspecialchars($p['species']) ?></td>
          <td><?= htmlspecialchars($p['breed'] ?? '—') ?></td>
          <td>
            <?= $p['birthdate']
              ? (new DateTime($p['birthdate']))->format('d/m/Y')
              : '—' ?>
          </td>
          <td><?= htmlspecialchars($p['owner_name']) ?></td>
          <td style="max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            <?= htmlspecialchars($p['notes'] ?? '—') ?>
          </td>
          <td>
            <div class="actions-cell">
              <button class="btn btn-ghost btn-icon btn-sm"
                title="Editar"
                onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
              <button class="btn btn-danger btn-icon btn-sm"
                title="Remover"
                onclick="confirmDelete(<?= $p['id'] ?>, '<?= addslashes($p['name']) ?>')">
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
      <h3>Novo pet</h3>
      <button class="modal-close" onclick="closeModal('modal-create')" aria-label="Fechar">✕</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="_action" value="create">
      <div class="form-grid">
        <div class="form-group full">
          <label for="c-owner">Tutor *</label>
          <select id="c-owner" name="owner_id" class="input" required>
            <option value="">Selecione o tutor</option>
            <?php foreach ($owners as $o): ?>
              <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="c-name">Nome *</label>
          <input type="text" id="c-name" name="name" class="input" placeholder="Nome do pet" required>
        </div>
        <div class="form-group">
          <label for="c-species">Espécie *</label>
          <select id="c-species" name="species" class="input" required>
            <option value="">Selecione</option>
            <?php foreach ($species_options as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="c-breed">Raça</label>
          <input type="text" id="c-breed" name="breed" class="input" placeholder="Ex: Poodle">
        </div>
        <div class="form-group">
          <label for="c-birthdate">Data de nascimento</label>
          <input type="date" id="c-birthdate" name="birthdate" class="input">
        </div>
        <div class="form-group full">
          <label for="c-notes">Observações</label>
          <textarea id="c-notes" name="notes" class="input" placeholder="Alergias, cuidados especiais..."></textarea>
        </div>
      </div>
      <div class="flex gap-sm" style="justify-content:flex-end; margin-top:24px;">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-create')">Cancelar</button>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
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
      <h3>Editar pet</h3>
      <button class="modal-close" onclick="closeModal('modal-edit')" aria-label="Fechar">✕</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="_action" value="update">
      <input type="hidden" name="id" id="e-id">
      <div class="form-grid">
        <div class="form-group full">
          <label for="e-owner">Tutor *</label>
          <select id="e-owner" name="owner_id" class="input" required>
            <option value="">Selecione o tutor</option>
            <?php foreach ($owners as $o): ?>
              <option value="<?= $o['id'] ?>"><?= htmlspecialchars($o['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="e-name">Nome *</label>
          <input type="text" id="e-name" name="name" class="input" required>
        </div>
        <div class="form-group">
          <label for="e-species">Espécie *</label>
          <select id="e-species" name="species" class="input" required>
            <?php foreach ($species_options as $s): ?>
              <option value="<?= $s ?>"><?= $s ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="e-breed">Raça</label>
          <input type="text" id="e-breed" name="breed" class="input">
        </div>
        <div class="form-group">
          <label for="e-birthdate">Data de nascimento</label>
          <input type="date" id="e-birthdate" name="birthdate" class="input">
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
  function openEditModal(pet) {
    document.getElementById('e-id').value        = pet.id;
    document.getElementById('e-name').value      = pet.name;
    document.getElementById('e-breed').value     = pet.breed     ?? '';
    document.getElementById('e-birthdate').value = pet.birthdate ?? '';
    document.getElementById('e-notes').value     = pet.notes     ?? '';

    // Select tutor
    const ownerSel = document.getElementById('e-owner');
    for (let opt of ownerSel.options) {
      opt.selected = opt.value == pet.owner_id;
    }

    // Select espécie
    const speciesSel = document.getElementById('e-species');
    for (let opt of speciesSel.options) {
      opt.selected = opt.value === pet.species;
    }

    openModal('modal-edit');
  }

  function confirmDelete(id, name) {
    confirmAction(`Remover o pet "${name}"? Os agendamentos vinculados também serão removidos.`, () => {
      document.getElementById('delete-id').value = id;
      document.getElementById('form-delete').submit();
    });
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>