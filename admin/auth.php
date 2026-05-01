    <?php
    // Autenticación simple para admin pages
    require __DIR__ . '/../conn.php';
    // Asegurar que la cookie de sesión cubra la base de la aplicación
    session_set_cookie_params([ 'path' => '/', 'httponly' => true, 'samesite' => 'Lax' ]);
    session_start();

    function is_logged_in(){
        return !empty($_SESSION['user_id']);
    }

    function require_login(){
        if (!is_logged_in()){
            header('Location: login.php');
            exit;
        }
    }

    function attempt_login($username, $password){
        global $pdo;
        // Seleccionamos sólo columnas que sabemos existen para evitar errores si el esquema
        // usa `Nombres` en lugar de `Nombre` u otras variantes.
        $stmt = $pdo->prepare('SELECT u.id_usuario, u.Usuario, u.Contraseña, u.rol_id, r.nombre AS rol_name FROM usuario u LEFT JOIN rol r ON u.rol_id = r.id_rol WHERE u.Usuario = ? LIMIT 1');
        $stmt->execute([$username]);
        $u = $stmt->fetch();
        if ($u && password_verify($password, $u['Contraseña'])){
            // set session
            $_SESSION['user_id'] = $u['id_usuario'];

            // Intentar obtener un nombre legible del usuario de forma segura (Nombres o Nombre)
            try {
                $colsStmt = $pdo->query("SHOW COLUMNS FROM usuario");
                $cols = $colsStmt->fetchAll(PDO::FETCH_COLUMN, 0);
                if (in_array('Nombres', $cols)) {
                    $nstmt = $pdo->prepare('SELECT `Nombres` FROM usuario WHERE id_usuario = ?');
                    $nstmt->execute([$u['id_usuario']]);
                    $nrow = $nstmt->fetch();
                    $_SESSION['user_name'] = $nrow ? $nrow['Nombres'] : $u['Usuario'];
                } elseif (in_array('Nombre', $cols)) {
                    $nstmt = $pdo->prepare('SELECT `Nombre` FROM usuario WHERE id_usuario = ?');
                    $nstmt->execute([$u['id_usuario']]);
                    $nrow = $nstmt->fetch();
                    $_SESSION['user_name'] = $nrow ? $nrow['Nombre'] : $u['Usuario'];
                } else {
                    $_SESSION['user_name'] = $u['Usuario'];
                }
            } catch (Exception $e) {
                // si algo falla, usar el username como fallback
                $_SESSION['user_name'] = $u['Usuario'];
            }

            $_SESSION['role_id'] = $u['rol_id'] ?? null;
            $_SESSION['role_name'] = $u['rol_name'] ?? null;
            return true;
        }
        return false;
    }

    function is_admin(){
        // role_name 'admin' identifica al admin; también aceptamos role_id 1 como fallback si se desea
        if (!is_logged_in()) return false;
        if (!empty($_SESSION['role_name']) && strtolower($_SESSION['role_name']) === 'admin') return true;
        // Si no hay role_name, comprobar la tabla pivot usuario_rol por si el usuario tiene asignado el rol 'admin'
        global $pdo;
        try{
            $stmt = $pdo->prepare("SELECT 1 FROM usuario_rol ur JOIN rol r ON ur.rol_id = r.id_rol WHERE ur.usuario_id = ? AND LOWER(r.nombre) = 'admin' LIMIT 1");
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            if ($row) return true;
        } catch (Exception $e){
            // si hay cualquier error, no consideramos admin
        }
        return false;
    }

    function require_admin(){
        require_login();
        if (!is_admin()){
            // Si el usuario está autenticado pero no es admin, redirigir al login de admin
            // con un mensaje opcional para que pueda iniciar sesión con otra cuenta.
            $sep = (strpos($_SERVER['REQUEST_URI'], '?') !== false) ? '&' : '?';
            $back = urlencode($_SERVER['REQUEST_URI']);
            header('Location: login.php?r=' . $back);
            exit;
        }
    }

    function logout(){
        session_unset();
        session_destroy();
    }
    ?>