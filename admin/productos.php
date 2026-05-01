<?php
require __DIR__ . '/auth.php';
require_admin();
require_once __DIR__ . '/../conn.php';
$message = '';
// include minimal header (el header compartido fue removido)
$title = 'Admin - Productos';
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title><link rel="stylesheet" href="inc/admin-min.css"><link rel="stylesheet" href="inc/tabs.css"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous"></head><body>';
include __DIR__ . '/inc/tabs.php';
echo '<div class="container py-4">';
<<<<<<< HEAD
// estilos específicos para mejorar el aspecto del listado de productos
echo '<style>
  body { background:#f6f6f2; font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; }
  .admin-topbar { padding:18px 20px; background:linear-gradient(180deg,#1b5e20,#123f16); border-radius:6px; }
  .admin-topbar h3 { margin:0; color:#fff; font-weight:700; }
  .admin-topbar .btn { margin-left:8px; }
  .card { border:0; border-radius:8px; box-shadow:0 6px 18px rgba(30,30,30,0.06); }
  .table thead th { background:#fafafa; border-bottom:2px solid #eee; }
  .thumb { max-width:64px; max-height:64px; border-radius:6px; object-fit:cover; }
  .actions .btn { margin-right:6px; }
  .admin-topbar .form-control { min-width:340px; max-width:520px; }
  #searchInput { width:100%; }
  @media(min-width:992px){ #searchInput{ width:420px;} }
  .modal .modal-content { border-radius:8px; }
  .modal .form-label { font-weight:600; }
</style>';
=======
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca

// Detectar columnas disponibles en `producto`
$cols = [];
try {
  $colsStmt = $pdo->query("SHOW COLUMNS FROM producto");
  $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
} catch (Exception $e) { $cols = []; }

function pick_col($candidates, $cols){
  foreach($candidates as $c){ if (in_array($c, $cols)) return $c; }
  return null;
}

$idCol = pick_col(['id_producto','id','ID'], $cols) ?? 'id_producto';
$nameCol = pick_col(['nombre','Nombre','titulo','nombre_producto'], $cols) ?? 'nombre';
$descCol = pick_col(['descripcion','Descripcion','desc'], $cols);
$priceCol = pick_col(['precio','Precio','coste'], $cols);
$stockCol = pick_col(['stock','cantidad','existencias'], $cols);
$skuCol = pick_col(['sku','SKU'], $cols);
$imgCol = pick_col(['imagen','img','imagen_url'], $cols);

// helper: comprimir/resize imagen usando GD si está disponible
function compress_image_if_possible($file, $quality = 80, $max_width = 1200) {
  if (!function_exists('getimagesize')) return false;
  $info = @getimagesize($file);
  if (!$info) return false;
  list($width, $height) = $info;
  $mime = $info['mime'] ?? '';

  // calcular nuevo tamaño si es necesario
  if ($width > $max_width) {
    $ratio = $height / $width;
    $new_w = $max_width;
    $new_h = (int)($max_width * $ratio);
  } else {
    $new_w = $width;
    $new_h = $height;
  }

  // crear recurso según tipo
  switch ($mime) {
    case 'image/jpeg': $src = @imagecreatefromjpeg($file); break;
    case 'image/png':  $src = @imagecreatefrompng($file); break;
    case 'image/gif':  $src = @imagecreatefromgif($file); break;
    default: return false;
  }
  if (!$src) return false;

  $dst = imagecreatetruecolor($new_w, $new_h);
  // conservar transparencia para PNG/GIF
  if ($mime === 'image/png' || $mime === 'image/gif') {
    imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
    imagealphablending($dst, false);
    imagesavealpha($dst, true);
  }
  imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $width, $height);

  $ok = false;
  if ($mime === 'image/jpeg') {
    $ok = imagejpeg($dst, $file, $quality);
  } elseif ($mime === 'image/png') {
    // map quality 0-100 to compression 0-9 (inverted)
    $level = max(0, min(9, (int) round((100 - $quality) / 11)));
    $ok = imagepng($dst, $file, $level);
  } elseif ($mime === 'image/gif') {
    $ok = imagegif($dst, $file);
  }

  imagedestroy($src);
  imagedestroy($dst);
  return (bool)$ok;
}

// CSRF token simple
if (empty($_SESSION['csrf_token'])) {
    try { $_SESSION['csrf_token'] = bin2hex(random_bytes(16)); }
    catch (Exception $e) { $_SESSION['csrf_token'] = bin2hex(md5(uniqid('', true))); }
}

// Manejo de POST: bulk delete, delete single, add via modal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['csrf_token'] ?? '';
  if (!hash_equals($_SESSION['csrf_token'], $token)) {
    $message = 'Token CSRF inválido.';
  } else {
    // bulk delete
    if (!empty($_POST['bulk_delete']) && !empty($_POST['ids']) && is_array($_POST['ids'])) {
      $ids = array_map('intval', $_POST['ids']);
      if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM producto WHERE $idCol IN ($placeholders)");
        $stmt->execute($ids);
        $message = 'Productos eliminados.';
      }
    }
    // delete single
    if (!empty($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
      $id = (int)$_POST['delete_id'];
      $stmt = $pdo->prepare("DELETE FROM producto WHERE `$idCol` = ?");
      $stmt->execute([$id]);
      $message = 'Producto eliminado.';
    }
    // add new product (from modal)
    if (!empty($_POST['action']) && $_POST['action'] === 'add') {
      $nombre = trim($_POST['nombre'] ?? '');
      if ($nombre === '') {
        $message = 'El nombre es obligatorio.';
      } else {
        $insertCols = [];
        $placeholders = [];
        $values = [];
        if ($nameCol) { $insertCols[] = "`$nameCol`"; $placeholders[] = '?'; $values[] = $nombre; }
        if ($descCol) { $insertCols[] = "`$descCol`"; $placeholders[] = '?'; $values[] = $_POST['descripcion'] ?? null; }
        if ($priceCol) { $insertCols[] = "`$priceCol`"; $placeholders[] = '?'; $values[] = isset($_POST['precio']) ? floatval($_POST['precio']) : null; }
        if ($stockCol) { $insertCols[] = "`$stockCol`"; $placeholders[] = '?'; $values[] = isset($_POST['stock']) ? intval($_POST['stock']) : null; }
        if ($skuCol) { $insertCols[] = "`$skuCol`"; $placeholders[] = '?'; $values[] = $_POST['sku'] ?? null; }
        if ($imgCol) {
          // decidir si la columna de imagen es tipo texto/blob en la BD
          $colType = '';
          try {
            $tstmt = $pdo->prepare("SHOW COLUMNS FROM producto LIKE ?");
            $tstmt->execute([$imgCol]);
            $colInfo = $tstmt->fetch(PDO::FETCH_ASSOC);
            $colType = $colInfo['Type'] ?? '';
          } catch (Exception $e) { $colType = ''; }

          $uploadedPath = null;
          // Si el usuario sube un archivo
          if (!empty($_FILES['imagen_file']) && !empty($_FILES['imagen_file']['tmp_name'])) {
            $f = $_FILES['imagen_file'];
            if ($f['error'] === UPLOAD_ERR_OK) {
              // Si la columna es TEXT/LONGTEXT/BLOB -> guardamos el contenido (base64) en la BD
              if (stripos($colType, 'text') !== false || stripos($colType, 'blob') !== false) {
                // limitar tamaño razonable (ej. 8MB) para evitar inserts enormes
                if ($f['size'] > 8 * 1024 * 1024) {
                  $message = 'La imagen es demasiado grande para almacenar en la base de datos (límite 8MB).';
                } else {
                  $data = @file_get_contents($f['tmp_name']);
                  if ($data === false) {
                    $message = 'No se pudo leer el archivo subido.';
                  } else {
                    $mime = $f['type'] ?? 'image/jpeg';
                    $b64 = base64_encode($data);
                    // guardar como data URI para mostrar fácilmente: data:<mime>;base64,<data>
                    $uploadedPath = 'data:' . $mime . ';base64,' . $b64;
                  }
                }
              } else {
                // Guardar en disco y almacenar la ruta en la BD (como antes)
                $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
                $safe = bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-zA-Z0-9\.\-]/', '', $ext);
                $imagesDir = __DIR__ . '/../scr/resources/images';
                $uploadDir = $imagesDir . '/uploads';
                if (!is_dir($uploadDir)) {
                  if (!@mkdir($uploadDir, 0755, true)) {
                    $message = 'No se pudo crear el directorio de uploads. Ajusta permisos en: ' . $uploadDir;
                  }
                }
                if (is_dir($uploadDir) && is_writable($uploadDir)) {
                  $dest = $uploadDir . '/' . $safe;
                  if (@move_uploaded_file($f['tmp_name'], $dest)) {
                    $compressed = compress_image_if_possible($dest, 80, 1200);
                    $uploadedPath = '/casabonsai/scr/resources/images/uploads/' . $safe;
                  } else {
                    $message = 'No se pudo mover el archivo subido. Revisa permisos del directorio: ' . $uploadDir;
                  }
                } else {
                  if (empty($message)) $message = 'Directorio de uploads no escribible: ' . $uploadDir;
                }
              }
            } else {
              $message = 'Error al subir archivo (code ' . intval($f['error']) . ').';
            }
          }

          // si el usuario puso manualmente una ruta/texto en el form, usarla como fallback
          $fallback = $_POST['imagen'] ?? null;
          $insertCols[] = "`$imgCol`"; $placeholders[] = '?'; $values[] = $uploadedPath ?? $fallback;
        }
        if (in_array('activo', $cols)) { $insertCols[] = '`activo`'; $placeholders[] = '?'; $values[] = 1; }
        if (count($insertCols) === 0) {
          $message = 'No hay columnas válidas para insertar producto.';
        } else {
          $sql = 'INSERT INTO producto (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';
          try { $stmt = $pdo->prepare($sql); $stmt->execute($values); $message = 'Producto agregado.'; }
          catch (Exception $e) { $message = 'Error al agregar producto: ' . htmlspecialchars($e->getMessage()); }
        }
      }
    }
  }
}

// Listado: construir SELECT con columnas detectadas
$selectCols = [$idCol];
if ($nameCol && !in_array($nameCol, $selectCols)) $selectCols[] = $nameCol;
if ($priceCol && !in_array($priceCol, $selectCols)) $selectCols[] = $priceCol;
if ($stockCol && !in_array($stockCol, $selectCols)) $selectCols[] = $stockCol;
if ($skuCol && !in_array($skuCol, $selectCols)) $selectCols[] = $skuCol;
if ($imgCol && !in_array($imgCol, $selectCols)) $selectCols[] = $imgCol;
try {
  $productos = $pdo->query('SELECT ' . implode(', ', array_map(function($c){ return "`$c`"; }, $selectCols)) . ' FROM producto ORDER BY `' . $idCol . '` DESC')->fetchAll();
} catch (Exception $e) {
  $productos = [];
}
?>
  <!-- content: productos -->
  <div class="container my-4">
    <div class="admin-topbar d-flex align-items-center justify-content-between mb-3">
      <h3 class="mb-0" style="color:#fff;">Productos</h3>
      <div>
        <form method="post" id="bulkTopForm" class="d-inline">
          <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
          <input type="hidden" name="bulk_delete" value="1">
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar productos seleccionados?')"><i class="fa fa-minus-circle"></i> Borrar</button>
          <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addModal"><i class="fa fa-plus-circle"></i> Agregar producto</button>
        </form>
      </div>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <?php if($message): ?>
          <div class="alert alert-info"><?php echo e($message); ?></div>
        <?php endif; ?>

        <div class="row mb-2">
          <div class="col-md-6">
            <input id="searchInput" class="form-control" placeholder="Buscar por nombre, SKU...">
          </div>
          <div class="col-md-6 text-end text-muted small">Total: <?php echo e(count($productos)); ?> productos</div>
        </div>

        <form method="post" id="productsForm">
          <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
          <input type="hidden" name="bulk_delete" value="1">
          <div class="table-responsive">
            <table class="table table-hover align-middle" id="productsTable">
              <thead class="table-light">
                <tr>
                  <th style="width:40px;"><input type="checkbox" id="selectAll"></th>
                  <?php if($imgCol): ?><th>Imagen</th><?php endif; ?>
                  <th>Nombre</th>
                  <?php if($skuCol): ?><th>SKU</th><?php endif; ?>
                  <?php if($priceCol): ?><th>Precio</th><?php endif; ?>
                  <?php if($stockCol): ?><th>Stock</th><?php endif; ?>
                  <th style="width:140px;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($productos as $p): ?>
                <tr>
                  <td><input type="checkbox" name="ids[]" value="<?php echo e($p[$idCol]); ?>" class="row-checkbox"></td>
                  <?php if($imgCol): ?>
                    <td>
                      <?php if(!empty($p[$imgCol])): ?>
                        <img src="<?php echo e($p[$imgCol]); ?>" class="thumb" alt="thumb">
                      <?php else: ?>
                        <span class="text-muted small">—</span>
                      <?php endif; ?>
                    </td>
                  <?php endif; ?>
                  <td><?php echo e($p[$nameCol] ?? ''); ?></td>
                  <?php if($skuCol): ?><td><?php echo e($p[$skuCol] ?? ''); ?></td><?php endif; ?>
                  <?php if($priceCol): ?><td><?php echo e(isset($p[$priceCol]) ? number_format((float)$p[$priceCol], 2) : ''); ?></td><?php endif; ?>
                  <?php if($stockCol): ?><td><?php echo e($p[$stockCol] ?? ''); ?></td><?php endif; ?>
                  <td class="actions">
                    <a href="manage.php?table=producto&edit=<?php echo e($p[$idCol]); ?>" class="btn action-btn-edit btn-sm" title="Editar">Edit</a>
                    <form method="post" class="d-inline" onsubmit="return confirm('Eliminar este producto?');">
                      <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="delete_id" value="<?php echo e($p[$idCol]); ?>">
                      <button type="submit" class="btn action-btn-delete btn-sm">Delete</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Agregar -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Agregar producto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
        <form method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="modal-body">
              <input type="hidden" name="csrf_token" value="<?php echo e($_SESSION['csrf_token']); ?>">
              <input type="hidden" name="action" value="add">
              <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input name="nombre" class="form-control" required>
              </div>
              <?php if($descCol): ?>
              <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="3"></textarea>
              </div>
              <?php endif; ?>
              <div class="row g-2">
                <?php if($priceCol): ?><div class="col-md-4"><label class="form-label">Precio</label><input name="precio" type="number" step="0.01" class="form-control"></div><?php endif; ?>
                <?php if($stockCol): ?><div class="col-md-4"><label class="form-label">Stock</label><input name="stock" type="number" class="form-control"></div><?php endif; ?>
                <?php if($skuCol): ?><div class="col-md-4"><label class="form-label">SKU</label><input name="sku" class="form-control"></div><?php endif; ?>
              </div>
              <?php if($imgCol): ?>
              <div class="mb-3 mt-3">
                <label class="form-label">Imagen</label>
                <div class="input-group">
                  <input type="file" name="imagen_file" id="imagen_file" accept="image/*" class="form-control">
                </div>
                <div class="mt-2">
                  <img id="previewImg" src="" class="thumb" style="display:none;" alt="preview">
                </div>
              </div>
              <?php endif; ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    // seleccionar/desmarcar todo
    document.addEventListener('DOMContentLoaded', function(){
      var selectAll = document.getElementById('selectAll');
      if (selectAll) selectAll.addEventListener('change', function(e){ document.querySelectorAll('.row-checkbox').forEach(function(cb){ cb.checked = e.target.checked; }); });

      // busqueda simple cliente
      var search = document.getElementById('searchInput');
      if (search) search.addEventListener('input', function(){
        var q = this.value.toLowerCase();
        document.querySelectorAll('#productsTable tbody tr').forEach(function(row){
          var text = row.textContent.toLowerCase();
          row.style.display = text.indexOf(q) === -1 ? 'none' : '';
        });
      });
<<<<<<< HEAD
        // focus en el campo Nombre cuando se abre el modal Agregar
        var addModalEl = document.getElementById('addModal');
        if (addModalEl) {
          addModalEl.addEventListener('shown.bs.modal', function () {
            var first = addModalEl.querySelector('input[name="nombre"]');
            if (first) first.focus();
          });
        }
=======
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca

      // cuando el formulario superior se envíe (bulkTopForm), copiar ids seleccionados
      var topForm = document.getElementById('bulkTopForm');
      if (topForm) topForm.addEventListener('submit', function(e){
        var checked = Array.from(document.querySelectorAll('.row-checkbox:checked')).map(function(cb){ return cb.value; });
        if (!checked.length) { alert('Selecciona al menos un producto.'); e.preventDefault(); return false; }
        // eliminar inputs previos
        topForm.querySelectorAll('input[name="ids[]"]').forEach(function(n){ n.remove(); });
        checked.forEach(function(id){ var i = document.createElement('input'); i.type='hidden'; i.name='ids[]'; i.value=id; topForm.appendChild(i); });
      });
      // preview imagen en modal
      var fileInput = document.getElementById('imagen_file');
      var preview = document.getElementById('previewImg');
      if (fileInput) {
        fileInput.addEventListener('change', function(){
          var f = this.files && this.files[0];
          if (!f) { preview.style.display='none'; preview.src=''; return; }
          if (!f.type.startsWith('image/')) { preview.style.display='none'; preview.src=''; return; }
          var reader = new FileReader();
          reader.onload = function(ev){ preview.src = ev.target.result; preview.style.display = 'inline-block'; };
          reader.readAsDataURL(f);
        });
      }
    });
  </script>
<<<<<<< HEAD
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
=======
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
<?php echo '</div></body></html>'; ?>