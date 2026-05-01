<?php
// Página genérica de catálogo: filtra productos por categoría
require __DIR__ . '/../../conn.php';

$cat = trim($_GET['cat'] ?? ($_GET['categoria'] ?? ''));
// Aceptar slugs (guiones) y transformar a nombre aproximado
if ($cat === '' && !empty($_GET['slug'])) {
  $cat = str_replace('-', ' ', $_GET['slug']);
}

try {
  if ($cat !== '') {
    // Intentar filtrar por columna 'categoria' si existe
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE activo = 1 AND (categoria = :cat OR categoria_slug = :slug) ORDER BY creado_at DESC LIMIT 500");
    $stmt->execute([':cat' => $cat, ':slug' => $cat]);
    $productos = $stmt->fetchAll();
  } else {
    $stmt = $pdo->prepare("SELECT * FROM producto WHERE activo = 1 ORDER BY creado_at DESC LIMIT 500");
    $stmt->execute();
    $productos = $stmt->fetchAll();
  }
} catch (Exception $e) {
  $productos = [];
}

function product_image_src($imagen){
    if (empty($imagen)) return '../resources/images/placeholder.png';
    $img = trim($imagen);
    if (strpos($img, 'data:') === 0) return $img;
    if (preg_match('#^https?://#i', $img)) return $img;
    if (strpos($img, '/') === 0) return $img;
    return '/casabonsai/scr/resources/images/uploads/' . rawurlencode($img);
}

function e($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Catálogo - <?php echo e(ucfirst($cat ?: 'Productos')); ?></title>
  <link rel="stylesheet" href="../styles/styles.css">
</head>
<body>
  <main>
    <section class="page-hero small-hero">
      <div class="container">
        <h1><?php echo e(ucfirst($cat ?: 'Productos')); ?></h1>
        <p class="lead">Catálogo de <?php echo e(ucfirst($cat ?: 'productos')); ?> disponibles.</p>
      </div>
    </section>

    <section class="container">
      <div class="products-list">
<?php foreach($productos as $p):
    $img = product_image_src($p['imagen'] ?? '');
    $price = isset($p['precio']) ? ('$' . number_format((float)$p['precio'],0,',','.')) : null;
?>
      <article class="product-row">
        <div class="product-thumb"><img src="<?php echo e($img); ?>" alt="<?php echo e($p['nombre']); ?>"></div>
        <div class="product-info">
          <h3><?php echo e($p['nombre']); ?></h3>
          <p class="muted"><?php echo e($p['descripcion']); ?></p>
          <div class="price-row"><?php if($price): ?><span class="price-sale"><?php echo e($price); ?></span><?php else: ?><span class="product-price">Precio a consultar</span><?php endif; ?></div>
          <div class="actions-row"><button class="btn btn-buy open-modal" data-product="<?php echo e($p['nombre']); ?>">Solicitar</button></div>
        </div>
      </article>
<?php endforeach; ?>
      </div>
    </section>
  </main>
</body>
</html>
