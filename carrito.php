<?php
require 'include/funciones.php';
incluirTemplates('header');
?>

<main class="cart-page">
    <section class="cart-hero"><div class="container"><span class="section-kicker">Tu selección</span><h1>Resumen del pedido</h1></div></section>
    <section class="cart-content container">
        <div class="cart-main">
            <div class="cart-panel"><div class="panel-title"><h2>Productos <span data-cart-items-label>(0)</span></h2><a href="<?php echo BASE_URL; ?>buscador.php">+ Agregar más</a></div><div id="cart-items"></div></div>
            <form class="customer-form" id="customer-form">
                <h2>Datos del cliente</h2>
                <div class="form-grid"><label>Nombre completo *<input name="name" required placeholder="Tu nombre completo"></label><label>Teléfono / WhatsApp *<input name="phone" required placeholder="+57 300 000 0000"></label></div>
                <label>Correo electrónico<input name="email" type="email" placeholder="correo@ejemplo.com"><small>Opcional — para enviarte el resumen</small></label>
                <div class="form-grid"><label>Ciudad / Dirección *<input name="address" required placeholder="Palmira, Valle del Cauca"></label><label>Método de entrega *<select name="delivery" required><option value="">Seleccionar...</option><option>Envío a domicilio</option><option>Recogida acordada</option></select></label></div>
                <label>Observaciones<textarea name="notes" rows="4" placeholder="Instrucciones especiales, referencia de foto, dedicatoria, etc."></textarea></label>
            </form>
        </div>
        <aside class="order-summary"><h2>Total del pedido</h2><div id="order-lines"></div><div class="totals"><p><span>Subtotal</span><strong data-cart-subtotal>$0</strong></p><p><span>Envío estimado</span><strong>Por confirmar</strong></p><p class="total"><span>Total estimado</span><strong data-cart-total>$0</strong></p></div><p class="whatsapp-note">◌ El pago se coordina por WhatsApp. Te escribimos para confirmar disponibilidad y forma de pago.</p><button class="confirm-order" type="submit" form="customer-form">✓ Confirmar pedido</button><a class="continue-shopping" href="<?php echo BASE_URL; ?>buscador.php">‹ Seguir comprando</a><div class="trust-list"><span>▧ Envío seguro</span><span>◇ Embalaje</span><span>◌ WhatsApp</span><span>✓ Garantía</span></div><p class="order-feedback" id="order-feedback" role="status"></p></aside>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
