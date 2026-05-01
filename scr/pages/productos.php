<?php
// Página de productos dinámica: carga desde la tabla `producto`
require __DIR__ . '/../../conn.php';

// Obtener productos activos
try {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE activo = 1 ORDER BY creado_at DESC LIMIT 200");
    $stmt->execute();
    $productos = $stmt->fetchAll();
} catch (Exception $e) {
    $productos = [];
}

// Obtener categorías existentes para el combobox (leer `categoria_producto`)
$cats = [];
try {
  $cstmt = $pdo->prepare("SELECT nombre FROM categoria_producto ORDER BY nombre");
  $cstmt->execute();
  $cats = $cstmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $ex) { $cats = []; }

function product_image_src($imagen){
    // imagen puede ser: data-uri, URL absoluta (http/https), ruta relativa guardada en DB, o nombre de archivo
    if (empty($imagen)) return '../resources/images/placeholder.png';
    $img = trim($imagen);
    if (strpos($img, 'data:') === 0) return $img;
    if (preg_match('#^https?://#i', $img)) return $img;
    if (strpos($img, '/') === 0) return $img; // ya es ruta absoluta
    // fallback: asumir nombre de archivo en uploads
  return '/casabonsai/scr/resources/images/uploads/' . rawurlencode($img);
}

function money($v){
    if ($v === null || $v === '' || $v == 0) return null;
    return '$' . number_format((float)$v, 0, ',', '.');
}
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Productos - Casa Bonsái</title>
  <link rel="stylesheet" href="../styles/styles.css">
  <meta name="description" content="Catálogo de productos para adultos mayores: pañales, bastones, ayudas técnicas y solicitud de personal.">
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
          <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="productos.php" id="productosDropdown" role="button" aria-expanded="false" aria-controls="productosMenu">Productos <span class="dropdown-caret" aria-hidden="true"></span></a>
                        <ul id="productosMenu" class="dropdown-menu" aria-labelledby="productosDropdown">
                            <li><a class="dropdown-item" href="catalogos/alimentacion.php">Alimentación</a></li>
                            <li><a class="dropdown-item" href="catalogos/complementos.php">Complementos</a></li>
                            <li><a class="dropdown-item" href="catalogos/cuidado-personal.php">Cuidado personal</a></li>
                            <li><a class="dropdown-item" href="catalogos/higiene.php">Higiene</a></li>
                            <li><a class="dropdown-item" href="catalogos/hogar.php">Hogar</a></li>
                            <li><a class="dropdown-item" href="catalogos/movilidad.php">Movilidad</a></li>
                            <li><a class="dropdown-item" href="catalogos/ocio.php">Ocio</a></li>
                            <li><a class="dropdown-item" href="catalogos/ortopedia.php">Ortopedia</a></li>
                            <li><a class="dropdown-item" href="catalogos/rehabilitacion.php">Rehabilitación</a></li>
                            <li><a class="dropdown-item" href="catalogos/seguridad.php">Seguridad</a></li>
                            <li><a class="dropdown-item" href="catalogos/tecnologia.php">Tecnología</a></li>
                            <li><a class="dropdown-item" href="catalogos/vestimenta.php">Vestimenta</a></li>
                        </ul>
                    </li>
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

  <main>
    <section class="page-hero small-hero">
      <div class="container">
        <h1>Productos</h1>
        <p class="lead">Pañales, bastones, ayudas técnicas y servicios de personal. Navega nuestro catálogo y solicita lo que necesites.</p>
        <?php
          // Obtener categorías existentes para el combobox
          $cats = [];
          try {
            $cstmt = $pdo->prepare("SELECT DISTINCT categoria FROM producto WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
            $cstmt->execute();
            $cats = $cstmt->fetchAll(PDO::FETCH_COLUMN);
          } catch (Exception $ex) { $cats = []; }
        ?>
        
      </div>
    </section>

    <section class="container" aria-label="Catálogo de productos">

      <div class="products-list" aria-label="Lista de productos">

<?php foreach($productos as $p):
    $img = product_image_src($p['imagen'] ?? '');
    $price = money($p['precio'] ?? null);
    $category = 'general';
    // si existe campo categoría en la fila úselo (compatibilidad futura)
    if (!empty($p['categoria'])) $category = $p['categoria'];
?>
      <article class="product-row" data-category="<?php echo e($category); ?>">
        <div class="product-thumb">
          <img src="<?php echo e($img); ?>" alt="<?php echo e($p['nombre']); ?>">
        </div>
        <div class="product-info">
          <h3><?php echo e($p['nombre']); ?></h3>
          <p class="muted"><?php echo e($p['descripcion']); ?></p>
          <div class="price-row">
            <?php if($price): ?>
              <span class="price-sale"><?php echo e($price); ?></span>
            <?php else: ?>
              <span class="product-price">Precio a consultar</span>
            <?php endif; ?>
          </div>
          <div class="actions-row">
            <button class="btn btn-buy open-modal" data-product="<?php echo e($p['nombre']); ?>">Solicitar</button>
          </div>
        </div>
      </article>
<?php endforeach; ?>

      </div>
        
      <div id="product-modal" class="modal" aria-hidden="true">
        <div class="modal-dialog" role="dialog" aria-modal="true">
          <button class="modal-close" aria-label="Cerrar">×</button>
          <h2 id="modal-title">Solicitar</h2>
          <form id="modal-form">
            <input type="hidden" name="product" id="modal-product">
            <label>Nombre completo<br><input name="name" required></label>
            <label>Teléfono o celular<br><input name="phone" required></label>
            <label>Mensaje / Detalles<br><textarea name="message" rows="4"></textarea></label>
            <div class="buttons-row">
              <button class="btn btn-primary" type="submit">Enviar solicitud</button>
              <button class="btn btn-outline form-cancel" type="button">Cancelar</button>
            </div>
          </form>
        </div>
      </div>

    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-inner container">
      <div class="footer-grid">
        <div class="footer-col footer-brand">
          <div class="footer-brand-title">Casa Bonsái</div>
          <p>Como un bonsái que crece con paciencia y dedicación, en Casa Bonsái cultivamos el bienestar de cada adulto mayor con amor, respeto y cuidado integral en un ambiente de tranquilidad y armonía.</p>
          <div class="social" aria-label="Redes sociales" style="margin-top:10px">
            <a href="#" aria-label="facebook">f</a>
            <a href="#" aria-label="instagram">ig</a>
            <a href="#" aria-label="youtube">yt</a>
          </div>
        </div>
        <div class="footer-col footer-links">
          <h4>Enlaces</h4>
          <ul class="footer-list">
            <li><a href="../../index.html">Inicio</a></li>
            <li><a href="servicios.html">Servicios</a></li>
            <li><a href="productos.php">Productos</a></li>
            <li><a href="contacto.html">Contacto</a></li>
          </ul>
        </div>
        <div class="footer-col footer-services">
          <h4>Servicios</h4>
          <ul class="footer-list">
            <li><a href="#">Atención Médica</a></li>
            <li><a href="#">Alimentación</a></li>
            <li><a href="#">Fisioterapia</a></li>
            <li><a href="#">Actividades</a></li>
          </ul>
        </div>
        <div class="footer-col footer-contact">
          <h4>Contacto</h4>
          <p class="contact-line">📞 <a href="tel:+525512345678">(55) 1234-5678</a></p>
          <p class="contact-line">✉️ <a href="mailto:info@casabonsai.com">info@casabonsai.com</a></p>
          <p class="contact-line">📍 Av. Serenidad 456, CDMX</p>
        </div>
      </div>
      <hr>
      <div class="footer-bottom">© 2025 Casa Bonsái. Todos los derechos reservados.</div>
    </div>
  </footer>

  <script src="../scripts/productos.js" defer></script>
  <script src="../scripts/session-check.js" defer></script>
  <script src="../scripts/nav-toggle.js" defer></script>
  <script src="../scripts/auth.js" defer></script>

  <script>
    (function(){
      var sels = document.querySelectorAll('.category-select');
      if (!sels || !sels.length) return;
      sels.forEach(function(sel){
        sel.addEventListener('change', function(){
          var v = this.value || '';
          if (!v) {
            window.location.href = 'productos.php';
          } else {
            window.location.href = 'catalogo.php?cat=' + encodeURIComponent(v);
          }
        });
      });
    })();
  </script>

  <script>
    // Dropdown navbar categories: toggle menu and close on outside click
    (function(){
      var btn = document.getElementById('catDropdownBtn');
      var menu = document.getElementById('catDropdownMenu');
      if (!btn || !menu) return;
      function openMenu(){ menu.style.display = 'block'; btn.setAttribute('aria-expanded','true'); }
      function closeMenu(){ menu.style.display = 'none'; btn.setAttribute('aria-expanded','false'); }
      btn.addEventListener('click', function(e){ e.stopPropagation(); if (menu.style.display === 'block') closeMenu(); else openMenu(); });
      // close when clicking outside
      document.addEventListener('click', function(){ if (menu.style.display === 'block') closeMenu(); });
      // keyboard: Esc to close
      document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeMenu(); });
    })();
  </script>

  <div id="login-modal" class="modal" aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true">
      <h2>Ingresar</h2>
      <form id="login-form" autocomplete="on">
        <label>Usuario<br><input id="login-user" name="user" autocomplete="username" required></label>
        <label>Contraseña<br><input id="login-pass" type="password" name="pass" autocomplete="current-password" required></label>
  <div style="margin-top:12px;display:flex;gap:8px"><button class="btn btn-success" type="submit">Entrar</button><button type="button" class="btn btn-outline form-cancel">Cancelar</button></div>
      </form>
    </div>
  </div>

  <div id="register-modal" class="modal" aria-hidden="true">
    <div class="modal-dialog" role="dialog" aria-modal="true">
      <h2>Registrarse</h2>
      <form id="register-form" autocomplete="on">
        <label>Nombre(s) completo<br><input id="register-name" name="name" autocomplete="given-name" required></label>
        <label>Apellidos<br><input id="register-lastname" name="apellidos" autocomplete="family-name" required></label>
        <label>Teléfono<br><input id="register-phone" name="phone" autocomplete="tel" required></label>
        <label>Email<br><input id="register-email" type="email" name="email" autocomplete="email"></label>
        <label>Dirección<br><input id="register-address" name="address" autocomplete="street-address"></label>
        <label>Usuario<br><input id="register-user" name="user" autocomplete="username" required></label>
        <label>Contraseña<br><input id="register-pass" type="password" name="pass" autocomplete="new-password" required></label>
  <div style="margin-top:12px;display:flex;gap:8px"><button class="btn btn-success" type="submit">Registrar</button><button type="button" class="btn btn-outline form-cancel">Cancelar</button></div>
      </form>
    </div>
  </div>
</body>
</html>
