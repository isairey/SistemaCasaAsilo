<?php
require __DIR__ . '/auth.php';
require_admin();
$message='';
// include minimal header (el header compartido fue removido)
$title = 'Admin - Contactos';
<<<<<<< HEAD
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title><link rel="stylesheet" href="../scr/styles/styles.css"><link rel="stylesheet" href="inc/tabs.css"><link rel="stylesheet" href="inc/admin-style.css"></head><body>';
=======
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title><link rel="stylesheet" href="/casabonsai/scr/styles/styles.css"><link rel="stylesheet" href="/casabonsai/admin/inc/tabs.css"><link rel="stylesheet" href="/casabonsai/admin/inc/admin-style.css"></head><body>';
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
include __DIR__ . '/inc/tabs.php';
echo '<div class="container py-4">';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])){
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM contacto WHERE id_contacto = ?');
    $stmt->execute([$id]);
    $message = 'Contacto eliminado.';
}
$cols = [];
try {
  $colsStmt = $pdo->query("SHOW COLUMNS FROM contacto");
  $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) {
  $cols = [];
}

// helper para elegir la primera columna disponible entre opciones
function pick_col($candidates, $cols){
  foreach($candidates as $c){ if (in_array($c, $cols)) return $c; }
  return null;
}

$idCol = pick_col(['id_contacto','ID','Id_contacto'], $cols) ?? 'id_contacto';
$nameCol = pick_col(['nombre','Nombre','Nombres'], $cols);
$emailCol = pick_col(['correo','Correo','email','Email'], $cols);
$phoneCol = pick_col(['telefono','Telefono','telefono_contacto'], $cols);
$msgCol = pick_col(['mensaje','Mensaje','mensaje_contacto'], $cols);

$select = [$idCol];
if ($nameCol) $select[] = $nameCol;
if ($emailCol) $select[] = $emailCol;
if ($phoneCol) $select[] = $phoneCol;
if ($msgCol) $select[] = $msgCol;

$sql = 'SELECT ' . implode(', ', array_map(function($c){ return "`$c`"; }, $select)) . ' FROM contacto ORDER BY ' . $idCol . ' DESC';
try {
  $items = $pdo->query($sql)->fetchAll();
} catch (Exception $e) {
  $items = [];
}
?>
  <div class="dash"><div class="card"><h2>Contactos</h2><?php if($message): ?><p class="muted-small"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if(count($items)): ?>
    <table><thead><tr><th>ID</th><th>Nombre</th><th>Correo</th><th>Tel</th><th>Mensaje</th><th>Acción</th></tr></thead><tbody>
      <?php foreach($items as $it): ?>
        <tr>
            <td><?php echo e($it[$idCol] ?? ($it['id_contacto'] ?? '')); ?></td>
            <td><?php echo e($it[$nameCol] ?? ($it['nombre'] ?? '')); ?></td>
            <td><?php echo e($it[$emailCol] ?? ($it['correo'] ?? '')); ?></td>
            <td><?php echo e($it[$phoneCol] ?? ($it['telefono'] ?? '')); ?></td>
            <td><?php echo e($it[$msgCol] ?? ($it['mensaje'] ?? '')); ?></td>
            <td class="actions">
              <a href="?edit=<?php echo e($it[$idCol] ?? ($it['id_contacto'] ?? '')); ?>" class="btn action-btn-edit btn-sm">Edit</a>
              <a class="btn action-btn-delete btn-sm" href="?delete=<?php echo e($it[$idCol] ?? ($it['id_contacto'] ?? '')); ?>" onclick="return confirm('Eliminar?')">Delete</a>
            </td>
          </tr>
      <?php endforeach; ?>
    </tbody></table>
    <?php else: ?><p class="muted-small">No hay contactos.</p><?php endif; ?>
  </div></div>
  <?php echo '</div></body></html>'; ?>