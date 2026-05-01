<?php
// Redirige al gestor genérico del admin para la tabla 'inventario_movimientos'
$redirect = dirname($_SERVER['SCRIPT_NAME']) . '/../admin/manage.php?table=inventario_movimientos';
header('Location: ' . $redirect);
exit;
