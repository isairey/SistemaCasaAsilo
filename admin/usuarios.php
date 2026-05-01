<?php
require __DIR__ . '/auth.php';
require_admin();
$message='';
// include minimal header (se removió header compartido)
$title = 'Admin - Usuarios';
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title><link rel="stylesheet" href="inc/admin-min.css"><link rel="stylesheet" href="inc/tabs.css"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous"></head><body>';
include __DIR__ . '/inc/tabs.php';
echo '<div class="container py-4">';

// CSRF token simple (auth.php inicia la sesión)
if (empty($_SESSION['csrf_token'])) {
  try {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
  } catch (Exception $e) {
    $_SESSION['csrf_token'] = bin2hex(md5(uniqid('', true)));
  }
}

// Manejo de POST: eliminación individual o en lote (bulk)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $token)) {
    $message = 'Token CSRF inválido.';
  } else {
    // eliminación en lote
    if (!empty($_POST['bulk_delete']) && !empty($_POST['ids']) && is_array($_POST['ids'])) {
      $ids = array_map('intval', $_POST['ids']);
      if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario IN ($placeholders)");
        $stmt->execute($ids);
        $message = 'Usuarios eliminados.';
      }
    }
    // eliminación individual (button por fila)
    if (!empty($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
      $id = (int)$_POST['delete_id'];
      $stmt = $pdo->prepare('DELETE FROM usuario WHERE id_usuario = ?');
      $stmt->execute([$id]);
      $message = 'Usuario eliminado.';
    }
  }
}
// list users — construimos la consulta de forma robusta según columnas disponibles
try {
  $colsStmt = $pdo->query("SHOW COLUMNS FROM usuario");
  $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) {
  $cols = [];
}

$nameCol = null;
if (in_array('Nombres', $cols)) $nameCol = 'Nombres';
elseif (in_array('Nombre', $cols)) $nameCol = 'Nombre';

$selectCols = ['id_usuario', 'Usuario', 'Correo', 'Telefono', 'rol_id'];
// incluir Apellidos si existe
if (in_array('Apellidos', $cols)) $selectCols[] = 'Apellidos';
// incluir columna de nombre si existe
if ($nameCol) array_unshift($selectCols, $nameCol);

$sql = 'SELECT ' . implode(', ', array_map(function($c){ return "`$c`"; }, $selectCols)) . ' FROM usuario ORDER BY id_usuario DESC';
$users = $pdo->query($sql)->fetchAll();
$roles = $pdo->query('SELECT id_rol, nombre FROM rol')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
  <div class="container my-4">
    <div class="admin-topbar d-flex align-items-center justify-content-between mb-3">
      <h3 class="mb-0" style="color: #fff;">Gestionar empleados</h3>
      <div>
        <form method="post" id="bulkActionForm" class="d-inline">
          <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
          <input type="hidden" name="bulk_delete" value="1">
          <button type="submit" class="btn btn-danger btn-sm" id="bulkDeleteBtn" onclick="return confirm('¿Eliminar los usuarios seleccionados?')"><i class="fa fa-minus-circle"></i> Borrar</button>
          <a href="manage.php?table=usuario" class="btn btn-success btn-sm"><i class="fa fa-plus-circle"></i> Agregar nuevo empleado</a>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body">
        <?php if($message): ?>
          <div class="alert alert-info" role="alert"><?php echo e($message); ?></div>
        <?php endif; ?>

        <div class="table-responsive">
          <form method="post" id="usersForm">
            <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="bulk_delete" value="1">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
                  <th>Nombre</th>
                  <th>Usuario</th>
                  <th>Correo electrónico</th>
                  <th>Teléfono</th>
                  <th>Rol</th>
                  <th style="width:140px;">Comportamiento</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach($users as $u): ?>
                <tr>
                  <td><input type="checkbox" name="ids[]" value="<?php echo e($u['id_usuario']); ?>" class="row-checkbox"></td>
                  <?php
                    // calcular nombre visible
                    $displayName = '';
                    if (!empty($nameCol) && isset($u[$nameCol])) {
                      $displayName = $u[$nameCol];
                    } elseif (isset($u['Nombre'])) {
                      $displayName = $u['Nombre'];
                    } elseif (isset($u['Usuario'])) {
                      $displayName = $u['Usuario'];
                    }
                    if (isset($u['Apellidos']) && $u['Apellidos']) $displayName = trim($displayName . ' ' . $u['Apellidos']);
                  ?>
                  <td><?php echo e($displayName); ?></td>
                  <td><?php echo e($u['Usuario']); ?></td>
                  <td><?php echo e($u['Correo']); ?></td>
                  <td><?php echo e($u['Telefono']); ?></td>
                  <td><?php echo e($roles[$u['rol_id']] ?? '-'); ?></td>
                  <td class="table-actions actions">
                    <a href="manage.php?table=usuario&edit=<?php echo e($u['id_usuario']); ?>" class="btn action-btn-edit btn-sm" title="Editar">Edit</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Eliminar este usuario?');">
                      <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="delete_id" value="<?php echo e($u['id_usuario']); ?>">
                      <button type="submit" class="btn action-btn-delete btn-sm" title="Eliminar">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script>
    // seleccionar/desmarcar todo
    document.getElementById('selectAll').addEventListener('change', function(e){
      document.querySelectorAll('.row-checkbox').forEach(function(cb){ cb.checked = e.target.checked; });
    });

    // enviar formulario de borrado masivo con confirm
    document.getElementById('bulkActionForm').addEventListener('submit', function(e){
      // copiamos los ids seleccionados del formulario de la tabla al formulario de cabecera
      var checked = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(function(cb){ return cb.value; });
      if (!checked.length) {
        alert('Selecciona al menos un registro.');
        e.preventDefault();
        return false;
      }
      // crear inputs en el formulario de cabecera
      // eliminar inputs previos ids[]
      var form = this;
      form.querySelectorAll('input[name="ids[]"]').forEach(function(n){ n.remove(); });
      checked.forEach(function(id){
        var i = document.createElement('input');
        i.type = 'hidden'; i.name = 'ids[]'; i.value = id; form.appendChild(i);
      });
      // dejar que el submit continúe
    });
  </script>
<?php echo '</div></body></html>'; ?>