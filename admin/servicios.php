<?php
require __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/../conn.php';
$message = '';

// include minimal header (el header compartido fue removido)
  $title = 'Admin - Servicios';
  echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title>' .
       '<link rel="stylesheet" href="inc/admin-min.css">' .
       '<link rel="stylesheet" href="inc/tabs.css">' .
       '</head><body>';
include __DIR__ . '/inc/tabs.php';
echo '<div class="container py-4">';

// Detectar columnas en `servicio`
$cols = [];
try {
  $colsStmt = $pdo->query("SHOW COLUMNS FROM servicio");
  $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) { $cols = []; }

function pick_col($candidates, $cols){
  foreach($candidates as $c){ if (in_array($c, $cols)) return $c; }
  return null;
}

$idCol = pick_col(['id_servicio','id','ID'], $cols) ?? 'id_servicio';
$titleCol = pick_col(['titulo','Titulo','nombre','Nombre'], $cols) ?? 'titulo';
$descCol = pick_col(['descripcion','Descripcion','desc'], $cols);
$priceCol = pick_col(['precio','Precio','coste'], $cols);
$durCol = pick_col(['duracion_min','duracion','duracion_minutos','duracion_minutos'], $cols);

// Procesar POST con columnas disponibles
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titulo = trim($_POST['titulo'] ?? '');
  if ($titulo === '') {
    $message = 'El título es obligatorio.';
  } else {
    $insertCols = [];
    $placeholders = [];
    $values = [];
    if ($titleCol) { $insertCols[] = "`$titleCol`"; $placeholders[] = '?'; $values[] = $titulo; }
    if ($descCol) { $insertCols[] = "`$descCol`"; $placeholders[] = '?'; $values[] = $_POST['descripcion'] ?? null; }
    if ($priceCol) { $insertCols[] = "`$priceCol`"; $placeholders[] = '?'; $values[] = isset($_POST['precio']) ? floatval($_POST['precio']) : null; }
    if ($durCol) { $insertCols[] = "`$durCol`"; $placeholders[] = '?'; $values[] = isset($_POST['duracion_min']) ? intval($_POST['duracion_min']) : null; }
    if (in_array('activo', $cols)) { $insertCols[] = '`activo`'; $placeholders[] = '?'; $values[] = 1; }

    if (count($insertCols) === 0) {
      $message = 'No hay columnas válidas para insertar servicio en la tabla.';
    } else {
      $sql = 'INSERT INTO servicio (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';
      try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        $message = 'Servicio agregado.';
      } catch (Exception $e) {
        $message = 'Error al agregar servicio: ' . htmlspecialchars($e->getMessage());
      }
    }
  }
}

<<<<<<< HEAD
// Manejo de eliminación simple (si se envía `delete_id`)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
  $delId = $_POST['delete_id'];
  try {
    $delStmt = $pdo->prepare("DELETE FROM servicio WHERE `$idCol` = ?");
    $delStmt->execute([$delId]);
    $message = 'Servicio eliminado.';
  } catch (Exception $e) {
    $message = 'Error al eliminar: ' . htmlspecialchars($e->getMessage());
  }
}

=======
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
// Listado
$selectCols = [$idCol];
if ($titleCol) $selectCols[] = $titleCol;
if ($priceCol) $selectCols[] = $priceCol;
if ($durCol) $selectCols[] = $durCol;
try {
  $servicios = $pdo->query('SELECT ' . implode(', ', array_map(function($c){ return "`$c`"; }, $selectCols)) . ' FROM servicio ORDER BY ' . $idCol . ' DESC')->fetchAll();
} catch (Exception $e) {
  $servicios = [];
}
?>
  <div class="dash">
    <div class="card">
      <h2>Servicios</h2>
      <?php if($message): ?><p class="muted-small"><?php echo e($message); ?></p><?php endif; ?>
      <form method="post">
        <label>Título<input name="titulo" required></label>
        <label>Descripción<textarea name="descripcion"></textarea></label>
        <label>Precio<input name="precio" type="number" step="0.01"></label>
        <label>Duración (min)<input name="duracion_min" type="number"></label>
        <button type="submit" class="btn btn-buy">Agregar</button>
      </form>
    </div>

    <div class="card" style="margin-top:18px">
      <h3>Listado</h3>
      <?php if(count($servicios)): ?>
        <table>
          <thead><tr><th>ID</th><th>Título</th><th>Precio</th><th>Duración</th></tr></thead>
          <tbody>
            <?php foreach($servicios as $s): ?>
              <tr>
                <td><?php echo e($s[$idCol] ?? ($s['id_servicio'] ?? '')); ?></td>
                <td><?php echo e($s[$titleCol] ?? ($s['titulo'] ?? '')); ?></td>
                <td><?php echo e(isset($priceCol) ? ($s[$priceCol] ?? '') : ($s['precio'] ?? '')); ?></td>
                <td><?php echo e(isset($durCol) ? ($s[$durCol] ?? '') : ($s['duracion_min'] ?? '')); ?></td>
                  <td class="actions">
                <a href="manage.php?table=servicio&edit=<?php echo e($s[$idCol]); ?>" class="btn action-btn-edit btn-sm" title="Editar">Edit</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Eliminar este servicio?');">
                  <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="delete_id" value="<?php echo e($s[$idCol]); ?>">
                  <button type="submit" class="btn action-btn-delete btn-sm">Delete</button>
                </form>
                </td>
<<<<<<< HEAD
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No hay servicios registrados.</p>
      <?php endif; ?>
    </div>
  </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>
</body>
</html>

<?php
=======
  <?php echo '</div></body></html>'; ?>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
