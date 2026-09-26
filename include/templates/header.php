<!doctype html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Francy Tamayo</title>
<meta name="csrf-token" content="<?= htmlspecialchars(csrfToken()) ?>">
<meta name="base-url" content="<?= htmlspecialchars(BASE_URL) ?>">
<?php
// Hojas de estilo del sitio en orden de cascada (ver assets/css/README.md).
// Se enlazan por separado para que el navegador las descargue en paralelo;
// la versión se toma de la fecha de modificación de cada archivo.
$hojasEstilo = [
    'base/global.css',
    'layout/header.css',
    'layout/footer.css',
    'componentes/ui.css',
    'paginas/index.css',
    'paginas/contacto.css',
    'paginas/admin.css',
    'paginas/crear.css',
    'paginas/menu.css',
    'paginas/login.css',
    'paginas/tienda.css',
];
foreach ($hojasEstilo as $hoja): ?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/<?= $hoja ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/' . $hoja) ?>">
<?php endforeach; ?>

<?php if (isset($estilosExtra) && is_array($estilosExtra)): foreach ($estilosExtra as $href): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($href) ?>">
<?php endforeach; endif; ?>
<?php if (isset($scriptsExtraHead) && is_array($scriptsExtraHead)): foreach ($scriptsExtraHead as $src): ?>
<script src="<?= htmlspecialchars($src) ?>"></script>
<?php endforeach; endif; ?>

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
<a target="_blank" href="https://wa.me/573184597719" class="btn-nav" aria-label="Escríbenos por WhatsApp"><svg class="btn-nav-icon" viewBox="0 0 24 24" aria-hidden="true" width="18" height="18" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.4-.7-1.7-.8-.2-.1-.4-.1-.6.1-.2.2-.7.8-.8.9-.1.2-.3.2-.5.1-.2-.1-1-.4-1.9-1.2-.7-.6-1.2-1.4-1.3-1.6-.1-.2 0-.4.1-.5l.4-.4c.1-.1.2-.2.2-.4.1-.1 0-.3 0-.4-.1-.1-.6-1.4-.8-1.9-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9 0 1.1.8 2.2.9 2.4.1.2 1.6 2.5 4 3.5.6.2 1 .4 1.3.5.5.2 1 .1 1.4.1.4-.1 1.4-.6 1.6-1.1.2-.5.2-1 .1-1.1-.1-.1-.2-.2-.4-.3Z"></path></svg><span class="btn-nav-label">WhatsApp</span></a>
</div>

</div>


</header>
