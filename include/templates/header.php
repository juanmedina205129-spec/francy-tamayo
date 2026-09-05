<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Francy Tamayo</title>
<link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/app.css?v=9">

<!-- jQuery  -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css">

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js"></script>


</head>

<body>

<header class="header">
<div class="container nav">

<div class="logo">Francy <span>Tamayo</span></div>

<?php $paginaActual = basename($_SERVER['PHP_SELF']); ?>
<nav>
<a href="<?php echo BASE_URL;?>index.php" class="<?php echo $paginaActual === 'index.php' ? 'active' : ''; ?>">Inicio</a>
<a href="<?php echo BASE_URL;?>pinturas.php" class="<?php echo $paginaActual === 'pinturas.php' ? 'active' : ''; ?>">Pinturas</a>
<a href="<?php echo BASE_URL;?>retratos.php" class="<?php echo $paginaActual === 'retratos.php' ? 'active' : ''; ?>">Retratos</a>
<a href="<?php echo BASE_URL;?>camisetas.php" class="<?php echo $paginaActual === 'camisetas.php' ? 'active' : ''; ?>">Camisetas</a>
<a href="<?php echo BASE_URL;?>como-funciona.php" class="<?php echo $paginaActual === 'como-funciona.php' ? 'active' : ''; ?>">Como funciona</a>
<a href="<?php echo BASE_URL;?>contacto.php">Contacto</a>
<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

<?php if (!empty($_SESSION['login'])): ?>

<!-- 🔐 Usuario logueado -->
<a href="<?= BASE_URL; ?>admin/index.php" class="nav-user">👤 <?= $_SESSION['usuario']; ?></a>

<a href="<?= BASE_URL; ?>admin/logout.php" class="btn-salir">
🚪 Salir
</a>

<?php else: ?>

<!-- 🔓 Usuario no logueado -->
<a href="<?= BASE_URL; ?>admin/login.php" class="btn-admin">
Iniciar sesión
</a>

<?php endif; ?>

</nav>

<div class="nav-tools" aria-label="Herramientas de compra">
<a href="<?php echo BASE_URL;?>buscador.php" class="nav-search" aria-label="Buscar productos">⌕</a>
<a href="<?php echo BASE_URL;?>carrito.php" class="nav-cart" aria-label="Ver carrito">♧<span class="cart-count" data-cart-count>0</span></a>
</div>
<a target="_blank" href="https://wa.me/573184597719" class="btn-nav">WhatsApp</a>

</div>


</header>
