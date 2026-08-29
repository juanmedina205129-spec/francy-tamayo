<?php
require 'include/funciones.php';
incluirTemplates('header');
?>

<main class="guide-page">
    <section class="guide-hero">
        <div class="container">
            <span class="section-kicker">Guía de compra</span>
            <h1>¿Cómo funciona?</h1>
            <p>Todo lo que necesitas saber para elegir, encargar y recibir una pieza de Francy Tamayo.</p>
        </div>
    </section>

    <section class="guide-content container">
        <div class="guide-steps">
            <article><span>01</span><h2>Elige tu pieza</h2><p>Explora pinturas, retratos y camisetas. Puedes añadir un producto al carrito o solicitar un diseño a medida.</p><a href="<?php echo BASE_URL; ?>pinturas.php">Ver catálogo →</a></article>
            <article><span>02</span><h2>Revisa el pedido</h2><p>En el carrito puedes ajustar cantidades, completar tus datos y elegir si recoges tu pedido o deseas envío.</p><a href="<?php echo BASE_URL; ?>carrito.php">Ir al carrito →</a></article>
            <article><span>03</span><h2>Confirmamos contigo</h2><p>El pago se coordina por WhatsApp. Confirmaremos disponibilidad, detalles de personalización y forma de pago.</p><a target="_blank" href="https://wa.me/573184597719">Hablar por WhatsApp →</a></article>
            <article><span>04</span><h2>Creamos y entregamos</h2><p>Las piezas por encargo se elaboran artesanalmente. El tiempo estimado es de 10 a 20 días hábiles según el pedido.</p></article>
        </div>

        <div class="guide-info-grid">
            <article><h2>Pagos</h2><p>La forma de pago se acuerda directamente por WhatsApp antes de iniciar la producción o despacho.</p></article>
            <article><h2>Envíos y entregas</h2><p>Coordinamos envío seguro o recogida. El costo y fecha final se confirman según tu ubicación.</p></article>
            <article><h2>¿Tienes dudas?</h2><p>Cuéntanos tu idea, comparte una foto de referencia y te orientamos sin compromiso.</p></article>
        </div>

        <section class="faq-section">
            <span class="section-kicker">Resolvemos tus dudas</span><h2>Preguntas frecuentes</h2>
            <details><summary>¿Cómo solicito un producto personalizado?</summary><p>Elige un retrato o camiseta por encargo, agrégalo al carrito o escríbenos por WhatsApp con una foto y tus indicaciones.</p></details>
            <details><summary>¿Cuánto tarda un pedido?</summary><p>Los productos disponibles se coordinan según el envío. Los encargos toman entre 10 y 20 días hábiles.</p></details>
            <details><summary>¿Puedo cambiar la cantidad de un producto?</summary><p>Sí. Desde el carrito puedes aumentar, disminuir o eliminar productos antes de confirmar.</p></details>
        </section>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
