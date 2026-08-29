<?php
require 'include/funciones.php';
incluirTemplates('header');
?>

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
                <div class="filter-list gold-filters">
                    <button class="filter active">Todos</button>
                    <button class="filter">Disponible</button>
                    <button class="filter">Encargo</button>
                    <button class="filter">Agotado</button>
                </div>
                <div class="store-count">
                    <span>4 productos</span>
                    <strong>Destacados</strong>
                </div>
            </div>

            <div class="product-grid category-products">
                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image11.png" alt="Camiseta perro acuarela">
                    </div>
                    <div class="product-body">
                        <span>Camiseta</span>
                        <h3>Camiseta Perro Acuarela</h3>
                        <p class="rating">★★★★☆ 4.7</p>
                        <div class="product-footer">
                            <strong>$85.000</strong>
                            <button class="add-to-cart" data-product="Camiseta Perro Acuarela" data-category="Camiseta" data-price="85000" data-image="assets/imagenes/img/image11.png">Añadir</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image8.png" alt="Camiseta gato minimalista">
                    </div>
                    <div class="product-body">
                        <span>Camiseta</span>
                        <h3>Camiseta Gato Minimalista</h3>
                        <p class="rating">★★★★☆ 4.6</p>
                        <div class="product-footer">
                            <strong>$75.000</strong>
                            <button class="add-to-cart" data-product="Camiseta Gato Minimalista" data-category="Camiseta" data-price="75000" data-image="assets/imagenes/img/image8.png">Añadir</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image17.png" alt="Camiseta tigre estampado">
                    </div>
                    <div class="product-body">
                        <span>Camiseta</span>
                        <h3>Camiseta Tigre Estampado</h3>
                        <p class="rating">★★★★☆ 4.8</p>
                        <div class="product-footer">
                            <strong>$90.000</strong>
                            <button class="add-to-cart" data-product="Camiseta Tigre Estampado" data-category="Camiseta" data-price="90000" data-image="assets/imagenes/img/image17.png">Añadir</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge custom">Por encargo</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image7.png" alt="Camiseta mascota personalizada">
                    </div>
                    <div class="product-body">
                        <span>Camiseta</span>
                        <h3>Camiseta Mascota Personalizada</h3>
                        <p class="rating">★★★★☆ 4.9</p>
                        <div class="product-footer">
                            <strong>$110.000</strong>
                            <button class="add-to-cart" data-product="Camiseta Mascota Personalizada" data-category="Camiseta por encargo" data-price="110000" data-image="assets/imagenes/img/image7.png">Añadir</button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
