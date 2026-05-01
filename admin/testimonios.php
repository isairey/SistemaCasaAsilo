<<<<<<< HEAD
<?php
require __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/../conn.php';
$message = '';

// helper escaper (definir solo si no existe)
if (!function_exists('e')) {
  function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
}

// include minimal header
$title = 'Admin - Testimonios';
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title>' .
     '<link rel="stylesheet" href="../scr/styles/styles.css">' .
     '<link rel="stylesheet" href="inc/tabs.css">' .
     '<link rel="stylesheet" href="inc/admin-style.css">' .
     '</head><body>';
include __DIR__ . '/inc/tabs.php';
echo '<div class="container py-4">';

// Delete via GET (simple pattern used en otras páginas)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $id = (int)$_GET['delete'];
    try {
      $stmt = $pdo->prepare('DELETE FROM testimonio WHERE id_testimonio = ?');
      $stmt->execute([$id]);
      $message = 'Testimonio eliminado.';
    } catch (Exception $ex) {
      $message = 'Error al eliminar testimonio: ' . e($ex->getMessage());
    }
}

// Detectar columnas
=======
              <tr>
                <td><?php echo e($t[$idCol] ?? ''); ?></td>
                <td><?php echo e($t['nombre'] ?? ''); ?></td>
                <td><?php echo e($t['mensaje'] ?? ''); ?></td>
                <td class="actions">
                  <a href="manage.php?table=testimonio&edit=<?php echo e($t[$idCol]); ?>" class="btn action-btn-edit btn-sm">Edit</a>
                  <form method="post" class="d-inline" onsubmit="return confirm('Eliminar este testimonio?');">
                    <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                    <input type="hidden" name="delete_id" value="<?php echo e($t[$idCol]); ?>">
                    <button type="submit" class="btn action-btn-delete btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
    $message = 'Testimonio eliminado.';
}
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
$cols = [];
try {
  $colsStmt = $pdo->query("SHOW COLUMNS FROM testimonio");
  $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) {
  $cols = [];
}

function pick_col($candidates, $cols){
  foreach($candidates as $c){ if (in_array($c, $cols)) return $c; }
  return null;
}

$idCol = pick_col(['id_testimonio','id','ID'], $cols) ?? 'id_testimonio';
$nameCol = pick_col(['nombre','Nombre','Nombres'], $cols);
$msgCol = pick_col(['mensaje','Mensaje','contenido'], $cols);
$pubCol = pick_col(['publicado','Publicado','activo'], $cols);

$select = [$idCol];
if ($nameCol) $select[] = $nameCol;
if ($msgCol) $select[] = $msgCol;
if ($pubCol) $select[] = $pubCol;

$sql = 'SELECT ' . implode(', ', array_map(function($c){ return "`$c`"; }, $select)) . ' FROM testimonio ORDER BY ' . $idCol . ' DESC';
try {
  $items = $pdo->query($sql)->fetchAll();
} catch (Exception $e) {
  $items = [];
}
?>
  <div class="dash"><div class="card"><h2>Testimonios</h2><?php if($message): ?><p class="muted-small"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if(count($items)): ?>
    <table><thead><tr><th>ID</th><th>Nombre</th><th>Mensaje</th><th>Publicado</th><th>Acción</th></tr></thead><tbody>
      <?php foreach($items as $it): ?>
        <tr>
          <td><?php echo e($it[$idCol] ?? ($it['id_testimonio'] ?? '')); ?></td>
          <td><?php echo e($it[$nameCol] ?? ($it['nombre'] ?? '')); ?></td>
          <td><?php echo e($it[$msgCol] ?? ($it['mensaje'] ?? '')); ?></td>
          <td><?php echo e($it[$pubCol] ?? ($it['publicado'] ?? '')); ?></td>
<<<<<<< HEAD
          <td class="actions">
            <a href="manage.php?table=testimonio&edit=<?php echo e($it[$idCol] ?? ($it['id_testimonio'] ?? '')); ?>" class="btn action-btn-edit btn-sm">Edit</a>
            <a class="btn action-btn-delete btn-sm" href="?delete=<?php echo e($it[$idCol] ?? ($it['id_testimonio'] ?? '')); ?>" onclick="return confirm('Eliminar?')">Delete</a>
          </td>
=======
          <td><a class="danger" href="?delete=<?php echo e($it[$idCol] ?? ($it['id_testimonio'] ?? '')); ?>" onclick="return confirm('Eliminar?')">Eliminar</a></td>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
        </tr>
      <?php endforeach; ?>
    </tbody></table>
    <?php else: ?><p class="muted-small">No hay testimonios.</p><?php endif; ?>
  </div></div>
<<<<<<< HEAD
<?php echo '</div></body></html>'; ?>
=======
  <?php echo '</div></body></html>'; ?>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
