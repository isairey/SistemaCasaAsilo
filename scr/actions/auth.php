<?php
// Asegurar que la cookie de sesión sea válida para la base de la app
// (ajusta la ruta a la carpeta donde sirves la app)
session_set_cookie_params([ 'path' => '/', 'httponly' => true, 'samesite' => 'Lax' ]);
session_start();
require __DIR__ . '/../../conn.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    if ($action === 'register') {
        // Campos esperados desde el formulario
        $name = trim($_POST['name'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $telefono = trim($_POST['phone'] ?? '');
        $correo = trim($_POST['email'] ?? '');
        $direccion = trim($_POST['address'] ?? '');
        $user = trim($_POST['user'] ?? '');
        $pass = $_POST['pass'] ?? '';

        // Requerir al menos nombre, apellidos, usuario y contraseña
        if ($name === '' || $apellidos === '' || $user === '' || $pass === '') throw new Exception('Faltan campos requeridos');
        $hash = password_hash($pass, PASSWORD_DEFAULT);

        // Verificar unicidad de Usuario
        $chk = $pdo->prepare('SELECT 1 FROM usuario WHERE Usuario = ? LIMIT 1');
        $chk->execute([$user]);
        if ($chk->fetch()) throw new Exception('El nombre de usuario ya existe');

        // Obtener columnas existentes en la tabla `usuario` para insertar sólo las columnas disponibles
        $colsStmt = $pdo->query("SHOW COLUMNS FROM usuario");
        $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Determinar rol de cliente si la tabla de roles existe
        $clientRoleId = null;
        try {
            $r = $pdo->query("SHOW TABLES LIKE 'rol'")->fetch();
            if ($r) {
                $q = $pdo->prepare('SELECT id_rol FROM rol WHERE LOWER(nombre) = ? LIMIT 1');
                $q->execute(['cliente']);
                $row = $q->fetch();
                if ($row && !empty($row['id_rol'])) {
                    $clientRoleId = (int)$row['id_rol'];
                } else {
                    // crear rol 'cliente' automáticamente
                    $ins = $pdo->prepare('INSERT INTO rol (nombre, descripcion) VALUES (?,?)');
                    $ins->execute(['cliente','Rol cliente (creado automáticamente)']);
                    $clientRoleId = (int)$pdo->lastInsertId();
                }
            }
        } catch (Exception $e) {
            // ignorar si no existe la tabla de roles o falla la creación; no crítico
            $clientRoleId = null;
        }

        // Mapa preferido de columnas -> valor
        $candidates = [
            'Nombres' => $name,
            'Apellidos' => $apellidos,
            'Telefono' => $telefono,
            'Correo' => $correo,
            'Direccion' => $direccion,
            'rol_id' => $clientRoleId,
            'Usuario' => $user,
            'Contraseña' => $hash,
        ];

        $use = [];
        $vals = [];
        foreach ($candidates as $col => $val) {
            if (in_array($col, $cols, true)) {
                $use[] = $col;
                $vals[] = $val === '' ? null : $val;
            }
        }

        if (empty($use)) throw new Exception('La tabla de usuarios no tiene columnas esperadas');

        $placeholders = implode(',', array_fill(0, count($use), '?'));
        $sql = 'INSERT INTO usuario (' . implode(',', $use) . ') VALUES (' . $placeholders . ')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);

        echo json_encode(['ok' => true, 'msg' => 'Registro correcto']);
        exit;
    }

    if ($action === 'login') {
        $user = trim($_POST['user'] ?? '');
        $pass = $_POST['pass'] ?? '';
        if ($user === '' || $pass === '') throw new Exception('Credenciales incompletas');
        $stmt = $pdo->prepare('SELECT * FROM usuario WHERE Usuario = ? LIMIT 1');
        $stmt->execute([$user]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($pass, $row['Contraseña'] ?? $row['Contraseña'])) throw new Exception('Usuario o contraseña incorrectos');
        // Guardar sesión mínima
        $_SESSION['site_user'] = ['id'=> $row['id_usuario'] ?? null, 'user'=> $row['Usuario'] ?? null];
        // Respuesta de login (sin redirecciones automáticas)
        echo json_encode(['ok' => true, 'msg' => 'Login correcto', 'user' => $_SESSION['site_user']]);
        exit;
    }

    if ($action === 'logout') {
        unset($_SESSION['site_user']);
        echo json_encode(['ok'=>true]);
        exit;
    }

        if ($action === 'status') {
            // devuelve información mínima de sesión
            if (!empty($_SESSION['site_user'])) {
                echo json_encode(['ok'=>true,'user'=>$_SESSION['site_user']]);
            } else {
                echo json_encode(['ok'=>true,'user'=>null]);
            }
            exit;
        }

    echo json_encode(['ok'=>false,'error'=>'Acción no reconocida']);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
    exit;
}
    