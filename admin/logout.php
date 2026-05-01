<?php
require __DIR__ . '/auth.php';
logout();
header('Location: login.php');
exit;
?>