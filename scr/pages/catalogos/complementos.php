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
		$sql = "SELECT p.* FROM producto p
						JOIN producto_categoria pc ON pc.producto_id = p.id_producto
						JOIN categoria_producto c ON c.id_categoria = pc.categoria_id
						WHERE p.activo = 1 AND (c.nombre = :c1 OR c.nombre = :c2 OR c.slug = :slug)
						ORDER BY p.creado_at DESC LIMIT 200";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([':c1' => 'Complementos', ':c2' => 'Complementos', ':slug' => 'complementos']);
		$productos = $stmt->fetchAll();
}catch(Exception $ex){
		$last_error = $ex->getMessage();
		$productos = [];
}

if (empty($productos)){
		$productos = [
				['nombre'=>'Suplemento Nutricional en Polvo','descripcion'=>'Fórmula equilibrada en vitaminas y minerales, fácil de disolver en agua o leche.','precio'=>'180','imagen'=>'/casabonsai/scr/resources/images/placeholder.png'],
				['nombre'=>'Bote de Vitaminas Masticables','descripcion'=>'Tabletas masticables con multivitamínicos adaptados para adultos mayores.','precio'=>'150','imagen'=>'/casabonsai/scr/resources/images/placeholder.png'],
				['nombre'=>'Barritas Energéticas Blandas','descripcion'=>'Barritas de textura blanda, alto aporte calórico y fácil de masticar.','precio'=>'65','imagen'=>'/casabonsai/scr/resources/images/placeholder.png'],
				['nombre'=>'Gel Alimenticio Nutritivo','descripcion'=>'Gel de fácil ingestión, alto en energía y con nutrientes esenciales.','precio'=>'110','imagen'=>'/casabonsai/scr/resources/images/placeholder.png']
		];
}

$productos_count = is_array($productos) ? count($productos) : 0;
?>
<!doctype html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>Complementos - Casa Bonsái</title>
	<link rel="stylesheet" href="../../styles/styles.css">
	<meta name="description" content="Complementos y suplementos pensados para adultos mayores: texturas suaves, aporte vitamínico y presentaciones prácticas.">
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
												<a class="nav-link dropdown-toggle" href="scr/pages/productos.php" id="productosDropdown" role="button" aria-expanded="false" aria-controls="productosMenu">Productos <span class="dropdown-caret" aria-hidden="true"></span></a>
												<ul id="productosMenu" class="dropdown-menu" aria-labelledby="productosDropdown">
														<li><a class="dropdown-item" href="alimentacion.php">Alimentación</a></li>
														<li><a class="dropdown-item" href="complementos.php">Complementos</a></li>
														<li><a class="dropdown-item" href="cuidado-personal.php">Cuidado personal</a></li>
														<li><a class="dropdown-item" href="higiene.php">Higiene</a></li>
														<li><a class="dropdown-item" href="hogar.php">Hogar</a></li>
														<li><a class="dropdown-item" href="movilidad.php">Movilidad</a></li>
														<li><a class="dropdown-item" href="ocio.php">Ocio</a></li>
														<li><a class="dropdown-item" href="ortopedia.php">Ortopedia</a></li>
														<li><a class="dropdown-item" href="/rehabilitacion.php">Rehabilitación</a></li>
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

	<header class="page-hero page-hero--small">
		<div class="container">
			<h1>Complementos</h1>
			<p class="lead">Suplementos y complementos alimenticios adaptados a las necesidades de adultos mayores.</p>
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
		<section class="products-grid" aria-label="Complementos">
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
					<h3>Casa Bonsái</h3>
					<p>Como un bonsái que crece con paciencia y dedicación, en Casa Bonsái cultivamos el bienestar de cada adulto mayor con amor, respeto y cuidado integral en un ambiente de tranquilidad y armonía.</p>
					<div class="social" aria-label="Redes sociales" style="margin-top:10px">
						<a href="#" aria-label="facebook">f</a>
						<a href="#" aria-label="instagram">ig</a>
						<a href="#" aria-label="youtube">yt</a>
					</div>
				</div>
				<div class="footer-col footer-links">
					<h4>Enlaces</h4>
					<ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px">
						<li><a href="../../index.html">Inicio</a></li>
						<li><a href="../servicios.html">Servicios</a></li>
						<li><a href="../productos.php">Productos</a></li>
						<li><a href="../contacto.html">Contacto</a></li>
					</ul>
				</div>
				<div class="footer-col footer-services">
					<h4>Servicios</h4>
					<ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:6px">
						<li><a href="#">Atención Médica</a></li>
						<li><a href="#">Alimentación</a></li>
						<li><a href="#">Fisioterapia</a></li>
						<li><a href="#">Actividades</a></li>
					</ul>
				</div>
				<div class="footer-col footer-contact">
					<h4>Contacto</h4>
					<p style="margin:6px 0">📞 (55) 1234-5678</p>
					<p style="margin:6px 0">✉️ info@casabonsai.com</p>
					<p style="margin:6px 0">📍 Av. Serenidad 456, CDMX</p>
				</div>
			</div>
			<hr>
			<div class="footer-bottom">© 2025 Casa Bonsái. Todos los derechos reservados.</div>
		</div>
	</footer>

	<script src="../../scripts/nav-toggle.js" defer></script>
	<script src="../../scripts/auth.js" defer></script>
</body>
</html>
