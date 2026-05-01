<?php
require_once __DIR__ . '/../conn.php';
$table = 'usuarios';
$redirect = dirname($_SERVER['SCRIPT_NAME']) . '/../admin/manage.php?table=' . urlencode($table);
header('Location: ' . $redirect);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>CRUD - Usuarios</title>
<<<<<<< HEAD
    <link rel="stylesheet" href="../scr/styles/styles.css">
=======
    <link rel="stylesheet" href="/casabonsai/scr/styles/styles.css">
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
    <style>
        .container{max-width:980px;margin:28px auto;padding:0 16px}
        .card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.06);}
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container-fluid">
<<<<<<< HEAD
            <a class="navbar-brand" href="../admin/index.php">Casa Bonsái - Admin</a>
=======
            <a class="navbar-brand" href="/casabonsai/admin/index.php">Casa Bonsái - Admin</a>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
            <div class="navbar-nav ml-auto gap-8">
                <a class="nav-link" href="/casabonsai/">Volver al sitio</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h1>Administrar Usuarios</h1>
            <p>Esta es una página CRUD mínima para la tabla <strong><?php echo $table; ?></strong>.</p>
            <p>Si quieres la interfaz completa, puedo implementar las operaciones (listar/crear/editar/borrar) con CSRF y formularios POST.</p>
<<<<<<< HEAD
            <p>Ir al gestor genérico: <a href="../admin/manage.php?table=<?php echo $table; ?>">Administrar desde Manage</a></p>
=======
            <p>Ir al gestor genérico: <a href="/casabonsai/admin/manage.php?table=<?php echo $table; ?>">Administrar desde Manage</a></p>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
        </div>
    </div>
</body>
</html>
    
