<?php
// Página simple de perfil público del usuario
require __DIR__ . '/../../conn.php';

// Asegurar cookie de sesión con mismo path que el sitio
session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();

$siteUser = $_SESSION['site_user'] ?? null;
if (!$siteUser) {
    // No autenticado -> redirigir al inicio (puedes cambiar a una página de login si lo prefieres)
    header('Location: /casabonsai/index.html');
    exit;
}

// Intentar obtener datos de la tabla usuario por id; si no hay id, buscar por nombre de usuario
$userRow = null;
try {
  if (!empty($siteUser['id'])){
  $stmt = $pdo->prepare('SELECT id_usuario, Usuario, Nombres, Apellidos, Correo, Telefono, Direccion FROM usuario WHERE id_usuario = ? LIMIT 1');
    $stmt->execute([(int)$siteUser['id']]);
    $userRow = $stmt->fetch();
  }
  // fallback: buscar por nombre de usuario (columna Usuario)
  if (empty($userRow) && !empty($siteUser['user'])){
  $stmt = $pdo->prepare('SELECT id_usuario, Usuario, Nombres, Apellidos, Correo, Telefono, Direccion FROM usuario WHERE Usuario = ? LIMIT 1');
    $stmt->execute([trim($siteUser['user'])]);
    $userRow = $stmt->fetch();
  }
} catch (Exception $e){
  $userRow = null;
}

// Obtener residentes asignados a este usuario (si existe id)
$residentes = [];
try {
  $userId = $userRow['id_usuario'] ?? $siteUser['id'] ?? null;
  if (!empty($userId)) {
    $stmt = $pdo->prepare('SELECT id_residente, nombre, apellidos, fecha_nacimiento, contacto_emergencia_nombre, contacto_emergencia_telefono, enfermedades, creado_at FROM residente WHERE id_usuario = ? ORDER BY creado_at DESC');
    $stmt->execute([(int)$userId]);
    $residentes = $stmt->fetchAll();
  }
} catch (Exception $e) {
  $residentes = [];
}

// (se eliminó helper de tiempo relativo — no mostramos "última actualización")

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mi perfil - Casa Bonsái</title>
  <link rel="stylesheet" href="../styles/styles.css">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="../../index.html">
        <img src="../resources/images/logo.png" alt="Casa Bonsái" class="navbar-logo">
        <span class="brand-text">Casa Bonsái</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon" aria-hidden="true"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="servicios.html">Servicios</a></li>
          <li class="nav-item"><a class="nav-link" href="productos.php">Productos</a></li>
          <li class="nav-item"><a class="nav-link" href="contacto.html">Contacto</a></li>
          <li class="nav-item"><a class="nav-link" href="nosotros.html">Nosotros</a></li>
        </ul>

        <!-- mobile-only auth inside the collapse (no id so scripts keep using desktop id) -->
        <ul class="navbar-nav nav-auth-mobile">
          <li class="nav-item"><a href="#" class="nav-link open-login">Ingresar</a></li>
          <li class="nav-item"><a href="#" class="nav-link open-register">Registrarse</a></li>
        </ul>
      </div>

      <!-- desktop auth (keeps the expected id for JS) -->
      <ul id="nav-auth-wrap" class="navbar-nav nav-auth-desktop ml-auto" aria-label="Auth links">
        <li class="nav-item"><a href="#" class="nav-link open-login">Ingresar</a></li>
        <li class="nav-item"><a href="#" class="nav-link open-register">Registrarse</a></li>
      </ul>
    </div>
  </nav>

  <main>
    <section class="page-hero small-hero">
      <div class="container">
        <h1>Mi perfil</h1>
        <p class="lead">Aquí puedes ver a tus familiares asignados</p>
      </div>
    </section>

    <section class="container" style="max-width:900px">
      <div class="profile-header">
        <div class="profile-meta">
          <h2><?php echo e($userRow['Usuario'] ?? $siteUser['user'] ?? 'Usuario'); ?></h2>
          <div class="muted-small">ID: <?php echo e($userRow['id_usuario'] ?? '—'); ?></div>
        </div>
      </div>

  <div class="profile-info-grid">
        <div>
          <h4>Nombre</h4>
          <p><?php echo e(trim(($userRow['Nombres'] ?? '') . ' ' . ($userRow['Apellidos'] ?? '')) ?: '—'); ?></p>
        </div>
        <div>
          <h4>Usuario</h4>
          <p><?php echo e($userRow['Usuario'] ?? $siteUser['user'] ?? '—'); ?></p>
        </div>
        <div>
          <h4>Correo</h4>
          <p><?php echo e($userRow['Correo'] ?? '—'); ?></p>
        </div>
        <div>
          <h4>Teléfono</h4>
          <p><?php echo e($userRow['Telefono'] ?? '—'); ?></p>
        </div>
        <div class="profile-info-full">
          <h4>Dirección</h4>
          <p><?php echo e($userRow['Direccion'] ?? '—'); ?></p>
        </div>
      </div>

      <section class="residents-section" style="margin-top:22px">
        <h3>Residentes asignados</h3>
        <?php if (!empty($residentes)): ?>
          <div class="resident-grid" style="margin-top:12px">
            <?php foreach ($residentes as $r):
              $photoPath = !empty($r['foto']) ? '/casabonsai/scr/resources/images/' . ltrim($r['foto'], '/') : '/casabonsai/scr/resources/images/perfil.png';
              $shortText = mb_strimwidth($r['enfermedades'] ?? $r['observaciones'] ?? '', 0, 200, '...');
            ?>
            <article class="card resident-card">
              <img class="card-img-top" src="<?php echo e($photoPath); ?>" alt="<?php echo e($r['nombre']); ?>">
              <div class="card-body">
                <h5 class="card-title"><?php echo e(trim($r['nombre'].' '.($r['apellidos'] ?? ''))); ?></h5>
                <p class="card-text muted-small"><?php echo e($r['fecha_nacimiento'] ?? '—'); ?> • <?php echo e($r['genero'] ?? '—'); ?></p>
                <p class="card-text"><?php echo e($shortText ?: 'Sin anotaciones'); ?></p>
                <p class="muted-small">Contacto: <?php echo e($r['contacto_emergencia_nombre'] ?? '—'); ?> / <?php echo e($r['contacto_emergencia_telefono'] ?? '—'); ?></p>
                <div style="margin-top:10px">
                  <a class="btn-link-green" href="/casabonsai/scr/pages/residente.php?id=<?php echo (int)$r['id_residente']; ?>">Ver más detalles</a>
                </div>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="muted-small">No hay residentes asignados a este usuario.</p>
        <?php endif; ?>
      </section>

    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-inner container">
      <div class="footer-bottom">© 2025 Casa Bonsái. Todos los derechos reservados.</div>
    </div>
  </footer>

  <script src="../scripts/session-check.js" defer></script>
  <script src="../scripts/nav-toggle.js" defer></script>
  <script src="../scripts/auth.js" defer></script>
</body>
</html>
