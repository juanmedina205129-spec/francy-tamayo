<?php
require 'include/funciones.php';
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
                    <span>6 productos</span>
                    <strong>Destacados</strong>
                </div>
            </div>

            <div class="product-grid category-products">
                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image9.png" alt="Retrato golden retriever">
                    </div>
                    <div class="product-body">
                        <span>Pintura al oleo</span>
                        <h3>Retrato Golden Retriever</h3>
                        <p class="rating">★★★★☆ 4.9</p>
                        <div class="product-footer">
                            <strong>$280.000</strong>
                            <button class="add-to-cart" data-product="Retrato Golden Retriever" data-category="Pintura al óleo" data-price="280000" data-image="assets/imagenes/img/image9.png">Añadir</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image10.png" alt="Perro en acuarela">
                    </div>
                    <div class="product-body">
                        <span>Acuarela</span>
                        <h3>Perro en acuarela</h3>
                        <p class="rating">★★★★☆ 4.8</p>
                        <div class="product-footer">
                            <strong>$195.000</strong>
                            <button class="add-to-cart" data-product="Perro en acuarela" data-category="Acuarela" data-price="195000" data-image="assets/imagenes/img/image10.png">Añadir</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge custom">Agotado</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image12.png" alt="Dos gatos al oleo">
                    </div>
                    <div class="product-body">
                        <span>Pintura al oleo</span>
                        <h3>Dos gatitos - oleo</h3>
                        <p class="rating">★★★★☆ 4.9</p>
                        <div class="product-footer">
                            <strong>$340.000</strong>
                            <button class="add-to-cart" data-product="Dos gatitos - óleo" data-category="Pintura al óleo" data-price="340000" data-image="assets/imagenes/img/image12.png" disabled>Agotado</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image13.png" alt="Ave colorida">
                    </div>
                    <div class="product-body">
                        <span>Acuarela</span>
                        <h3>Ave colorida - acuarela</h3>
                        <p class="rating">★★★★☆ 4.8</p>
                        <div class="product-footer">
                            <strong>$175.000</strong>
                            <button class="add-to-cart" data-product="Ave colorida - acuarela" data-category="Acuarela" data-price="175000" data-image="assets/imagenes/img/image13.png">Añadir</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image14.png" alt="Martin pescador">
                    </div>
                    <div class="product-body">
                        <span>Acuarela</span>
                        <h3>Martin pescador</h3>
                        <p class="rating">★★★★☆ 4.7</p>
                        <div class="product-footer">
                            <strong>$160.000</strong>
                            <button class="add-to-cart" data-product="Martín pescador" data-category="Acuarela" data-price="160000" data-image="assets/imagenes/img/image14.png">Añadir</button>
                        </div>
                    </div>
                </article>

                <article class="product-card">
                    <div class="product-image">
                        <span class="badge available">Disponible</span>
                        <img src="<?php echo BASE_URL; ?>assets/imagenes/img/image15.png" alt="Perro blanco al oleo">
                    </div>
                    <div class="product-body">
                        <span>Pintura al oleo</span>
                        <h3>Perro blanco - oleo</h3>
                        <p class="rating">★★★★☆ 4.8</p>
                        <div class="product-footer">
                            <strong>$260.000</strong>
                            <button class="add-to-cart" data-product="Perro blanco - óleo" data-category="Pintura al óleo" data-price="260000" data-image="assets/imagenes/img/image15.png">Añadir</button>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
