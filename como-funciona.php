<?php
require 'include/funciones.php';
incluirTemplates('header');
?>

<main class="guide-page">
    <section class="guide-hero">
        <div class="container">
            <span class="section-kicker">Guía de compra</span>
            <h1>¿Cómo funciona?</h1>
            <p>Todo lo que necesitas saber para elegir, encargar y recibir una pieza de Nido de Arte.</p>
        </div>
    </section>

    <section class="guide-content container">
        <div class="guide-steps">
            <article><span>01</span><h2>Elige tu pieza</h2><p>Explora pinturas, retratos y camisetas. Puedes añadir un producto al carrito o solicitar un diseño a medida.</p><a href="<?php echo BASE_URL; ?>pinturas.php">Ver catálogo →</a></article>
            <article><span>02</span><h2>Revisa el pedido</h2><p>En el carrito puedes ajustar cantidades, completar tus datos y elegir si recoges tu pedido o deseas envío.</p><a href="<?php echo BASE_URL; ?>carrito.php">Ir al carrito →</a></article>
            <article><span>03</span><h2>Confirmamos contigo</h2><p>El pago se coordina por WhatsApp. Confirmaremos disponibilidad, detalles de personalización y forma de pago.</p><a target="_blank" rel="noopener" href="https://wa.me/573184597719">Hablar por WhatsApp →</a></article>
            <article><span>04</span><h2>Apruebas el boceto y sigues el avance</h2><p>Antes de pintar te mostramos un boceto para que lo apruebes o pidas cambios. Después subimos fotos del avance, que puedes ver con tu código hasta la entrega (10 a 20 días hábiles según el pedido).</p><a href="<?php echo BASE_URL; ?>seguimiento.php">Seguimiento de pedidos →</a></article>
        </div>

        <div class="guide-info-grid">
            <article><h2>Pagos</h2><p>La forma de pago se acuerda directamente por WhatsApp antes de iniciar la producción o despacho.</p></article>
            <article><h2>Envíos y entregas</h2><p>Coordinamos envío seguro o recogida. El costo y fecha final se confirman según tu ubicación.</p></article>
            <article><h2>Cupos limitados</h2><p>Cada pieza se hace a mano, así que aceptamos pocos encargos a la vez. Si la agenda está llena, el formulario lo indica y puedes pedirnos por WhatsApp que te avisemos.</p></article>
            <article><h2>¿Tienes dudas?</h2><p>Cuéntanos tu idea, comparte una foto de referencia y te orientamos sin compromiso.</p></article>
        </div>

        <section class="faq-section">
            <span class="section-kicker">Resolvemos tus dudas</span><h2>Preguntas frecuentes</h2>
            <details><summary>¿Cómo solicito un producto personalizado?</summary><p>Elige un retrato o camiseta por encargo y agrégalo al carrito, o usa el <a href="<?php echo BASE_URL; ?>contacto.php">formulario de encargo</a>. En ambos puedes adjuntar la foto de tu mascota y tus indicaciones.</p></details>
            <details><summary>¿Cuánto tarda un pedido?</summary><p>Los productos disponibles se coordinan según el envío. Los encargos toman entre 10 y 20 días hábiles.</p></details>
            <details><summary>¿Cómo sé en qué va mi pedido?</summary><p>Al confirmar recibes un código como NA-7K3M9Q. Escríbelo junto con tu teléfono en <a href="<?php echo BASE_URL; ?>seguimiento.php">Seguimiento de pedidos</a> para ver su estado, el boceto y las fotos del avance.</p></details>
            <details><summary>¿Puedo pedirlo como regalo?</summary><p>Sí. Marca «¿Es un regalo?» al hacer el pedido, dinos para quién es y, si quieres, escribe el mensaje de la tarjeta que acompañará la pieza.</p></details>
            <details><summary>¿Las reseñas son reales?</summary><p>Sí. Solo puede dejar una reseña quien hizo un pedido y cuando este ya está finalizado, desde la página de seguimiento con su código y su teléfono. Hay una sola por pedido. Las valoraciones del catálogo se calculan con esas reseñas.</p></details>
            <details><summary>¿Qué pasa si la agenda está llena?</summary><p>Trabajamos un número limitado de encargos a la vez. Cuando no quedan cupos, el formulario se cierra temporalmente; escríbenos por WhatsApp y te avisamos apenas se libere uno.</p></details>
            <details><summary>¿Puedo cambiar la cantidad de un producto?</summary><p>Sí. Desde el carrito puedes aumentar, disminuir o eliminar productos antes de confirmar.</p></details>
        </section>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
