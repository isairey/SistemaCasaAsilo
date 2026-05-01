<?php
require __DIR__ . '/auth.php';

$message = '';
<<<<<<< HEAD
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

=======
// CSRF token
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$csrf = $_SESSION['csrf'];

// allow optional redirect back URL via ?r=
// default to the admin index in the same folder (relative) so it works when the site is under /Pagina
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
$redirect = 'index.php';
if (!empty($_GET['r'])) {
  // very small sanitization: allow only relative internal paths
  $r = $_GET['r'];
  if (strpos($r, '/') === 0 && strpos($r, '..') === false) $redirect = $r;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  // validate csrf
  if (empty($_POST['csrf']) || !hash_equals($csrf, $_POST['csrf'])) {
    $message = 'Solicitud inválida (CSRF).';
  } else {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';
    if ($user === '' || $pass === '') $message = 'Usuario y contraseña son requeridos.';
    else {
      if (attempt_login($user, $pass)){
        header('Location: ' . $redirect);
        exit;
      } else {
        $message = 'Credenciales inválidas.';
      }
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Admin</title>
    <link rel="stylesheet" href="inc/admin-min.css">
    <link rel="stylesheet" href="inc/login.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="login-page">
  <div class="login-wrap">
  <div class="big-logo"><img src="../scr/resources/images/logo.png" alt="Logo" onerror="this.style.opacity=0.6;" /></div>
    <div class="card card-auth shadow-sm">
      <div class="mb-3">
        <div class="brand">Panel de administración - CasaBonsai</div>
      </div>
    <?php if($message): ?>
      <div class="alert alert-danger py-2" role="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf); ?>" />
      <div class="mb-3">
        <label class="form-label small">Usuario</label>
        <input name="user" class="form-control" required />
      </div>
      <div class="mb-3">
        <label class="form-label small">Contraseña</label>
        <input name="pass" type="password" class="form-control" required />
      < /div>
      <div class="d-flex justify-content-between align-items-center">
        <button class="btn btn-success" type="submit">Entrar</button>
        <a href="../index.html" class="text-success small">Volver al sitio</a>
      </div>
    </form>
    </div>
  </div>
</body>
</html>