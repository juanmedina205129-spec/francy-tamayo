<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Francy Tamayo</title>
<link rel="stylesheet" href="<?php echo BASE_URL;?>assets/css/app.css?v=15">

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
<nav class="main-nav" aria-label="Navegación principal">
<a href="<?php echo BASE_URL;?>index.php" class="<?php echo $paginaActual === 'index.php' ? 'active' : ''; ?>">Inicio</a>
<a href="<?php echo BASE_URL;?>pinturas.php" class="<?php echo $paginaActual === 'pinturas.php' ? 'active' : ''; ?>">Pinturas</a>
<a href="<?php echo BASE_URL;?>retratos.php" class="<?php echo $paginaActual === 'retratos.php' ? 'active' : ''; ?>">Retratos</a>
<a href="<?php echo BASE_URL;?>camisetas.php" class="<?php echo $paginaActual === 'camisetas.php' ? 'active' : ''; ?>">Camisetas</a>
<a href="<?php echo BASE_URL;?>como-funciona.php" class="<?php echo $paginaActual === 'como-funciona.php' ? 'active' : ''; ?>">Como funciona</a>
<a href="<?php echo BASE_URL;?>contacto.php">Contacto</a>
<?php iniciarSesion(); ?>

<?php if (!empty($_SESSION['login'])): ?>

<!-- 🔐 Usuario logueado -->
<div class="nav-session">
<a href="<?= BASE_URL; ?>admin/index.php" class="nav-user" title="Panel de <?= htmlspecialchars($_SESSION['usuario']); ?>"><span class="nav-user-icon" aria-hidden="true">👤</span><span class="nav-user-name"><?= htmlspecialchars($_SESSION['usuario']); ?></span></a>

<form class="nav-logout" method="POST" action="<?= BASE_URL; ?>admin/logout.php">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
<button type="submit" class="btn-salir">Cerrar sesión</button>
</form>
</div>

<?php else: ?>

<!-- 🔓 Usuario no logueado -->
<div class="nav-session"><a href="<?= BASE_URL; ?>admin/login.php" class="btn-admin">
Iniciar sesión
</a></div>

<?php endif; ?>

</nav>

<div class="nav-actions">
<div class="nav-tools" aria-label="Herramientas de compra">
<a href="<?php echo BASE_URL;?>buscador.php" class="nav-search <?php echo $paginaActual === 'buscador.php' ? 'active' : ''; ?>" aria-label="Buscar productos"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="m16 16 4 4"></path></svg><span class="nav-search-label">Buscar</span></a>
<a href="<?php echo BASE_URL;?>carrito.php" class="nav-cart <?php echo $paginaActual === 'carrito.php' ? 'active' : ''; ?>" aria-label="Ver carrito">♧<span class="cart-count" data-cart-count>0</span></a>
</div>
<a target="_blank" href="https://wa.me/573184597719" class="btn-nav">WhatsApp</a>
</div>

</div>


</header>
