<?php
require 'include/funciones.php';
require 'include/productos.php';

$productos = obtenerProductos('retratos');
incluirTemplates('header');
?>

<main class="category-page category-portraits">
    <section class="category-hero">
        <div class="container category-hero-content">
            <span class="category-pill accent-red">Servicio especial</span>
            <h1>Retratos a pedido</h1>
            <p>Envianos la foto de tu mascota y creamos un retrato personalizado que captura su esencia.</p>
        </div>
    </section>

    <section class="category-store">
        <div class="container">
            <div class="category-note note-action">
                <span class="note-icon accent-red">02</span>
                <p>Los retratos personalizados son nuestra especialidad. Trabajamos con fotografias de alta calidad de tu mascota para crear una obra de arte unica.</p>
                <a href="<?php echo BASE_URL; ?>contacto.php" class="outline-action">Solicitar encargo</a>
            </div>

            <div class="store-toolbar">
                <div class="filter-list red-filters">
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

            <div class="product-grid category-products portraits-products">
                <?php foreach ($productos as $producto): ?>
                    <?php renderizarTarjetaProducto($producto); ?>
                <?php endforeach; ?>
            </div>

            <div class="category-cta">
                <div>
                    <h2>¿No encuentras lo que buscas?</h2>
                    <p>Todos nuestros retratos se hacen a pedido. Envianos la foto de tu mascota y te cotizamos sin compromiso.</p>
                </div>
                <div class="hero-actions">
                    <a href="https://wa.me/573184597719" class="hero-btn hero-btn-primary">WhatsApp</a>
                    <a href="<?php echo BASE_URL; ?>contacto.php" class="hero-btn hero-btn-light">Formulario</a>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
