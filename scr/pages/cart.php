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

        <ul class="navbar-nav nav-auth-mobile">
          <li class="nav-item"><a href="#" class="nav-link open-login">Ingresar</a></li>
          <li class="nav-item"><a href="#" class="nav-link open-register">Registrarse</a></li>
        </ul>
      </div>

      <ul id="nav-auth-wrap" class="navbar-nav nav-auth-desktop ml-auto" aria-label="Auth links">
        <li class="nav-item"><a href="#" class="nav-link open-login">Ingresar</a></li>
        <li class="nav-item"><a href="#" class="nav-link open-register">Registrarse</a></li>
      </ul>
    </div>
  </nav>
<?php
// Asegurar cookie de sesión consistente para todo el sitio
session_set_cookie_params([ 'path' => '/', 'httponly' => true, 'samesite' => 'Lax' ]);
session_start();
require __DIR__ . '/../../conn.php';
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
function money($v){ if ($v === null || $v === '') return 'Precio a consultar'; return '$' . number_format((float)$v,0,',','.'); }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Carrito - Casa Bonsái</title>
  <link rel="stylesheet" href="../styles/styles.css">
  <style>.cart-list{max-width:980px;margin:32px auto;display:grid;gap:12px}.cart-item{display:flex;gap:12px;align-items:center;background:#fff;padding:12px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.04)}.cart-summary{max-width:980px;margin:18px auto;text-align:right}</style>
</head>
<body>
  <script src="../scripts/session-check.js" defer></script>
  <script src="../scripts/nav-toggle.js" defer></script>
  <main>
    <div class="container">
      <h1>Tu carrito</h1>
      <div class="cart-list">
        <?php if(empty($_SESSION['cart'])): ?>
          <div class="card">No hay productos en el carrito.</div>
        <?php else: foreach($_SESSION['cart'] as $it): ?>
          <div class="cart-item">
            <div class="thumb-box">
              <img src="../resources/images/placeholder.png" alt="thumb">
            </div>
            <div class="flex-1">
              <strong><?php echo e($it['name']); ?></strong>
              <div class="muted-small">Cantidad: <?php echo e($it['qty']); ?></div>
            </div>
            <div class="text-right">
              <div style="font-weight:700"><?php echo e(money($it['price'])); ?></div>
              <form method="post" action="../actions/cart.php" class="mt-8" >
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="id" value="<?php echo e($it['id']); ?>">
                <button class="btn btn-outline" type="submit">Eliminar</button>
              </form>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>

      <div class="cart-summary">
        <form method="post" action="../actions/cart.php" class="form-inline">
          <input type="hidden" name="action" value="clear">
          <button class="btn btn-outline" type="submit">Vaciar carrito</button>
        </form>
        <a class="btn btn-buy ml-8" href="../pages/contacto.html">Finalizar compra / Solicitar</a>
      </div>
    </div>
  </main>
  <footer class="bg-light text-center text-lg-start mt-5"><div class="text-center p-3">© 2025 Casa Bonsái</div></footer>
</body>
</html>
