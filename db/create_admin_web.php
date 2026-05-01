<?php
// Script web para crear un usuario admin desde el navegador.
// Uso: coloca este archivo en /db y abre en el navegador: http://localhost/casabonsai/db/create_admin_web.php
// IMPORTANTE: Después de usarlo, elimina este archivo por seguridad.

// Reutilizar la conexión centralizada (admin/conn.php)
require_once __DIR__ . '/../conn.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? 'Admin');

    if ($username === '' || $password === '') {
        $message = 'Usuario y contraseña son obligatorios.';
    } else {
        try {
            $pdo->beginTransaction();

            // crear rol admin si no existe
            $stmt = $pdo->prepare('INSERT IGNORE INTO rol (nombre, descripcion) VALUES (?, ?)');
            $stmt->execute(['admin', 'Administrador del sistema']);

            // obtener id_rol
            $stmt = $pdo->prepare('SELECT id_rol FROM rol WHERE nombre = ? LIMIT 1');
            $stmt->execute(['admin']);
            $r = $stmt->fetch();
            $roleId = $r ? $r['id_rol'] : null;

            // verificar si ya existe el usuario
            $stmt = $pdo->prepare('SELECT id_usuario FROM usuario WHERE Usuario = ? LIMIT 1');
            $stmt->execute([$username]);
            $existing = $stmt->fetch();
            if ($existing) {
                $message = 'El usuario ya existe. Puedes usar otra cuenta o actualizar la existente.';
                $pdo->rollBack();
            } else {
                // insertar usuario con contraseña hasheada
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Hacemos el INSERT robusto: detectamos si la tabla `usuario` tiene columna
        // `Nombres` (o `Nombre`) y construimos la consulta dinámicamente para evitar
        // errores de columna desconocida en instalaciones con esquemas distintos.
        $colsStmt = $pdo->query("SHOW COLUMNS FROM usuario");
        $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $nameCol = null;
        if (in_array('Nombres', $cols)) {
          $nameCol = 'Nombres';
        } elseif (in_array('Nombre', $cols)) {
          $nameCol = 'Nombre';
        }

        $insertCols = [];
        $placeholders = [];
        $values = [];
        if ($nameCol) {
          $insertCols[] = "`" . $nameCol . "`";
          $placeholders[] = "?";
          $values[] = $name;
        }
        $insertCols[] = '`Usuario`';
        $placeholders[] = '?';
        $values[] = $username;
        $insertCols[] = '`Contraseña`';
        $placeholders[] = '?';
        $values[] = $hash;
        $insertCols[] = '`rol_id`';
        $placeholders[] = '?';
        $values[] = $roleId;

        $sql = 'INSERT INTO usuario (' . implode(', ', $insertCols) . ') VALUES (' . implode(', ', $placeholders) . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
                $userId = $pdo->lastInsertId();

                // insertar en pivot usuario_rol
                if ($roleId) {
                    $stmt = $pdo->prepare('INSERT IGNORE INTO usuario_rol (usuario_id, rol_id) VALUES (?,?)');
                    $stmt->execute([$userId, $roleId]);
                }

                $pdo->commit();
                $message = "Usuario admin creado correctamente. Usuario: <strong>" . htmlspecialchars($username) . "</strong>.\n";
                $message .= "<a href=\"/casabonsai/admin/login.php\">Ir a login</a>.";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $message = 'Error: ' . htmlspecialchars($e->getMessage());
        }
    }
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Crear admin - Casa Bonsái</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f6f7f9;padding:24px}
    .card{max-width:520px;margin:20px auto;background:#fff;padding:18px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.06)}
    label{display:block;margin-top:10px}
    input{width:100%;padding:8px;margin-top:6px;border:1px solid #ddd;border-radius:6px}
    button{margin-top:12px;padding:10px 14px;background:#0b7a44;color:#fff;border:none;border-radius:6px}
    .muted{color:#666;font-size:14px}
  </style>
</head>
<body>
  <div class="card">
    <h2>Crear usuario admin</h2>
    <?php if($message): ?>
      <div class="muted"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="post">
      <label>Nombre (opcional)
        <input name="name" placeholder="Admin" />
      </label>
      <label>Usuario
        <input name="username" required placeholder="admin" />
      </label>
      <label>Contraseña
        <input name="password" type="password" required />
      </label>
      <div style="margin-top:12px">
        <button type="submit">Crear admin</button>
      </div>
    </form>

    <p class="muted">Nota: este script crea el rol 'admin' si no existe, el usuario y lo asigna al rol. Elimina este archivo después de usarlo por seguridad.</p>
  </div>
</body>
</html>