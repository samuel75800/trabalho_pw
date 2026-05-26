<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

auth_require();

$error   = '';
$success = '';

/*  CREATE — Processamento do Cadastro */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'create') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$name || !$email || !$phone) {
        $error = 'Nome, e-mail e telefone são obrigatórios.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO owners (name, email, phone, address) VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$name, $email, $phone, $address ?: null]);
            $success = 'Tutor cadastrado com sucesso!';
        } catch (PDOException $e) {
            $error = str_contains($e->getMessage(), 'Duplicate')
                ? 'Este e-mail já está cadastrado.'
                : 'Erro ao cadastrar tutor.';
        }
    }
}

/* UPDATE — edição */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'update') {
    $id      = (int)($_POST['id']      ?? 0);
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim($_POST['phone']   ?? '');
    $address = trim($_POST['address'] ?? '');

    if (!$id || !$name || !$email || !$phone) {
        $error = 'Nome, e-mail e telefone são obrigatórios.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'UPDATE owners SET name=?, email=?, phone=?, address=? WHERE id=?'
            );
            $stmt->execute([$name, $email, $phone, $address ?: null, $id]);
            $success = 'Tutor atualizado com sucesso!';
        } catch (PDOException $e) {
            $error = str_contains($e->getMessage(), 'Duplicate')
                ? 'Este e-mail já está em uso.'
                : 'Erro ao atualizar tutor.';
        }
    }
}

/* DELETE — remoção */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_action'] ?? '') === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $pdo->prepare('DELETE FROM owners WHERE id = ?')->execute([$id]);
        $success = 'Tutor removido com sucesso!';
    }
}

/* READ — Consultar e listar dados */
$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $pdo->prepare(
        "SELECT o.*, COUNT(p.id) AS pet_count
         FROM owners o
         LEFT JOIN pets p ON p.owner_id = o.id
         WHERE o.name LIKE ? OR o.email LIKE ? OR o.phone LIKE ?
         GROUP BY o.id ORDER BY o.name"
    );
    $like = "%$search%";
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = $pdo->query(
        "SELECT o.*, COUNT(p.id) AS pet_count
         FROM owners o
         LEFT JOIN pets p ON p.owner_id = o.id
         GROUP BY o.id ORDER BY o.name"
    );
}
$owners = $stmt->fetchAll();

$page_title = 'Tutores';
$page_icon  = '👤';
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
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
  }

  .search-wrap .input {
    padding-left: 40px;
  }

  .avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: var(--accent-soft);
    color: var(--accent);
    font-family: 'Syne', sans-serif;
    font-weight: 700;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }

  .owner-name-cell {
    display: flex;
    align-items: center;
    gap: 10px;
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

<div class="toolbar">
  <form method="GET" action="" class="search-wrap">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" name="q" class="input" placeholder="Buscar tutor..."
           value="<?= htmlspecialchars($search) ?>">
  </form>
  <button class="btn btn-primary" onclick="openModal('modal-create')">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2">
      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
    </svg>
    Novo tutor
  </button>
</div>

<div class="table-wrap">
  <table id="owners-table">
    <thead>
      <tr>
        <th>Tutor</th>
        <th>E-mail</th>
        <th>Telefone</th>
        <th>Endereço</th>
        <th>Pets</th>
        <th style="text-align:right">Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($owners)): ?>
        <tr>
          <td colspan="6" style="text-align:center; padding:48px; color:var(--text-muted);">
            <?= $search ? 'Nenhum tutor encontrado para "'  . htmlspecialchars($search) . '".' : 'Nenhum tutor cadastrado ainda.' ?>
          </td>
        </tr>
      <?php else: ?>
        <?php foreach ($owners as $o): ?>
        <tr>
          <td>
            <div class="owner-name-cell">
              <div class="avatar"><?= mb_strtoupper(mb_substr($o['name'], 0, 1)) ?></div>
              <?= htmlspecialchars($o['name']) ?>
            </div>
          </td>
          <td><?= htmlspecialchars($o['email']) ?></td>
          <td><?= htmlspecialchars($o['phone']) ?></td>
          <td><?= htmlspecialchars($o['address'] ?? '—') ?></td>
          <td>
            <span class="badge badge-blue"><?= $o['pet_count'] ?> pet<?= $o['pet_count'] != 1 ? 's' : '' ?></span>
          </td>
          <td>
            <div class="actions-cell">
              
              <button class="btn btn-ghost btn-icon btn-sm"
                title="Editar"
                onclick="openEditModal(<?= htmlspecialchars(json_encode($o)) ?>)">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </button>
              
              <button class="btn btn-danger btn-icon btn-sm"
                title="Remover"
                onclick="confirmDelete(<?= $o['id'] ?>, '<?= addslashes($o['name']) ?>')">
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

<div id="modal-create" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3>Novo tutor</h3>
      <button class="modal-close" onclick="closeModal('modal-create')" aria-label="Fechar">✕</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="_action" value="create">
      <div class="form-grid">
        <div class="form-group full">
          <label for="c-name">Nome *</label>
          <input type="text" id="c-name" name="name" class="input" placeholder="Nome completo" required>
        </div>
        <div class="form-group">
          <label for="c-email">E-mail *</label>
          <input type="email" id="c-email" name="email" class="input" placeholder="email@exemplo.com" required>
        </div>
        <div class="form-group">
          <label for="c-phone">Telefone *</label>
          <input type="text" id="c-phone" name="phone" class="input" placeholder="(00) 00000-0000" required>
        </div>
        <div class="form-group full">
          <label for="c-address">Endereço</label>
          <input type="text" id="c-address" name="address" class="input" placeholder="Rua, número — Cidade">
        </div>
      </div>
      <div class="flex gap-sm" style="justify-content:flex-end; margin-top:24px;">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-create')">Cancelar</button>
        <button type="submit" class="btn btn-primary">Cadastrar</button>
      </div>
    </form>
  </div>
</div>

<div id="modal-edit" class="modal-overlay">
  <div class="modal">
    <div class="modal-header">
      <h3>Editar tutor</h3>
      <button class="modal-close" onclick="closeModal('modal-edit')" aria-label="Fechar">✕</button>
    </div>
    <form method="POST" action="">
      <input type="hidden" name="_action" value="update">
      <input type="hidden" name="id" id="e-id">
      <div class="form-grid">
        <div class="form-group full">
          <label for="e-name">Nome *</label>
          <input type="text" id="e-name" name="name" class="input" required>
        </div>
        <div class="form-group">
          <label for="e-email">E-mail *</label>
          <input type="email" id="e-email" name="email" class="input" required>
        </div>
        <div class="form-group">
          <label for="e-phone">Telefone *</label>
          <input type="text" id="e-phone" name="phone" class="input" required>
        </div>
        <div class="form-group full">
          <label for="e-address">Endereço</label>
          <input type="text" id="e-address" name="address" class="input">
        </div>
      </div>
      <div class="flex gap-sm" style="justify-content:flex-end; margin-top:24px;">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-edit')">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
    </form>
  </div>
</div>

<form id="form-delete" method="POST" action="" style="display:none">
  <input type="hidden" name="_action" value="delete">
  <input type="hidden" name="id" id="delete-id">
</form>

<script>
  function openEditModal(owner) {
    document.getElementById('e-id').value      = owner.id;
    document.getElementById('e-name').value    = owner.name;
    document.getElementById('e-email').value   = owner.email;
    document.getElementById('e-phone').value   = owner.phone;
    document.getElementById('e-address').value = owner.address ?? '';
    openModal('modal-edit');
  }

  function confirmDelete(id, name) {
    confirmAction(`Remover o tutor "${name}"? Os pets vinculados também serão removidos.`, () => {
      document.getElementById('delete-id').value = id;
      document.getElementById('form-delete').submit();
    });
  }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>