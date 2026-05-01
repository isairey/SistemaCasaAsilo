<?php
require __DIR__ . '/auth.php';
require_admin();
// dashboard counts
$counts = [];
$tables = ['usuario','producto','servicio','contacto','testimonio'];
foreach($tables as $t){
    try{
        $row = $pdo->query("SELECT COUNT(*) as c FROM {$t}")->fetch();
        $counts[$t] = $row ? (int)$row['c'] : 0;
    } catch(Exception $e){ $counts[$t] = 0; }
}
// include minimal header (se eliminó el header compartido)
<<<<<<< HEAD
 $title = 'Casa Bonsái - Panel Administrativo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* --- 1. Estilos Generales --- */
    :root {
      --primary-color: #1b5e20; /* verde oscuro para mejor contraste */
      --primary-dark: #123f16;
      --bg-color: #f7f9f6;
      --text-color: #333;
      --card-bg: #ffffff;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg-color);
      margin: 0;
      padding: 0;
      color: var(--text-color);
    }

    /* --- 2. Barra de Navegación --- */
    .navbar {
      background-color: white;
      padding: 15px 40px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .brand {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--primary-dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logout-btn {
      text-decoration: none;
      color: #666;
      font-weight: 500;
      transition: color 0.3s;
    }
    .logout-btn:hover { color: #d9534f; }

    /* --- 3. Contenedor Principal --- */
    .main-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
    .header-title { text-align:center; margin-bottom: 40px; }
    .header-title h1 { color: var(--primary-dark); font-size: 2.5rem; margin:0; }
    .header-title p { color:#777; font-size:1.05rem }

    /* --- 4. Secciones y Grid de Tarjetas --- */
    .section-title { border-bottom: 2px solid #e0e0e0; padding-bottom:10px; margin-bottom:20px; margin-top:40px; color:#555; text-transform:uppercase; font-size:0.9rem; letter-spacing:1px; font-weight:bold; }
    .grid-container { display:grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap:20px; }

    /* --- 5. Diseño de la Tarjeta (Botón) --- */
    .card { background-color: var(--card-bg); border-radius:12px; padding:25px; text-decoration:none; color:var(--text-color); box-shadow:0 4px 6px rgba(0,0,0,0.02); border:1px solid #eee; transition:all .3s ease; display:flex; align-items:center; gap:15px; }
    .card i { font-size:1.8rem; color:var(--primary-color); background-color:#f0f7f0; padding:15px; border-radius:50%; transition:all .3s ease; }
    .card-info h3 { margin:0; font-size:1.1rem; font-weight:600 }
    .card-info span { font-size:0.85rem; color:#888 }
    .card:hover { transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.08); border-color:var(--primary-color); }
    .card:hover i { background-color:var(--primary-color); color:white }
  </style>
</head>
<body>

  <nav class="navbar">
      <div class="brand"><i class="fa-solid fa-leaf"></i> Casa Bonsai Admin</div>
    <div>
      <span style="margin-right:15px;color:#777;">Hola, Admin</span>
      <a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar Sesión</a>
    </div>
  </nav>

  <div class="main-container">
    <div class="header-title">
      <h1>Panel de Control</h1>
      <p>Selecciona un módulo para gestionar la información</p>
    </div>

    <div class="section-title">Gestión de Inventario</div>
    <div class="grid-container">
      <a href="productos.php" class="card">
        <i class="fa-solid fa-box-open"></i>
        <div class="card-info">
          <h3>Productos</h3>
          <span>Ver listado completo · <strong><?php echo $counts['producto'] ?? 0; ?></strong></span>
        </div>
      </a>
            
      <a href="../crud/categoria_producto.php" class="card">
        <i class="fa-solid fa-tags"></i>
        <div class="card-info">
          <h3>Categorías</h3>
          <span>Clasificación de items</span>
        </div>
      </a>

      <a href="../crud/producto_categoria.php" class="card">
        <i class="fa-solid fa-layer-group"></i>
        <div class="card-info">
          <h3>Asignaciones</h3>
          <span>Producto - Categoría</span>
        </div>
      </a>

      <a href="../crud/proveedores.php" class="card">
        <i class="fa-solid fa-truck"></i>
        <div class="card-info">
          <h3>Proveedores</h3>
          <span>Contactos y datos de proveedores</span>
        </div>
      </a>

      <a href="../crud/entradas_inventario.php" class="card">
        <i class="fa-solid fa-arrow-down-to-line"></i>
        <div class="card-info">
          <h3>Entradas Inventario</h3>
          <span>Registrar entradas / salidas de stock</span>
        </div>
      </a>
    </div>

    <div class="section-title">Personas y Accesos</div>
    <div class="grid-container">
      <a href="../crud/residente.php" class="card">
        <i class="fa-solid fa-user-injured"></i>
        <div class="card-info">
          <h3>Residentes</h3>
          <span>Gestionar expedientes</span>
        </div>
      </a>

      <a href="usuarios.php" class="card">
        <i class="fa-solid fa-users-gear"></i>
        <div class="card-info">
          <h3>Usuarios Sistema</h3>
          <span>Administradores y Staff · <strong><?php echo $counts['usuario'] ?? 0; ?></strong></span>
        </div>
      </a>

      <a href="../crud/rol.php" class="card">
        <i class="fa-solid fa-shield-halved"></i>
        <div class="card-info">
          <h3>Roles</h3>
          <span>Permisos de acceso</span>
        </div>
      </a>

      <a href="../crud/usuario_rol.php" class="card">
        <i class="fa-solid fa-user-lock"></i>
        <div class="card-info">
          <h3>Asignar Roles</h3>
          <span>Usuario - Rol</span>
        </div>
      </a>
    </div>

    <div class="section-title">Página Web</div>
    <div class="grid-container">
      <a href="servicios.php" class="card">
        <i class="fa-solid fa-bell-concierge"></i>
        <div class="card-info">
          <h3>Servicios</h3>
          <span>Oferta comercial · <strong><?php echo $counts['servicio'] ?? 0; ?></strong></span>
        </div>
      </a>

      <a href="testimonios.php" class="card">
        <i class="fa-solid fa-comments"></i>
        <div class="card-info">
          <h3>Testimonios</h3>
          <span>Opiniones de clientes · <strong><?php echo $counts['testimonio'] ?? 0; ?></strong></span>
        </div>
      </a>

      <a href="contactos.php" class="card">
        <i class="fa-solid fa-envelope"></i>
        <div class="card-info">
          <h3>Mensajes</h3>
          <span>Formulario de contacto · <strong><?php echo $counts['contacto'] ?? 0; ?></strong></span>
        </div>
      </a>
    </div>

  </div>

</body>
</html>
    
<?php echo '</div>'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>
=======
$title = 'Casa Bonsái - Panel Administrativo';
echo '<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . htmlspecialchars($title) . '</title>'
  . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">'
  . '<link rel="stylesheet" href="inc/admin-min.css">'
  . '<link rel="stylesheet" href="inc/tabs.css">'
  . '</head><body>';
include __DIR__ . '/inc/tabs.php';
echo '<div class="container py-4">';
?>
      <div class="text-center mb-4">
        <h4 class="text-secondary mb-1">Panel de Control</h4>
        <h1 class="display-4 m-0"><span class="text-primary">Dashboard</span> Administrativo</h1>
      </div>


      <!-- Action cards -->

        <!-- Dynamic tables management -->
        <div class="row mt-3">
          <div class="col-12">
            <div class="bg-white rounded shadow p-4">
              <h5>Gestionar tablas </h5>
              <p class="muted-small">Para Crear Borrar</p>
              <div class="d-flex flex-wrap" style="gap:8px">
              <?php
                try {
                  $tbls = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN, 0);
                } catch (Exception $e) { $tbls = []; }
                if (count($tbls)) {
                  // friendly name map
                  $friendlyMap = [
                    'usuario' => 'Usuario',
                    'producto' => 'Producto',
                    'servicio' => 'Servicio',
                    'contacto' => 'Contacto',
                    'testimonio' => 'Testimonio',
                    'residente' => 'Residente',
                    'rol' => 'Rol',
                    'categoria_producto' => 'Categoria producto',
                    'producto_categoria' => 'Producto categoria',
                    'usuario_rol' => 'Usuario rol'
                  ];

                  foreach ($tbls as $t) {
                    $label = $friendlyMap[$t] ?? ucfirst(str_replace('_',' ',$t));
                    // prefer direct CRUD file when present
                    $crudPath = __DIR__ . '/../crud/' . $t . '.php';
                    if (file_exists($crudPath)) {
                      $href = '/../crud/' . $t . '.php';
                    } else {
                      $href = '/../admin/manage.php?table=' . urlencode($t);
                    }
                    echo '<a class="btn btn-outline-secondary btn-sm" href="' . $href . '">' . htmlspecialchars($label) . '</a>';
                  }
                } else {
                  echo '<span class="muted-small">No se detectaron tablas.</span>';
                }
              ?>
              </div>
            </div>
          </div>
        </div>

      <!-- Alerts & Quick Access removed per request -->

    </div>
  </div>

<?php echo '</div>'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="" crossorigin="anonymous"></script>
<?php echo '</body></html>'; ?>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
