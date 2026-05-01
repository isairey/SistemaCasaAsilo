<?php
require __DIR__ . '/../../../conn.php';

function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function product_image_src($imagen){
    if (empty($imagen)) return '/casabonsai/scr/resources/images/placeholder.png';
    $img = trim($imagen);
    if (strpos($img, 'data:') === 0) return $img;
    if (preg_match('#^https?://#i', $img)) return $img;
    if (strpos($img, '/') === 0) return $img;
    return '/casabonsai/scr/resources/images/uploads/' . rawurlencode($img);
}

$productos = [];
$last_error = null;
try{
      // La base de datos usa tablas separadas para categorías: `categoria_producto` y `producto_categoria`.
      // Hacemos JOIN para obtener productos que pertenecen a la categoría "Alimentación" (con o sin tilde) o con slug 'alimentacion'.
      $sql = "SELECT p.* FROM producto p
        JOIN producto_categoria pc ON pc.producto_id = p.id_producto
        JOIN categoria_producto c ON c.id_categoria = pc.categoria_id
        WHERE p.activo = 1 AND (c.nombre = :c1 OR c.nombre = :c2 OR c.slug = :slug)
        ORDER BY p.creado_at DESC LIMIT 200";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([':c1' => 'Alimentación', ':c2' => 'Alimentacion', ':slug' => 'alimentacion']);
      $productos = $stmt->fetchAll();
}catch(Exception $ex){
  // Guardamos el mensaje para depuración visible en la página
  $last_error = $ex->getMessage();
  $productos = [];
}

// Si no hay resultados en DB, mostrar ejemplos estáticos de alimentos para adulto mayor
if (empty($productos)){
    $productos = [
        ['nombre'=>'Puré Nutritivo de Vegetales','descripcion'=>'Puré suave, alto en fibra y proteínas, fácil de masticar y digerir.','precio'=>'120','imagen'=>'/casabonsai/scr/resources/images/placeholder.png'],
        ['nombre'=>'Galletas Blandas Fortificadas','descripcion'=>'Galletas ricas en calcio y vitaminas, textura suave para personas con masticación limitada.','precio'=>'80','imagen'=>'/casabonsai/scr/resources/images/placeholder.png'],
        ['nombre'=>'Batido Proteico (500ml)','descripcion'=>'Bebida nutricional balanceada para recuperación y aporte energético.','precio'=>'95','imagen'=>'/casabonsai/scr/resources/images/placeholder.png'],
        ['nombre'=>'Sopa Cremosa de Pollo','descripcion'=>'Sopa en presentación cremosa, baja en sodio y fácil de consumir.','precio'=>'140','imagen'=>'/casabonsai/scr/resources/images/placeholder.png']
    ];
}
// Debug: número de productos obtenidos (temporal)
$productos_count = is_array($productos) ? count($productos) : 0;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Alimentación - Casa Bonsái</title>
  <link rel="stylesheet" href="../../styles/styles.css">
  <meta name="description" content="Productos de alimentación para adultos mayores: alimentos suaves, fortificados y de fácil consumo.">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="../../../index.html">
        <img src="../../resources/images/logo.png" alt="Casa Bonsái" class="navbar-logo">
        <span class="brand-text">Casa Bonsái</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon" aria-hidden="true"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="servicios.html">Servicios</a></li>
            <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="/casabonsai/scr/pages/productos.php" id="productosDropdown" role="button" aria-expanded="false" aria-controls="productosMenu">Productos <span class="dropdown-caret" aria-hidden="true"></span></a>
                        <ul id="productosMenu" class="dropdown-menu" aria-labelledby="productosDropdown">
                            <li><a class="dropdown-item" href="alimentacion.php">Alimentación</a></li>
                            <li><a class="dropdown-item" href="complementos.php">Complementos</a></li>
                            <li><a class="dropdown-item" href="cuidado-personal.php">Cuidado personal</a></li>
                            <li><a class="dropdown-item" href="higiene.php">Higiene</a></li>
                            <li><a class="dropdown-item" href="hogar.php">Hogar</a></li>
                            <li><a class="dropdown-item" href="movilidad.php">Movilidad</a></li>
                            <li><a class="dropdown-item" href="ocio.php">Ocio</a></li>
                            <li><a class="dropdown-item" href="ortopedia.php">Ortopedia</a></li>
                            <li><a class="dropdown-item" href="rehabilitacion.php">Rehabilitación</a></li>
                            <li><a class="dropdown-item" href="seguridad.php">Seguridad</a></li>
                            <li><a class="dropdown-item" href="tecnologia.php">Tecnología</a></li>
                            <li><a class="dropdown-item" href="vestimenta.php">Vestimenta</a></li>
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


      <h1>Alimentación</h1>
      <p class="lead">Alimentos pensados para adultos mayores: fácil masticación, alto valor nutricional y presentación práctica.</p>
    </div>
  </header>

  <?php if (!empty($last_error) || $productos_count === 0): ?>
    <div style="background:#fff3cd;border:1px solid #ffeeba;padding:12px;margin:12px auto;max-width:1100px;border-radius:6px;color:#856404">
      <strong>Depuración:</strong>
      <div>Productos encontrados: <strong><?php echo (int)$productos_count; ?></strong></div>
      <?php if (!empty($last_error)): ?><div>Error de consulta: <code><?php echo htmlspecialchars($last_error,ENT_QUOTES,'UTF-8'); ?></code></div><?php endif; ?>
      <div style="margin-top:6px;font-size:0.95em;color:#6c757d">Si el conteo es 0 y no hay error, la página mostrará ejemplos estáticos.</div>
    </div>
  <?php endif; ?>

  <main class="container">
    <section class="products-grid" aria-label="Productos de alimentación">
      <?php foreach($productos as $p):
        $nombre = $p['nombre'] ?? $p['titulo'] ?? 'Producto';
        $desc = $p['descripcion'] ?? $p['descripcion_corta'] ?? '';
        $precio = $p['precio'] ?? null;
        $img = product_image_src($p['imagen'] ?? ($p['image'] ?? ''));
      ?>
      <article class="product-card">
        <div class="product-media">
          <img src="<?php echo e($img); ?>" alt="<?php echo e($nombre); ?>">
        </div>
        <div class="product-body">
          <h3 class="product-title"><?php echo e($nombre); ?></h3>
          <p class="product-desc"><?php echo e($desc); ?></p>
          <?php if ($precio): ?>
            <div class="product-price"><?php echo '$' . number_format((float)$precio,0,',','.'); ?></div>
          <?php endif; ?>
          <div style="margin-top:8px"><button class="btn btn-success btn-sm">Solicitar</button></div>
        </div>
      </article>
      <?php endforeach; ?>
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