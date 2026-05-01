<?php
// Redirige al gestor genérico del admin para la tabla 'proveedores'
$redirect = dirname($_SERVER['SCRIPT_NAME']) . '/../admin/manage.php?table=proveedores';
header('Location: ' . $redirect);
exit;
