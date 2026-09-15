
<?php
require __DIR__ . '/include/funciones.php';
require __DIR__ . '/include/productos.php';

$productosDestacados = obtenerProductosDestacados(3);

incluirTemplates('header');
?>

<main class="home-page">
    <section class="hero">
        <div class="overlay"></div>

        <div class="container hero-layout">
            <div class="hero-content">
                <span class="tagline">Arte hecho a mano · Pinturas · Retratos · Camisetas</span>
                <h1>Arte animal hecho a tu medida</h1>
                <p>Pinturas, retratos de mascotas y camisetas personalizadas en un solo lugar. Cada pieza nace del detalle, la paciencia y el amor por la naturaleza.</p>

                <div class="hero-actions">
                    <a href="#catalogo" class="hero-btn hero-btn-primary">Ver catalogo</a>
                    <a href="#personalizar" class="hero-btn hero-btn-outline">Encargar personalizado</a>
                </div>

                <div class="hero-stats">
                    <div>
                        <strong>+200</strong>
                        <span>obras creadas</span>
                    </div>
                    <div>
                        <strong>98%</strong>
                        <span>clientes felices</span>
                    </div>
                    <div>
                        <strong>5★</strong>
                        <span>valoracion</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section" id="catalogo">
        <div class="container">
            <span class="section-kicker">Explora</span>
            <h2>¿Que buscas?</h2>

            <div class="category-grid">
                <article class="category-card" id="pinturas">
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-golondrina.png" alt="Pintura artistica de golondrina">
                    <div class="category-body">
                        <span class="category-icon">01</span>
                        <h3>Pinturas</h3>
                        <p>Obras decorativas sobre lienzo, inspiradas en fauna, naturaleza y color.</p>
                        <a href="<?php echo BASE_URL; ?>pinturas.php">Explorar</a>
                    </div>
                </article>

                <article class="category-card" id="retratos">
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-buho.png" alt="Retrato artistico de buho">
                    <div class="category-body">
                        <span class="category-icon">02</span>
                        <h3>Retratos a pedido</h3>
                        <p>Retratos de mascotas y animales hechos desde una foto de referencia.</p>
                        <a href="<?php echo BASE_URL; ?>retratos.php">Explorar</a>
                    </div>
                </article>

                <article class="category-card" id="camisetas">
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/estuche-azulejo-2.png" alt="Producto con ilustracion de ave">
                    <div class="category-body">
                        <span class="category-icon">03</span>
                        <h3>Camisetas unicas</h3>
                        <p>Diseños aplicados en prendas para vestir arte original y personalizado.</p>
                        <a href="<?php echo BASE_URL; ?>camisetas.php">Explorar</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="home-section featured-section" id="productos">
        <div class="container">
            <div class="section-heading-row">
                <div>
                    <span class="section-kicker">Seleccion</span>
                    <h2>Productos destacados</h2>
                </div>
                <a href="#catalogo" class="section-link">Ver todos</a>
            </div>

            <div class="product-grid">
                <?php foreach ($productosDestacados as $producto): ?>
                    <?php renderizarTarjetaProducto($producto, true); ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="home-section process-section" id="funciona">
        <div class="container">
            <div class="section-heading-row">
                <div>
                    <span class="section-kicker">Proceso</span>
                    <h2>¿Como funciona?</h2>
                </div>
                <a href="#personalizar" class="section-link">Ver guia completa</a>
            </div>

            <div class="steps-grid">
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-golondrina.png" alt="Ilustración de golondrina para elegir tu pieza">
                    <span>01</span>
                    <h3>Elige o encarga</h3>
                    <p>Navega el catalogo o solicita un diseño completamente personalizado.</p>
                </article>
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/estuche-azulejo.png" alt="Ilustración de ave para cotización personalizada">
                    <span>02</span>
                    <h3>Cotizacion</h3>
                    <p>Te contactamos por WhatsApp para confirmar detalles, tamaño y precio final.</p>
                </article>
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-buho.png" alt="Ilustración de búho durante la creación de la pieza">
                    <span>03</span>
                    <h3>Creamos tu pieza</h3>
                    <p>La obra se realiza con dedicacion artesanal y revision de calidad.</p>
                </article>
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cojin-jilguero.png" alt="Ilustración de jilguero para entrega segura">
                    <span>04</span>
                    <h3>Entrega segura</h3>
                    <p>Recibes tu pieza lista para disfrutar, decorar o regalar.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="custom-order container" id="personalizar">
        <div>
            <span class="section-kicker">Especial para ti</span>
            <h2>¿Quieres el retrato de tu mascota?</h2>
            <p>Cuentanos sobre tu animal favorito y creamos una obra unica. Entrega estimada entre 10 y 20 dias habiles.</p>
            <div class="hero-actions">
                <a target="_blank" href="https://wa.me/573184597719" class="hero-btn hero-btn-primary">Pedir por WhatsApp</a>
                <a href="<?php echo BASE_URL; ?>contacto.php" class="hero-btn hero-btn-light">Formulario de encargo</a>
            </div>
        </div>
    </section>
</main>

<?php
include 'include/templates/footer.php';
?>
