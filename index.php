<?php
require __DIR__ . '/include/funciones.php';
require __DIR__ . '/include/productos.php';
require __DIR__ . '/include/pedidos.php';

$productosDestacados = obtenerProductosDestacados(3);

// Cifras del hero: salen de las reseñas de clientes y del cupo de encargos, nunca se escriben a mano.
$resumenResenas = obtenerResumenResenas();
$resenasRecientes = obtenerResenasRecientes(3);
$cupo = estadoCupoPublico();

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
                    <a href="#catalogo" class="hero-btn hero-btn-primary">Ver catálogo</a>
                    <a href="#personalizar" class="hero-btn hero-btn-outline">Encargar personalizado</a>
                </div>

                <div class="hero-stats">
                    <div>
                        <strong><?= $resumenResenas['promedio'] !== null ? number_format($resumenResenas['promedio'], 1, ',', '') . ' ★' : '—' ?></strong>
                        <span>valoración promedio</span>
                    </div>
                    <div>
                        <strong><?= $resumenResenas['total'] ?></strong>
                        <span><?= $resumenResenas['total'] === 1 ? 'reseña verificada' : 'reseñas verificadas' ?></span>
                    </div>
                    <div>
                        <?php if (!$cupo): ?>
                            <strong>—</strong>
                            <span>cupos de encargo libres</span>
                        <?php elseif ($cupo['lleno']): ?>
                            <strong>Llena</strong>
                            <span>agenda de encargos</span>
                        <?php else: ?>
                            <strong><?= $cupo['disponibles'] ?></strong>
                            <span><?= $cupo['disponibles'] === 1 ? 'cupo de encargo libre' : 'cupos de encargo libres' ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="home-section" id="catalogo">
        <div class="container">
            <span class="section-kicker">Explora</span>
            <h2>¿Qué buscas?</h2>

            <div class="category-grid">
                <article class="category-card" id="pinturas">
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-golondrina.png" alt="Pintura artística de golondrina">
                    <div class="category-body">
                        <span class="category-icon">01</span>
                        <h3>Pinturas</h3>
                        <p>Obras decorativas sobre lienzo, inspiradas en fauna, naturaleza y color.</p>
                        <a href="<?php echo BASE_URL; ?>pinturas.php">Explorar</a>
                    </div>
                </article>

                <article class="category-card" id="retratos">
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-buho.png" alt="Retrato artístico de búho">
                    <div class="category-body">
                        <span class="category-icon">02</span>
                        <h3>Retratos a pedido</h3>
                        <p>Retratos de mascotas y animales hechos desde una foto de referencia.</p>
                        <a href="<?php echo BASE_URL; ?>retratos.php">Explorar</a>
                    </div>
                </article>

                <article class="category-card" id="camisetas">
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/estuche-azulejo-2.png" alt="Producto con ilustración de ave">
                    <div class="category-body">
                        <span class="category-icon">03</span>
                        <h3>Camisetas únicas</h3>
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
                    <span class="section-kicker">Selección</span>
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
                    <h2>¿Cómo funciona?</h2>
                </div>
                <a href="<?php echo BASE_URL; ?>como-funciona.php" class="section-link">Ver guía completa</a>
            </div>

            <div class="steps-grid">
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-golondrina.png" alt="Ilustración de golondrina para elegir tu pieza">
                    <span>01</span>
                    <h3>Elige o encarga</h3>
                    <p>Navega el catálogo o solicita un diseño completamente personalizado.</p>
                </article>
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/estuche-azulejo.png" alt="Ilustración de ave para cotización personalizada">
                    <span>02</span>
                    <h3>Cotización</h3>
                    <p>Te contactamos por WhatsApp para confirmar detalles, tamaño y precio final.</p>
                </article>
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cuadro-buho.png" alt="Ilustración de búho para la aprobación del boceto">
                    <span>03</span>
                    <h3>Apruebas el boceto</h3>
                    <p>Antes de pintar te mostramos un boceto. Lo apruebas o pides cambios, y sigues el avance con fotos.</p>
                </article>
                <article>
                    <img src="<?php echo BASE_URL; ?>assets/imagenes/productos/cojin-jilguero.png" alt="Ilustración de jilguero para la entrega o el regalo">
                    <span>04</span>
                    <h3>Entrega o regalo</h3>
                    <p>Recibes tu pieza bien embalada o la enviamos como regalo, con tarjeta y dedicatoria.</p>
                </article>
            </div>
        </div>
    </section>

    <?php if ($resenasRecientes): ?>
        <section class="home-section reviews-section" id="resenas">
            <div class="container">
                <div class="section-heading-row">
                    <div>
                        <span class="section-kicker">Reseñas verificadas</span>
                        <h2>Lo que dicen nuestros clientes</h2>
                    </div>
                    <p class="reviews-summary"><strong><?= number_format($resumenResenas['promedio'], 1, ',', '') ?></strong> <span aria-hidden="true"><?= estrellasTexto($resumenResenas['promedio']) ?></span> · <?= $resumenResenas['total'] ?> <?= $resumenResenas['total'] === 1 ? 'reseña' : 'reseñas' ?></p>
                </div>

                <div class="reviews-grid">
                    <?php foreach ($resenasRecientes as $resena): ?>
                        <article class="review-card">
                            <p class="review-stars" aria-label="<?= (int) $resena['estrellas'] ?> de 5 estrellas"><span aria-hidden="true"><?= estrellasTexto($resena['estrellas']) ?></span></p>
                            <blockquote><?= nl2br(htmlspecialchars($resena['comentario'])) ?></blockquote>
                            <footer>
                                <strong><?= htmlspecialchars($resena['nombre']) ?></strong>
                                <span><?= htmlspecialchars($resena['tipo_encargo']) ?> · <?= htmlspecialchars(date('d/m/Y', strtotime($resena['fecha']))) ?></span>
                            </footer>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="custom-order container" id="personalizar">
        <div>
            <span class="section-kicker">Especial para ti</span>
            <h2>¿Quieres el retrato de tu mascota?</h2>
            <p>Cuéntanos sobre tu animal favorito y creamos una obra única. Entrega estimada entre 10 y 20 días hábiles.</p>
            <div class="hero-actions">
                <a target="_blank" rel="noopener" href="https://wa.me/573184597719" class="hero-btn hero-btn-primary">Pedir por WhatsApp</a>
                <a href="<?php echo BASE_URL; ?>contacto.php" class="hero-btn hero-btn-light">Formulario de encargo</a>
            </div>
        </div>
    </section>
</main>

<?php
include 'include/templates/footer.php';
?>
