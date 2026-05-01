<?php
// Minimal tabs component: only Dashboard button as requested
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$current = basename($_SERVER['PHP_SELF']);
<<<<<<< HEAD
$dashHref = 'index.php';
$logoutHref = 'logout.php';
$active = ($current === basename($dashHref)) ? 'active' : '';
?>
<nav class="admin-tabs-wrap">
  <div class="container admin-tabs-container">
    <div class="admin-tabs-left">
      <ul class="admin-tabs">
        <li class="tab <?php echo $active; ?>"><a href="<?php echo $dashHref; ?>">Dashboard</a></li>
      </ul>
    </div>
    <div class="admin-tabs-right">
      <a href="<?php echo $logoutHref; ?>" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</a>
    </div>
=======
$dashHref = '/../admin/index.php';
$logoutHref = '/../admin/logout.php';
$active = ($current === basename(parse_url($dashHref, PHP_URL_PATH))) ? 'active' : '';
?>
<nav class="admin-tabs-wrap">
  <div class="container">
    <ul class="admin-tabs">
      <li class="tab <?php echo $active; ?>"><a href="<?php echo $dashHref; ?>">Dashboard</a></li>
      <li class="tab logout"><a href="<?php echo $logoutHref; ?>">Cerrar sesión</a></li>
    </ul>
>>>>>>> f8bb86c551ffde9d290751c388ec6e8b7868f4ca
  </div>
</nav>
