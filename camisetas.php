<?php
require 'include/funciones.php';
require 'include/productos.php';

$estadoFiltro = $_GET['estado'] ?? '';
$estadosValidos = ['disponible', 'encargo', 'agotado'];
if (!in_array($estadoFiltro, $estadosValidos, true)) $estadoFiltro = '';
$productos = obtenerProductos('camisetas', true, $estadoFiltro ?: null);
incluirTemplates('header');
?>

/* 
    Plantilla para la pagina de categoria de camisetas
*/

<main class="category-page category-shirts">
    <section class="category-hero">
        <div class="container category-hero-content">
            <span class="category-pill accent-gold">Ropa artesanal</span>
            <h1>Camisetas</h1>
            <p>Diseños de animales en algodon premium. Originales, comodos y con opcion de personalizacion.</p>
        </div>
    </section>

    <section class="category-store">
        <div class="container">
            <div class="category-note note-action">
                <span class="note-icon accent-gold">03</span>
                <p>Nuestras camisetas combinan diseño artistico con calidad de prenda. Disponibles en diseños fijos del catalogo o completamente personalizadas.</p>
                <a href="<?php echo BASE_URL; ?>contacto.php" class="outline-action">Solicitar encargo</a>
            </div>

            <div class="store-toolbar">
                <form class="filter-list gold-filters" method="GET" aria-label="Filtrar camisetas por disponibilidad">
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

            <div class="product-grid category-products">
                <?php foreach ($productos as $producto): ?>
                    <?php renderizarTarjetaProducto($producto); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
