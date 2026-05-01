<?php
require_once __DIR__ . '/../conn.php';
$table = 'producto_categoria';
<<<<<<< HEAD
$redirect = dirname($_SERVER['SCRIPT_NAME']) . '/../admin/manage.php?table=' . urlencode($table);
header('Location: ' . $redirect);
=======
header('Location: /casabonsai/admin/manage.php?table=' . urlencode($table));
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
exit;
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>CRUD - Producto Categoria</title>
<<<<<<< HEAD
	<link rel="stylesheet" href="../scr/styles/styles.css">
	<style>.container{max-width:980px;margin:28px auto;padding:0 16px}.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.06);}</style>
</head>
<body>
	<nav class="navbar"><div class="container-fluid"><a class="navbar-brand" href="../admin/index.php">Casa Bonsái - Admin</a><div class="navbar-nav ml-auto gap-8"><a class="nav-link" href="../">Volver al sitio</a></div></div></nav>
=======
	<link rel="stylesheet" href="/casabonsai/scr/styles/styles.css">
	<style>.container{max-width:980px;margin:28px auto;padding:0 16px}.card{background:#fff;padding:18px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.06);}</style>
</head>
<body>
	<nav class="navbar"><div class="container-fluid"><a class="navbar-brand" href="/casabonsai/admin/index.php">Casa Bonsái - Admin</a><div class="navbar-nav ml-auto gap-8"><a class="nav-link" href="/casabonsai/">Volver al sitio</a></div></div></nav>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
	<div class="container"><div class="card"><h1>Administrar Producto - Categoría</h1><p>Tabla: <strong><?php echo $table; ?></strong></p><p>Ir al gestor: <a href="<?php echo htmlspecialchars(
		isset($redirect) ? $redirect : (dirname($_SERVER['SCRIPT_NAME']) . '/../admin/manage.php?table=' . urlencode($table)), ENT_QUOTES, 'UTF-8'); ?>">Manage</a></p></div></div>
</body>
</html>
