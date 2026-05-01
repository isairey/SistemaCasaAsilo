<?php
// Script mínimo para crear un usuario admin con rol 'admin'.
// Úsalo desde CLI: php db/create_admin.php "admin" "contraseñasegura"

// Reutilizar la conexión centralizada (admin/conn.php)
require_once __DIR__ . '/../conn.php';

if ($argc < 3) {
    echo "Uso: php create_admin.php <usuario> <password>\n";
    exit(1);
}

$usuario = $argv[1];
$password = $argv[2];

try {
    $pdo->beginTransaction();

    // Crear rol 'admin' si no existe
    $stmt = $pdo->prepare('INSERT IGNORE INTO rol (nombre, descripcion) VALUES (?, ?)');
    $stmt->execute(['admin', 'Administrador del sistema']);

    // Insertar usuario
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuario (Nombre, Usuario, Contraseña) VALUES (?, ?, ?)');
    $stmt->execute([$usuario, $usuario, $hash]);

    $userId = $pdo->lastInsertId();
    // Obtener id_rol
    $stmt = $pdo->prepare('SELECT id_rol FROM rol WHERE nombre = ?');
    $stmt->execute(['admin']);
    $rol = $stmt->fetch();

    if ($rol && $userId) {
        $stmt = $pdo->prepare('INSERT INTO usuario_rol (usuario_id, rol_id) VALUES (?, ?)');
        $stmt->execute([$userId, $rol['id_rol']]);
    }

    $pdo->commit();
    echo "Usuario admin creado (id={$userId}).\n";
} catch (Exception $e) {
    if (isset($pdo) && $pdo && $pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
