<?php
require 'include/funciones.php';
require 'include/productos.php';

$estadoFiltro = $_GET['estado'] ?? '';
$estadosValidos = ['disponible', 'encargo', 'agotado'];
if (!in_array($estadoFiltro, $estadosValidos, true)) $estadoFiltro = '';
$productos = obtenerProductos('pinturas', true, $estadoFiltro ?: null);
incluirTemplates('header');
?>

<main class="category-page category-paintings">
    <section class="category-hero">
        <div class="container category-hero-content">
            <span class="category-pill">Coleccion de arte</span>
            <h1>Pinturas</h1>
            <p>Oleo y acuarela sobre lienzo. Cada pieza es unica, hecha a mano y firmada por la artista.</p>
        </div>
    </section>

    <section class="category-store">
        <div class="container">
            <div class="category-note">
                <span class="note-icon">01</span>
                <p>Nuestra coleccion de pinturas incluye obras al oleo y acuarela sobre lienzo de alta calidad. Cada pieza es original, unica y viene firmada por la artista.</p>
            </div>

            <div class="store-toolbar">
                <form class="filter-list" method="GET" aria-label="Filtrar pinturas por disponibilidad">
                    <button class="filter <?= $estadoFiltro === '' ? 'active' : '' ?>" type="submit" name="estado" value="">Todos</button>
                    <button class="filter <?= $estadoFiltro === 'disponible' ? 'active' : '' ?>" type="submit" name="estado" value="disponible">Disponible</button>
                    <button class="filter <?= $estadoFiltro === 'encargo' ? 'active' : '' ?>" type="submit" name="estado" value="encargo">Encargo</button>
                    <button class="filter <?= $estadoFiltro === 'agotado' ? 'active' : '' ?>" type="submit" name="estado" value="agotado">Agotado</button>
                </form>
                <div class="store-count">
                    <span><?= count($productos) ?> productos</span>
                    <strong><?= $estadoFiltro ? ucfirst($estadoFiltro) : 'Todos' ?></strong>
                </div>
            </div>

            <?php if ($productos): ?>
                <div class="product-grid category-products">
                    <?php foreach ($productos as $producto): ?>
                        <?php renderizarTarjetaProducto($producto); ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="search-empty">No hay pinturas con este filtro por ahora. Prueba con otra disponibilidad.</div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
