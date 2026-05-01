<?php
// Script de migración: ejecuta el SQL en create_proveedores_inventario.sql
// Uso (PowerShell):
// php "c:\xampp\htdocs\CasaBonsai-ProyectoPaginaWeb\db\migrate_proveedores_inventario.php"

// Cargar la conexión (ajusta la ruta si tu `conn.php` está en otra ubicación)
require_once __DIR__ . '/../conn.php';

$sqlFile = __DIR__ . '/create_proveedores_inventario.sql';
if (!file_exists($sqlFile)) {
    echo "Archivo SQL no encontrado: {$sqlFile}\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
if ($sql === false) {
    echo "No se pudo leer el archivo SQL.\n";
    exit(1);
}

try {
    // Si $pdo no existe, intentar obtenerlo desde conn.php
    if (!isset($pdo)) {
        echo "Variable \$pdo no encontrada. Asegúrate de que \`conn.php\` define \$pdo (PDO).\n";
        exit(1);
    }

    // Ejecutar dentro de una transacción para mayor seguridad
    $pdo->beginTransaction();
    $pdo->exec($sql);
    $pdo->commit();

    echo "Migración completada: tablas 'proveedores' e 'inventario_movimientos' creadas (si no existían).\n";
    exit(0);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) { $pdo->rollBack(); }
    echo "Error al ejecutar migración: " . $e->getMessage() . "\n";
    exit(1);
}
