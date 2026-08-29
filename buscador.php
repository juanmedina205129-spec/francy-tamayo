<?php
require 'include/funciones.php';
incluirTemplates('header');
?>

<main class="search-page">
    <section class="search-hero">
        <div class="container">
            <span class="section-kicker">Encuentra tu pieza</span>
            <h1>Buscar en la tienda</h1>
            <form class="search-form" id="product-search-form">
                <label class="sr-only" for="product-search">Buscar productos</label>
                <input id="product-search" type="search" placeholder="Buscar pinturas, retratos, camisetas..." autocomplete="off">
                <button type="submit">Buscar</button>
            </form>
            <div class="popular-searches"><span>Búsquedas populares</span><button data-search-term="Pinturas al óleo">Pinturas al óleo</button><button data-search-term="Acuarela">Acuarela</button><button data-search-term="Retratos de mascotas">Retratos de mascotas</button><button data-search-term="Camisetas personalizadas">Camisetas personalizadas</button></div>
        </div>
    </section>
    <section class="search-results container" aria-live="polite">
        <p id="search-summary">Escribe un término o elige una búsqueda popular.</p>
        <div class="product-grid search-grid" id="search-results"></div>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
