<?php
require __DIR__ . '/auth.php';
require_once __DIR__ . '/../conn.php';
require_admin();

// include minimal header (el header compartido fue removido)
$title = 'Admin - Gestión genérica';
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title>'
  . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">'
  . '<link rel="stylesheet" href="inc/admin-min.css">'
  . '<link rel="stylesheet" href="inc/tabs.css">'
  . '</head><body>';
include __DIR__ . '/inc/tabs.php';
echo '<div class="container py-4">';

// helper: comprimir/resize imagen usando GD si está disponible
function compress_image_if_possible($file, $quality = 80, $max_width = 1200) {
  // require getimagesize
  if (!function_exists('getimagesize')) return false;
  $info = @getimagesize($file);
  if (!$info) return false;
  list($width, $height) = $info;
  $mime = $info['mime'] ?? '';

  if ($width > $max_width) {
    $ratio = $height / $width;
    $new_w = $max_width;
    $new_h = (int)($max_width * $ratio);
  } else {
    $new_w = $width;
    $new_h = $height;
  }

  // create source image depending on mime, but only if loader exists
  $src = false;
  if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
    $src = @imagecreatefromjpeg($file);
  } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
    $src = @imagecreatefrompng($file);
  } elseif ($mime === 'image/gif' && function_exists('imagecreatefromgif')) {
    $src = @imagecreatefromgif($file);
  } else {
    // GD loader not available for this mime, skip compression
    return false;
  }

  if (!$src) return false;
  $dst = @imagecreatetruecolor($new_w, $new_h);
  if (!$dst) {
    if (is_resource($src)) @imagedestroy($src);
    return false;
  }
  if ($mime === 'image/png') {
    if (function_exists('imagealphablending')) imagealphablending($dst, false);
    if (function_exists('imagesavealpha')) imagesavealpha($dst, true);
  }
  imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $width, $height);

  switch ($mime) {
    case 'image/jpeg':
      if (function_exists('imagejpeg')) imagejpeg($dst, $file, $quality);
      break;
    case 'image/png':
      if (function_exists('imagepng')) {
        $pngLevel = max(0, min(9, (int)round(9 - ($quality / 100) * 9)));
        imagepng($dst, $file, $pngLevel);
      }
      break;
    case 'image/gif':
      if (function_exists('imagegif')) imagegif($dst, $file);
      break;
  }

  if (isset($src) && function_exists('imagedestroy')) { @imagedestroy($src); }
  if (isset($dst) && function_exists('imagedestroy')) { @imagedestroy($dst); }
  return true;
}

// Compress image into a binary string (in-memory) and return it, or false on failure.
function compress_image_to_string($file, $quality = 80, $max_width = 1200) {
  if (!function_exists('getimagesize')) return false;
  $info = @getimagesize($file);
  if (!$info) return false;
  list($width, $height) = $info;
  $mime = $info['mime'] ?? '';

  // calculate new size
  if ($width > $max_width) {
    $ratio = $height / $width;
    $new_w = $max_width;
    $new_h = (int)($max_width * $ratio);
  } else {
    $new_w = $width;
    $new_h = $height;
  }

  $src = false;
  if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
    $src = @imagecreatefromjpeg($file);
  } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
    $src = @imagecreatefrompng($file);
  } elseif ($mime === 'image/gif' && function_exists('imagecreatefromgif')) {
    $src = @imagecreatefromgif($file);
  } else {
    return false;
  }
  if (!$src) return false;

  $dst = @imagecreatetruecolor($new_w, $new_h);
  if (!$dst) { if (is_resource($src)) @imagedestroy($src); return false; }
  if ($mime === 'image/png') {
    if (function_exists('imagealphablending')) imagealphablending($dst, false);
    if (function_exists('imagesavealpha')) imagesavealpha($dst, true);
  }
  imagecopyresampled($dst, $src, 0,0,0,0, $new_w, $new_h, $width, $height);

  // capture output to string
  ob_start();
  switch ($mime) {
    case 'image/jpeg':
      if (function_exists('imagejpeg')) imagejpeg($dst, null, $quality);
      break;
    case 'image/png':
      if (function_exists('imagepng')) {
        $pngLevel = max(0, min(9, (int)round(9 - ($quality / 100) * 9)));
        imagepng($dst, null, $pngLevel);
      }
      break;
    case 'image/gif':
      if (function_exists('imagegif')) imagegif($dst);
      break;
  }
  $bin = ob_get_clean();

  if (isset($src) && function_exists('imagedestroy')) { @imagedestroy($src); }
  if (isset($dst) && function_exists('imagedestroy')) { @imagedestroy($dst); }

  return $bin ?: false;
}

// Flag: cuando es true, NO se escriben archivos en disco para imágenes;
// en su lugar se guardan como data URLs base64 en la BD (útil cuando el host
// no permite escribir o servir la carpeta uploads). Ajustar según necesidad.
$FORCE_DB_IMAGE_STORAGE = true;

// Tamaño máximo recomendado para almacenar imagen en la BD (bytes).
// Si la data URL excede este tamaño se rechaza para evitar errores en MySQL.
$DB_IMAGE_MAX_BYTES = 3 * 1024 * 1024; // 3MB

// Depuración de uploads (temporal): cuando está en true activamos display_errors
// y volcamos POST/FILES/errores a `admin/logs/debug_upload.log`.
$ENABLE_UPLOAD_DEBUG = true;
$DEBUG_LOG_DIR = __DIR__ . '/logs';
$DEBUG_LOG_FILE = $DEBUG_LOG_DIR . '/debug_upload.log';

function admin_debug_log($note, $context = []) {
  global $ENABLE_UPLOAD_DEBUG, $DEBUG_LOG_DIR, $DEBUG_LOG_FILE;
  if (empty($ENABLE_UPLOAD_DEBUG)) return;
  if (!is_dir($DEBUG_LOG_DIR)) @mkdir($DEBUG_LOG_DIR, 0755, true);
  $entry = [
    'ts' => date('c'),
    'note' => $note,
    'context' => $context
  ];
  @file_put_contents($DEBUG_LOG_FILE, json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
}

// Save a data:...;base64, payload to uploads and return web path or false
function save_base64_to_disk($dataUrl, $origName = 'img') {
  if (!preg_match('#^data:([^;]+);base64,(.*)$#s', $dataUrl, $m)) return false;
  $mime = $m[1];
  $b64 = $m[2];
  $bin = base64_decode($b64);
  if ($bin === false) return false;
  // choose extension from mime
  $ext = 'bin';
  if (strpos($mime,'jpeg')!==false || strpos($mime,'jpg')!==false) $ext = 'jpg';
  elseif (strpos($mime,'png')!==false) $ext = 'png';
  elseif (strpos($mime,'gif')!==false) $ext = 'gif';
  $safe = bin2hex(random_bytes(8)) . '_' . preg_replace('/[^a-zA-Z0-9_\-]/','',substr($origName,0,12)) . '.' . $ext;
  $imagesDir = __DIR__ . '/../scr/resources/images';
  $uploadDir = $imagesDir . '/uploads';
  if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
  $dest = $uploadDir . '/' . $safe;
  if (@file_put_contents($dest, $bin) === false) return false;
  @compress_image_if_possible($dest, 80, 1200);
  return '/casabonsai/scr/resources/images/uploads/' . $safe;
}

  // Determine which table we're managing and load its columns
  $table = '';
  if (!empty($_REQUEST['table'])) {
    // allow only safe table name chars
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $_REQUEST['table']);
  }

  // ensure session CSRF token exists
  if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
  $csrf = $_SESSION['csrf'];

  $cols = [];
  $idCol = null;
  if ($table !== '') {
    try {
      $s = $pdo->prepare("SHOW COLUMNS FROM `" . $table . "`");
      $s->execute();
      $cols = $s->fetchAll(PDO::FETCH_ASSOC);
      if ($cols) {
        foreach ($cols as $c) {
          if (!empty($c['Key']) && strtoupper($c['Key']) === 'PRI') { $idCol = $c['Field']; break; }
        }
        if (!$idCol) $idCol = $cols[0]['Field'];
      } else {
        $error = 'Tabla no encontrada o sin columnas: ' . e($table);
      }
    } catch (Exception $ex) {
      $error = 'Error al obtener columnas de la tabla: ' . $ex->getMessage();
      $cols = [];
    }
  } else {
    $error = $error ?? 'No se indicó tabla para gestionar.';
  }

// simple admin action logger (append-only, JSON lines)
// NOTE: admin logging was removed to simplify the manager per request.

// Detectar tablas de producto y preparar orden/heurística de campos
$tableKey = strtolower($table);
$isProductTable = false;
if (preg_match('/prod|producto|productos/', $tableKey)) $isProductTable = true;
$preferredProductOrder = ['imagen','img','image','picture','foto','nombre','name','sku','precio','price','stock','descripcion','description','activo','creado_at','creado_por'];
// construir cols ordenadas para la UI si es tabla producto
$colsOrdered = $cols;
if ($isProductTable && count($cols) > 1) {
  $ordered = [];
  // map fields by lowercase name
  $map = [];
  foreach ($cols as $c) $map[strtolower($c['Field'])] = $c;
  // push preferred first
  foreach ($preferredProductOrder as $k) {
    if (isset($map[$k])) { $ordered[] = $map[$k]; unset($map[$k]); }
  }
  // then push the rest preserving original order
  foreach ($cols as $c) {
    $k = strtolower($c['Field']);
    if (isset($map[$k])) { $ordered[] = $c; unset($map[$k]); }
  }
  if (count($ordered)) $colsOrdered = $ordered;
}

// Handle POST actions: create, update, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // only allow POST when we have a valid target table
    if ($table === '') {
      $error = 'Tabla inválida.';
    }
    if (empty($_POST['csrf']) || ($_POST['csrf'] !== $csrf)) {
      $error = 'Token CSRF inválido.';
    } else {
      // Debug: registrar snapshot inicial de POST/FILES
      if (!empty($ENABLE_UPLOAD_DEBUG)) {
        @ini_set('display_errors', '1');
        @error_reporting(E_ALL);
        $filesInfo = [];
        foreach ($_FILES as $k => $fi) {
          $filesInfo[$k] = [
            'name' => $fi['name'] ?? null,
            'size' => $fi['size'] ?? null,
            'type' => $fi['type'] ?? null,
            'error' => $fi['error'] ?? null,
          ];
        }
        admin_debug_log('POST start', ['table'=>$table, 'post'=>array_slice($_POST,0,50), 'files'=>$filesInfo]);
      }
        // Delete single
        if (!empty($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
          $id = intval($_POST['id']);
          $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE `{$idCol}` = ? LIMIT 1");
          $stmt->execute([$id]);
          $message = 'Registro eliminado.';
        }
        // Bulk delete
        elseif (!empty($_POST['action']) && $_POST['action'] === 'bulk_delete' && !empty($_POST['ids']) && is_array($_POST['ids'])) {
            $ids = array_map('intval', $_POST['ids']);
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("DELETE FROM `{$table}` WHERE `{$idCol}` IN ({$ph})");
            $stmt->execute($ids);
            $message = 'Registros eliminados: ' . count($ids);
        }
        // Create or update
        else {
            $isUpdate = !empty($_POST['id']);
            $fields = [];
            $placeholders = [];
            $values = [];

            foreach($cols as $c) {
                $f = $c['Field'];
                if ($f === $idCol && !$isUpdate) continue; // skip id on insert
                if (stripos($c['Type'],'timestamp') !== false && strpos($c['Default'] ?? '', 'CURRENT_TIMESTAMP') !== false) continue;

                // prefer uploaded file if present
                $fileField = $f . '_file';
                $val = null;
                if (!empty($_FILES[$fileField]) && !empty($_FILES[$fileField]['tmp_name'])) {
                  $finfo = $_FILES[$fileField];
                  if ($finfo['error'] === UPLOAD_ERR_OK) {
                    $colType = $c['Type'] ?? '';
                    $tmpPath = $finfo['tmp_name'];
                    // Intentar comprimir a memoria (si GD disponible)
                    $bin = @compress_image_to_string($tmpPath, 80, 1200);
                    if ($bin === false) {
                      // fallback: leer el fichero tal cual
                      $bin = @file_get_contents($tmpPath);
                    }
                    if ($bin === false) {
                      $error = 'No se pudo leer el archivo subido para ' . e($f) . '.';
                    } else {
                      $mime = $finfo['type'] ?? (@function_exists('mime_content_type') ? mime_content_type($tmpPath) : 'application/octet-stream');
                      $b64 = base64_encode($bin);
                      $dataUrl = 'data:' . $mime . ';base64,' . $b64;
                      if (!empty($FORCE_DB_IMAGE_STORAGE)) {
                        // Forzar almacenamiento en BD: validar tamaño razonable
                        if (strlen($dataUrl) > $DB_IMAGE_MAX_BYTES) {
                          $error = 'Imagen demasiado grande para almacenar en BD (> ' . round($DB_IMAGE_MAX_BYTES/1024/1024,2) . 'MB). Reduce su tamaño.';
                        } else {
                          $val = $dataUrl;
                        }
                      } else {
                        // Comportamiento anterior: intentar guardar en disco
                        $ext = pathinfo($finfo['name'], PATHINFO_EXTENSION);
                        $safe = bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-zA-Z0-9\.-]/', '', $ext);
                        $imagesDir = __DIR__ . '/../scr/resources/images';
                        $uploadDir = $imagesDir . '/uploads';
                        if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
                        if (is_dir($uploadDir) && is_writable($uploadDir)) {
                          $dest = $uploadDir . '/' . $safe;
                          if (@move_uploaded_file($finfo['tmp_name'], $dest)) {
                            @compress_image_if_possible($dest, 80, 1200);
                            $val = '/casabonsai/scr/resources/images/uploads/' . $safe;
                          } else {
                            // si no se pudo mover, como fallback intentar dejar data URL (si cabe)
                            if (strlen($dataUrl) <= $DB_IMAGE_MAX_BYTES) {
                              $val = $dataUrl;
                            } else {
                              $error = 'No se pudo mover el archivo subido para ' . e($f) . '. Revisa permisos.';
                            }
                          }
                        } else {
                          // uploads no escribible -> fallback a BD si posible
                          if (strlen($dataUrl) <= $DB_IMAGE_MAX_BYTES) {
                            $val = $dataUrl;
                          } else {
                            $error = 'Directorio de uploads no escribible para ' . e($f) . ' y la imagen es demasiado grande para BD.';
                          }
                        }
                      }
                    }
                  } else {
                    $error = 'Error al subir archivo para ' . e($f) . ' (code ' . intval($finfo['error']) . ').';
                  }
                } else {
                  if (isset($_POST[$f])) $val = $_POST[$f];
                }

                // auto-hash password fields
                if (in_array(strtolower($f), ['contraseña','contrasena','password'])) {
                  if ($val !== '') $val = password_hash($val, PASSWORD_DEFAULT);
                  else $val = null;
                }

                // Convert empty strings to NULL for numeric/FK fields to avoid foreign key violations
                if ($val === '') {
                  if (preg_match('/int|bigint|tinyint/i', $c['Type']) || in_array(strtolower($f), ['creado_por','id_usuario'])) {
                    $val = null;
                  }
                }

                $fields[] = "`$f`";
                $placeholders[] = '?';
                $values[] = $val;
            }

            // If necessary, set default admin id for FK fields like creado_por or id_usuario
            $defaultAdminId = null;
            if (!empty($_SESSION['site_user']['id'])) {
              $defaultAdminId = (int)$_SESSION['site_user']['id'];
            } else {
              try {
                $chk = $pdo->prepare('SELECT id_usuario FROM usuario WHERE id_usuario IN (1,2) ORDER BY id_usuario LIMIT 1');
                $chk->execute();
                $r = $chk->fetch(PDO::FETCH_ASSOC);
                if ($r && !empty($r['id_usuario'])) $defaultAdminId = (int)$r['id_usuario'];
              } catch (Exception $ex) { /* ignore */ }
            }
            if ($defaultAdminId !== null) {
              foreach ($fields as $i => $colname) {
                $clean = trim($colname, "` ");
                if (in_array($clean, ['creado_por','id_usuario'])) {
                  if (!isset($values[$i]) || $values[$i] === '' || $values[$i] === null) {
                    $values[$i] = $defaultAdminId;
                  }
                }
              }
            }

            if ($isUpdate) {
                $sets = [];
                foreach($fields as $i => $colname) $sets[] = $colname . ' = ' . $placeholders[$i];
                $sql = "UPDATE `{$table}` SET " . implode(', ', $sets) . " WHERE `{$idCol}` = ? LIMIT 1";
              $id = intval($_POST['id']);
              $values[] = $id;
              $stmt = $pdo->prepare($sql);
              // sanitize large payloads before execute: if value exceeds DB-image limit,
              // either move to disk (if allowed) or error when FORCE_DB_IMAGE_STORAGE is on.
              foreach ($fields as $i => $colname) {
                $clean = trim($colname, "` ");
                $valNow = $values[$i] ?? null;
                if (is_string($valNow) && strlen($valNow) > $DB_IMAGE_MAX_BYTES) {
                  if (strpos($valNow, 'data:') === 0) {
                    if (!empty($FORCE_DB_IMAGE_STORAGE)) {
                      $error = 'Imagen demasiado grande para almacenar en BD en ' . e($clean) . ' (> ' . round($DB_IMAGE_MAX_BYTES/1024/1024,2) . 'MB).';
                      break;
                    } else {
                      $saved = save_base64_to_disk($valNow, $clean);
                      if ($saved !== false) $values[$i] = $saved;
                      else { $error = 'No se pudo guardar archivo grande en disco para ' . e($clean); break; }
                    }
                  } else {
                    $error = 'Valor demasiado grande para enviar a la base de datos en ' . e($clean);
                    break;
                  }
                }
              }
              if (empty($error)) {
                try {
                  $stmt->execute($values);
                  $message = 'Registro actualizado.';
                } catch (PDOException $ex) {
                  // if server gone away, try reconnect once and re-run
                  if (stripos($ex->getMessage(), 'server has gone away') !== false || strpos($ex->getMessage(), 'MySQL server has gone away') !== false || intval($ex->getCode()) === 2006) {
                    try {
                      unset($pdo);
                      require __DIR__ . '/../conn.php';
                      $stmt = $pdo->prepare($sql);
                      $stmt->execute($values);
                      $message = 'Registro actualizado.';
                    } catch (Exception $ex2) {
                      $error = 'Error al actualizar registro (reintento fallido): ' . $ex2->getMessage();
                    }
                  } else {
                    $error = 'Error al actualizar registro: ' . $ex->getMessage();
                    admin_debug_log('PDOException update', ['msg'=>$ex->getMessage(), 'table'=>$table, 'sql'=>$sql ?? null, 'values'=>array_slice($values,0,30)]);
                  }
                }
              }
            } else {
                if (count($fields)) {
                    $sql = "INSERT INTO `{$table}` (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    $stmt = $pdo->prepare($sql);
                    // same sanitization for inserts: if value exceeds DB-image limit,
                    // either move to disk (if allowed) or error when FORCE_DB_IMAGE_STORAGE is on.
                    foreach ($fields as $i => $colname) {
                      $clean = trim($colname, "` ");
                      $valNow = $values[$i] ?? null;
                      if (is_string($valNow) && strlen($valNow) > $DB_IMAGE_MAX_BYTES) {
                        if (strpos($valNow, 'data:') === 0) {
                          if (!empty($FORCE_DB_IMAGE_STORAGE)) {
                            $error = 'Imagen demasiado grande para almacenar en BD en ' . e($clean) . ' (> ' . round($DB_IMAGE_MAX_BYTES/1024/1024,2) . 'MB).';
                            break;
                          } else {
                            $saved = save_base64_to_disk($valNow, $clean);
                            if ($saved !== false) $values[$i] = $saved;
                            else { $error = 'No se pudo guardar archivo grande en disco para ' . e($clean); break; }
                          }
                        } else {
                          $error = 'Valor demasiado grande para enviar a la base de datos en ' . e($clean);
                          break;
                        }
                      }
                    }
                    if (empty($error)) {
                      try {
                        $stmt->execute($values);
                        $newId = $pdo->lastInsertId();
                        $message = 'Registro creado.';
                      } catch (PDOException $ex) {
                        if (stripos($ex->getMessage(), 'server has gone away') !== false || strpos($ex->getMessage(), 'MySQL server has gone away') !== false || intval($ex->getCode()) === 2006) {
                          try {
                            unset($pdo);
                            require __DIR__ . '/../conn.php';
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($values);
                            $newId = $pdo->lastInsertId();
                            $message = 'Registro creado.';
                          } catch (Exception $ex2) {
                            $error = 'Error al insertar registro (reintento fallido): ' . $ex2->getMessage();
                          }
                        } else {
                            $error = 'Error al insertar registro: ' . $ex->getMessage();
                            admin_debug_log('PDOException insert', ['msg'=>$ex->getMessage(), 'table'=>$table, 'sql'=>$sql ?? null, 'values'=>array_slice($values,0,30)]);
                        }
                      }
                    }
                } else {
                    $error = 'No hay campos enviados para insertar.';
                }
            }
        }
    }
}

// For edit, load row
$editRow = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $s = $pdo->prepare("SELECT * FROM `{$table}` WHERE `{$idCol}` = ? LIMIT 1");
    $s->execute([$id]);
    $editRow = $s->fetch(PDO::FETCH_ASSOC);
}

// Pagination (no búsqueda): siempre listar sin filtro
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// No search: mostrar todos los registros
$where = '';
$params = [];

// total count
$cntStmt = $pdo->prepare("SELECT COUNT(*) as c FROM `{$table}` {$where}");
$cntStmt->execute($params);
$total = (int)$cntStmt->fetchColumn();

// List rows with limit
$sqlList = "SELECT * FROM `{$table}` {$where} ORDER BY `{$idCol}` DESC LIMIT {$perPage} OFFSET {$offset}";
$listStmt = $pdo->prepare($sqlList);
$listStmt->execute($params);
$list = $listStmt->fetchAll(PDO::FETCH_ASSOC);

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

?>
  <div class="dash">
    <div class="card bg-white shadow-sm">
      <div class="toolbar mb-3 d-flex align-items-center justify-content-between">
        <div class="left d-flex align-items-center">
          <?php
<<<<<<< HEAD
            // usar rutas relativas para evitar problemas con el DocumentRoot o subcarpetas
            $backUrl = 'index.php';
            if (!empty($table)) {
              $backUrl = 'manage.php?table=' . urlencode($table);
=======
            $backUrl = '/../admin/index.php';
            if (!empty(
                $table
            )) {
              $backUrl = '/../admin/manage.php?table=' . urlencode($table);
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
            }
          ?>
          <a href="<?php echo e($backUrl); ?>" class="btn btn-outline-secondary btn-sm me-3">Volver</a>
          <h2 class="mb-0">Gestionar <?php echo e($table); ?></h2>
          <div class="muted-small ms-3">Mostrando <?php echo count($list); ?> de <?php echo $total; ?> registros</div>
        </div>
        <div class="right d-flex align-items-center">
          <button id="bulkDeleteBtn" class="btn btn-danger btn-sm">Borrar</button>
        </div>
      </div>

      <?php if(!empty($error)): ?><div class="muted-small" style="color:#a33;margin-top:8px"><?php echo e($error); ?></div><?php endif; ?>
      <?php if(!empty($message)): ?><div class="muted-small" style="color:#2a7;margin-top:8px"><?php echo e($message); ?></div><?php endif; ?>

  <!-- Formulario de creación/edición: mostrado arriba de la lista -->
  <div id="createPanel" class="card p-3 mb-3" style="display:block;">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h3 class="h5 mb-0"><i class="bi bi-pencil-square me-2"></i><?php echo $editRow ? 'Editar ' . e($table) : 'Crear nuevo ' . e($table); ?></h3>
      <?php if ($editRow): ?>
        <div class="text-muted small">ID: <?php echo e($editRow[$idCol]); ?></div>
      <?php endif; ?>
    </div>
    <form method="post" id="mainForm" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>" />
      <?php if($editRow): ?><input type="hidden" name="id" id="rowId" value="<?php echo e($editRow[$idCol]); ?>" /><?php endif; ?>
      <?php
        // etiquetas amigables
        $labelMap = [
          'nombre' => 'Nombre', 'descripcion' => 'Descripción', 'precio' => 'Precio', 'stock' => 'Stock',
          'sku' => 'SKU', 'imagen' => 'Imagen', 'img' => 'Imagen', 'foto' => 'Imagen', 'activo' => 'Activo'
        ];
        // si es producto, layout con preview grande
        if ($isProductTable):
          $imageField = null;
          foreach ($colsOrdered as $c) { if (preg_match('/imagen|img|foto|image|picture/i', $c['Field'])) { $imageField = $c['Field']; break; } }
      ?>
        <div class="row">
          <div class="col-lg-4">
            <div class="card text-center p-3">
              <div id="imgPreviewWrap" style="min-height:260px;display:flex;align-items:center;justify-content:center">
                <?php if ($editRow && $imageField):
                  $val = $editRow[$imageField] ?? '';
                  if (is_string($val) && preg_match('#^data:image#', $val) || (is_string($val) && strpos($val,'/casabonsai/scr/resources/images/uploads/')!==false)):
                ?>
                  <img id="imgPreview" src="<?php echo e($val); ?>" class="img-fluid" style="max-height:320px;object-fit:contain;border-radius:6px" />
                <?php else: ?>
                  <img id="imgPreview" src="/casabonsai/scr/resources/images/uploads/placeholder.png" class="img-fluid" style="max-height:320px;object-fit:contain;border-radius:6px;opacity:.4" />
                <?php endif; else: ?>
                  <img id="imgPreview" src="/casabonsai/scr/resources/images/uploads/placeholder.png" class="img-fluid" style="max-height:320px;object-fit:contain;border-radius:6px;opacity:.4" />
                <?php endif; ?>
              </div>
              <div class="mt-3">
                <label class="btn btn-outline-primary btn-sm mb-0">
                  <input type="file" id="fileInputMain" name="<?php echo e($imageField ?? 'imagen'); ?>_file" accept="image/*" style="display:none" />
                  Cambiar imagen
                </label>
              </div>
              <div class="mt-2 text-muted small">Formatos: JPG, PNG. Máx recomendado 3MB.</div>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="row">
              <?php foreach($colsOrdered as $c):
                $f = $c['Field'];
                if ($f === $idCol && !$editRow) continue;
                if ($imageField && $f === $imageField) continue;
                if (stripos($c['Type'],'timestamp') !== false && strpos($c['Default'] ?? '', 'CURRENT_TIMESTAMP') !== false) continue;
                $val = $editRow[$f] ?? '';
                $label = $labelMap[strtolower($f)] ?? ucwords(str_replace('_',' ',$f));
              ?>
                <div class="col-md-6 mb-3">
                  <label class="form-label small"><strong><?php echo e($label); ?></strong></label>
                  <?php if (in_array(strtolower($f), ['contraseña','contrasena','password'])): ?>
                    <input type="password" name="<?php echo e($f); ?>" class="form-control" value="" placeholder="Dejar vacío para no cambiar" />
                  <?php else: ?>
                    <?php if (preg_match('/char|varchar|text/i', $c['Type'])): ?>
                      <textarea name="<?php echo e($f); ?>" class="form-control" rows="3"><?php echo e($val); ?></textarea>
                    <?php elseif (preg_match('/int|decimal|float|double/i', $c['Type'])): ?>
                      <input type="text" name="<?php echo e($f); ?>" class="form-control" value="<?php echo e($val); ?>" />
                    <?php else: ?>
                      <input name="<?php echo e($f); ?>" id="field_<?php echo e($f); ?>" value="<?php echo e($val); ?>" class="form-control" />
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-success" type="submit"><?php echo $editRow ? 'Guardar cambios' : 'Crear producto'; ?></button>
        </div>
      <?php else: ?>
        <!-- fallback genérico (no producto) -->
        <?php if ($tableKey === 'residente'): ?>
          <?php
            // prepare usuarios for 'creado_por' select
            $users = [];
            try {
              $uSt = $pdo->query('SELECT id_usuario, Usuario FROM usuario ORDER BY Usuario');
              $users = $uSt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $ex) { /* ignore if table missing */ }

            // detect image field for residente (foto)
            $imageField = null;
            foreach ($cols as $c) {
              if (preg_match('/foto|imagen|img|picture/i', $c['Field'])) { $imageField = $c['Field']; break; }
            }
          ?>
          <div class="row">
            <div class="col-lg-4">
              <div class="card text-center p-3">
                <div id="imgPreviewWrapRes" style="min-height:200px;display:flex;align-items:center;justify-content:center">
                  <?php if ($editRow && $imageField):
                    $valImg = $editRow[$imageField] ?? '';
                    if (is_string($valImg) && (preg_match('#^data:image#', $valImg) || strpos($valImg,'/casabonsai/scr/resources/images/')!==false)):
                  ?>
                    <img id="imgPreviewRes" src="<?php echo e($valImg); ?>" class="img-fluid" style="max-height:240px;object-fit:contain;border-radius:6px" />
                  <?php else: ?>
                    <div id="imgPreviewRes" style="max-height:240px;width:100%;"></div>
                  <?php endif; else: ?>
                    <div id="imgPreviewRes" style="max-height:240px;width:100%;"></div>
                  <?php endif; ?>
                </div>
                <div class="mt-3">
                  <label class="btn btn-outline-primary btn-sm mb-0">
                    <input type="file" id="fileInputRes" name="<?php echo e($imageField ?? 'foto'); ?>_file" accept="image/*" style="display:none" />
                    Subir foto
                  </label>
                </div>
                <div class="mt-2 text-muted small">Formatos: JPG, PNG. Máx recomendado 3MB.</div>
              </div>
            </div>
            <div class="col-lg-8">
              <div class="row">
                <?php foreach($colsOrdered as $c):
                  $f = $c['Field'];
                  if ($f === $idCol && !$editRow) continue;
                  if ($imageField && $f === $imageField) continue;
                  if (stripos($c['Type'],'timestamp') !== false && strpos($c['Default'] ?? '', 'CURRENT_TIMESTAMP') !== false) continue;
                  $val = $editRow[$f] ?? '';
                  $label = ucwords(str_replace('_',' ',$f));
                ?>
                  <div class="col-md-6 mb-3">
                    <label class="form-label small"><?php echo e($label); ?></label>
                    <?php if ($f === 'fecha_nacimiento'): ?>
                      <input type="date" name="<?php echo e($f); ?>" class="form-control" value="<?php echo e($val); ?>" />
                    <?php elseif ($f === 'genero'): ?>
                      <div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="genero" id="gen_m" value="Masculino" <?php echo ($val === 'Masculino') ? 'checked' : ''; ?> />
                          <label class="form-check-label" for="gen_m">Masculino</label>
                        </div>
                        <div class="form-check form-check-inline">
                          <input class="form-check-input" type="radio" name="genero" id="gen_f" value="Femenino" <?php echo ($val === 'Femenino') ? 'checked' : ''; ?> />
                          <label class="form-check-label" for="gen_f">Femenino</label>
                        </div>
                      </div>
                    <?php elseif ($f === 'creado_por'): ?>
                      <select name="creado_por" class="form-select">
                        <option value="">-- Seleccionar usuario --</option>
                        <?php foreach($users as $u): ?>
                          <option value="<?php echo e($u['id_usuario']); ?>" <?php echo ($val == $u['id_usuario']) ? 'selected' : ''; ?>><?php echo e($u['Usuario']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php elseif ($f === 'id_usuario'): ?>
                      <select name="id_usuario" class="form-select">
                        <option value="">-- Seleccionar usuario --</option>
                        <?php foreach($users as $u): ?>
                          <option value="<?php echo e($u['id_usuario']); ?>" <?php echo ($val == $u['id_usuario']) ? 'selected' : ''; ?>><?php echo e($u['Usuario']); ?></option>
                        <?php endforeach; ?>
                      </select>
                    <?php else: ?>
                      <?php if (preg_match('/char|varchar|text/i', $c['Type'])): ?>
                        <textarea name="<?php echo e($f); ?>" class="form-control" rows="3"><?php echo e($val); ?></textarea>
                      <?php else: ?>
                        <input name="<?php echo e($f); ?>" class="form-control" value="<?php echo e($val); ?>" />
                      <?php endif; ?>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <div class="mt-2">
            <button class="btn btn-primary" type="submit"><?php echo $editRow ? 'Actualizar' : 'Crear'; ?></button>
          </div>
        <?php else: ?>
          <div class="row">
<<<<<<< HEAD
            <?php
              // preparar lista de productos y proveedores para selects si existen
              $products = [];
              $providers = [];
              try {
                $pSt = $pdo->query('SELECT id_producto, nombre FROM producto ORDER BY nombre');
                $products = $pSt->fetchAll(PDO::FETCH_ASSOC);
              } catch (Exception $ex) { /* tabla producto puede no existir */ }
              try {
                $prSt = $pdo->query('SELECT id_proveedor, nombre FROM proveedores ORDER BY nombre');
                $providers = $prSt->fetchAll(PDO::FETCH_ASSOC);
              } catch (Exception $ex) { /* tabla proveedores puede no existir */ }

              foreach($colsOrdered as $c):
=======
            <?php foreach($colsOrdered as $c):
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
              $f = $c['Field'];
              if ($f === $idCol && !$editRow) continue;
              if (stripos($c['Type'],'timestamp') !== false && strpos($c['Default'] ?? '', 'CURRENT_TIMESTAMP') !== false) continue;
              $val = $editRow[$f] ?? '';
              $label = ucwords(str_replace('_',' ',$f));
            ?>
              <div class="col-md-6 mb-3">
                <label class="form-label small"><?php echo e($label); ?></label>
<<<<<<< HEAD
                <?php
                  // Renderizar selects para claves foráneas conocidas
                  if (in_array($f, ['id_producto','producto_id'])): ?>
                    <select name="<?php echo e($f); ?>" class="form-select">
                      <option value="">-- Seleccionar producto --</option>
                      <?php foreach($products as $p): ?>
                        <option value="<?php echo e($p['id_producto']); ?>" <?php echo ($val == $p['id_producto']) ? 'selected' : ''; ?>><?php echo e($p['nombre']); ?></option>
                      <?php endforeach; ?>
                    </select>
                <?php elseif (in_array($f, ['id_proveedor','proveedor_id'])): ?>
                    <select name="<?php echo e($f); ?>" class="form-select">
                      <option value="">-- Seleccionar proveedor --</option>
                      <?php foreach($providers as $pr): ?>
                        <option value="<?php echo e($pr['id_proveedor']); ?>" <?php echo ($val == $pr['id_proveedor']) ? 'selected' : ''; ?>><?php echo e($pr['nombre']); ?></option>
                      <?php endforeach; ?>
                    </select>
                <?php elseif (preg_match('/char|varchar|text/i', $c['Type'])): ?>
=======
                <?php if (preg_match('/char|varchar|text/i', $c['Type'])): ?>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
                  <textarea name="<?php echo e($f); ?>" class="form-control" rows="3"><?php echo e($val); ?></textarea>
                <?php else: ?>
                  <input name="<?php echo e($f); ?>" class="form-control" value="<?php echo e($val); ?>" />
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="mt-2">
            <button class="btn btn-primary" type="submit"><?php echo $editRow ? 'Actualizar' : 'Crear'; ?></button>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </form>
  </div>

  <div class="admin-two-col">
    <div class="admin-right">
      <?php if (!$editRow): ?>
      <form method="post" id="bulkForm">
        <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>" />
        <input type="hidden" name="action" value="bulk_delete" />
        <?php if(count($list)): ?>
          <table class="manage table table-striped table-hover">
            <thead>
              <tr>
                <th><input type="checkbox" id="selectAll" /></th>
                <?php foreach(array_keys($list[0]) as $h) echo '<th>' . e($h) . '</th>'; ?>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach($list as $row): ?>
              <tr>
                <td><input type="checkbox" name="ids[]" value="<?php echo e($row[$idCol]); ?>" class="rowChk" /></td>
                <?php foreach(array_keys($row) as $field):
                    $v = $row[$field];
                    $lower = strtolower($field);
                    $isImg = false;
                    if (is_string($v)) {
                      if (preg_match('#^data:image#', $v)) $isImg = true;
                      if (strpos($v, '/casabonsai/scr/resources/images/uploads/') !== false) $isImg = true;
                      if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $v)) $isImg = true;
                    }
                    echo '<td' . ($isImg ? ' class="td-truncate"' : '') . '>';
                    if ($isImg) {
                      $src = $v;
                      echo '<img src="' . e($src) . '" class="thumb" />';
                    } else {
                      // truncate long text for readability
                      $out = e($v);
                      if (strlen($out) > 120) $out = substr($out,0,117) . '...';
                      echo '<div class="td-truncate" title="' . e($v) . '">' . $out . '</div>';
                    }
                    echo '</td>';
                  endforeach; ?>
                <td class="actions">
                  <a href="?table=<?php echo urlencode($table); ?>&edit=<?php echo e($row[$idCol]); ?>" class="btn action-btn-edit btn-sm">Edit</a>
                  <form method="post" style="display:inline" onsubmit="return confirm('Eliminar registro?')">
                    <input type="hidden" name="csrf" value="<?php echo e($csrf); ?>" />
                    <input type="hidden" name="action" value="delete" />
                    <input type="hidden" name="id" value="<?php echo e($row[$idCol]); ?>" />
                    <button type="submit" class="btn action-btn-delete btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?><p class="muted-small">No hay registros.</p><?php endif; ?>
      </form>

      <!-- pagination -->
      <?php
        $pages = max(1, ceil($total / $perPage));
        if ($pages > 1) {
          echo '<div style="margin-top:12px">';
          for($p=1;$p<=$pages;$p++){
            $cls = $p==$page? 'style="font-weight:bold;margin-right:6px"' : 'style="margin-right:6px"';
            $url = '?table=' . urlencode($table) . '&page=' . $p;
            echo '<a href="' . $url . '" ' . $cls . '>' . $p . '</a>';
          }
          echo '</div>';
        }
      ?>

    </div>
  </div>
  <?php else: ?>
    </div>
  </div>
  <?php endif; ?>

  <script>
    // toggle create panel (guarded)
    (function(){
      var showCreate = document.getElementById('showCreate');
      var overlay = document.getElementById('createOverlay');
      var cp = document.getElementById('createPanel');

      function openCreate() {
        var wrap = document.querySelector('.admin-two-col');
        if (wrap) wrap.classList.add('has-create');
        if (cp) { cp.classList.add('floating','open'); cp.style.display = 'block'; }
        if (overlay) overlay.style.display = 'block';
        // clear form fields
        document.querySelectorAll('#createPanel input[type=text], #createPanel input[type=password], #createPanel textarea').forEach(function(i){ i.value = ''; });
        var rid = document.getElementById('rowId'); if (rid) rid.remove();
      }

      function closeCreate() {
        var wrap = document.querySelector('.admin-two-col');
        if (wrap) wrap.classList.remove('has-create');
        if (cp) { cp.classList.remove('open'); cp.style.display = 'none'; }
        if (overlay) overlay.style.display = 'none';
      }

      // if server rendered form is open (edit state), make layout expand
      if (cp && cp.classList.contains('open')) {
        var wrapInit = document.querySelector('.admin-two-col'); if (wrapInit) wrapInit.classList.add('has-create');
      }

      if (showCreate) showCreate.addEventListener('click', openCreate);
      var cancelCreate = document.getElementById('cancelCreate');
      if (cancelCreate) cancelCreate.addEventListener('click', function(e){ if (e) e.preventDefault(); closeCreate(); });

      if (overlay) overlay.addEventListener('click', closeCreate);

      // select all (guarded)
      var selectAll = document.getElementById('selectAll');
      if (selectAll) selectAll.addEventListener('change', function(){
        var checked = this.checked;
        document.querySelectorAll('.rowChk').forEach(function(cb){ cb.checked = checked; });
      });

      // bulk delete (guarded)
      var bulkBtn = document.getElementById('bulkDeleteBtn');
      if (bulkBtn) bulkBtn.addEventListener('click', function(){ if (!confirm('Eliminar los registros seleccionados?')) return; var bf = document.getElementById('bulkForm'); if (bf) bf.submit(); });
    })();

    // edit link: fetch row and populate form
    document.querySelectorAll('.editLink').forEach(function(a){
      a.addEventListener('click', function(e){
        e.preventDefault();
        var id = this.getAttribute('data-id');
        // redirect to same page with edit param to load server-side editRow OR fetch via AJAX by reloading with ?edit
        window.location.href = '?table=<?php echo urlencode($table); ?>&edit=' + encodeURIComponent(id);
      });
    });

    // image preview for main file input
    (function(){
      var fileIn = document.getElementById('fileInputMain');
      if (!fileIn) return;
      fileIn.addEventListener('change', function(e){
        var f = this.files && this.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function(ev){
          var img = document.getElementById('imgPreview');
          if (img) img.src = ev.target.result;
        };
        reader.readAsDataURL(f);
      }, false);
    })();

    // image preview for residente file input
    (function(){
      var fileRes = document.getElementById('fileInputRes');
      if (!fileRes) return;
      fileRes.addEventListener('change', function(e){
        var f = this.files && this.files[0];
        if (!f) return;
        var reader = new FileReader();
        reader.onload = function(ev){
          var container = document.getElementById('imgPreviewRes');
          if (!container) return;
          container.innerHTML = '<img src="' + ev.target.result + '" class="img-fluid" style="max-height:240px;object-fit:contain;border-radius:6px" />';
        };
        reader.readAsDataURL(f);
      }, false);
    })();
  </script>

<div id="createOverlay"></div>
<?php echo '</div>'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>
<?php echo '</body></html>'; ?>