<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$title = $title ?? 'Casa Bonsái - Panel Administrativo';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link rel="icon" href="/scr/resources/images/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/scr/styles/styles.css" rel="stylesheet">
    <link href="/admin/inc/admin.css" rel="stylesheet">
    <style>body{font-family:'Nunito Sans', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;}</style>
</head>
<body>
  <main>
    <div class="container-fluid bg-light pt-4 pb-5">
      <div class="container py-4">

