<?php
require 'include/funciones.php';
require 'include/productos.php';

$productos = obtenerProductos('pinturas');
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
                <div class="filter-list">
                    <button class="filter active">Todos</button>
                    <button class="filter">Disponible</button>
                    <button class="filter">Encargo</button>
                    <button class="filter">Agotado</button>
                </div>
                <div class="store-count">
                    <span><?= count($productos) ?> productos</span>
                    <strong>Destacados</strong>
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
