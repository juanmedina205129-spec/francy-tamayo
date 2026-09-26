<?php
require 'include/funciones.php';
require 'include/productos.php';

$productos = obtenerProductos();
incluirTemplates('header');
?>

<main class="cart-page">
    <section class="cart-hero"><div class="container"><span class="section-kicker">Tu selección</span><h1>Resumen del pedido</h1><p>Completa los detalles de tu encargo y prepararemos una propuesta hecha para ti.</p></div></section>
    <section class="cart-content container">
        <div class="cart-main">
            <div class="cart-panel"><div class="panel-title"><h2>Productos <span data-cart-items-label>(0)</span></h2><a href="<?php echo BASE_URL; ?>buscador.php">+ Agregar más</a></div><p class="cart-sync-notice" id="cart-sync-notice" role="status" hidden></p><div id="cart-items"></div></div>
            <form class="customer-form" id="customer-form">
                <div class="form-heading">
                    <div><span class="form-kicker">Pedido a tu medida</span><h2>Encargo y datos de contacto</h2></div>
                    <span class="saved-data" id="saved-data-status" aria-live="polite">Se guarda en este dispositivo</span>
                </div>
                <p class="form-intro">Cuéntanos qué imaginas. Usaremos estos datos únicamente para preparar y confirmar tu pedido por WhatsApp.</p>

                <fieldset class="form-section form-section-client">
                    <legend><span>1</span> Tus datos</legend>
                    <div class="form-grid"><label>Nombre completo *<input name="name" autocomplete="name" required placeholder="Tu nombre completo"></label><label>Teléfono / WhatsApp *<input name="phone" autocomplete="tel" required placeholder="+57 300 000 0000"></label></div>
                    <div class="form-grid"><label>Correo electrónico<input name="email" autocomplete="email" type="email" placeholder="correo@ejemplo.com"><small>Para enviarte el resumen, si lo deseas</small></label><label>Ciudad *<input name="city" autocomplete="address-level2" required placeholder="Palmira, Valle del Cauca"></label></div>
                </fieldset>

                <fieldset class="form-section form-section-commission">
                    <legend><span>2</span> Tu encargo personalizado</legend>
                    <div class="form-grid"><label>¿Qué deseas encargar? *<select name="commission_type" required><option value="">Seleccionar...</option><option>Retrato personalizado</option><option>Pintura original</option><option>Camiseta personalizada</option><option>Otro encargo</option></select></label><label>Fecha ideal de entrega<input name="desired_date" type="date" min="<?= date('Y-m-d') ?>"></label></div>
                    <div class="form-grid"><label>Tamaño o talla<select name="size"><option value="">Aún no lo sé</option><option>Pequeño</option><option>Mediano</option><option>Grande</option><option>Talla S</option><option>Talla M</option><option>Talla L</option><option>Talla XL</option></select></label><label>Presupuesto aproximado<select name="budget"><option value="">Prefiero recibir cotización</option><option>Hasta $150.000</option><option>$150.000 — $300.000</option><option>$300.000 — $500.000</option><option>Más de $500.000</option></select></label></div>
                    <label>Idea, estilo y detalles *<textarea name="commission_details" rows="4" required placeholder="Ej.: retrato de mi mascota en acuarela, con fondo de flores y una dedicatoria."></textarea><small>Puedes enviarnos fotos de referencia por WhatsApp al confirmar.</small></label>
                </fieldset>

                <fieldset class="form-section form-section-delivery">
                    <legend><span>3</span> Entrega</legend>
                    <div class="form-grid"><label>Método de entrega *<select name="delivery" required><option value="">Seleccionar...</option><option>Envío a domicilio</option><option>Recogida acordada</option></select></label><label>Dirección o punto de encuentro *<input name="address" autocomplete="street-address" required placeholder="Barrio, dirección o punto de encuentro"></label></div>
                    <label>Indicaciones adicionales<textarea name="notes" rows="3" placeholder="Horario preferido, dedicatoria, referencia para la entrega…"></textarea></label>
                </fieldset>
                <label class="privacy-check"><input name="save_data" type="checkbox" checked> <span>Guardar mis datos para facilitar futuros encargos en este dispositivo.</span></label>
            </form>
        </div>
        <aside class="order-summary"><h2>Total del pedido</h2><div id="order-lines"></div><div class="totals"><p><span>Subtotal</span><strong data-cart-subtotal>$0</strong></p><p><span>Envío estimado</span><strong>Por confirmar</strong></p><p class="total"><span>Total estimado</span><strong data-cart-total>$0</strong></p></div><p class="whatsapp-note">◌ El pago se coordina por WhatsApp. Te escribimos para confirmar disponibilidad y forma de pago.</p><button class="confirm-order" type="submit" form="customer-form">✓ Confirmar pedido</button><a class="continue-shopping" href="<?php echo BASE_URL; ?>buscador.php">‹ Seguir comprando</a><div class="trust-list"><span>▧ Envío seguro</span><span>◇ Embalaje</span><span>◌ WhatsApp</span><span>✓ Garantía</span></div><p class="order-feedback" id="order-feedback" role="status"></p><div class="order-confirmation" id="order-confirmation" hidden></div></aside>
    </section>
</main>

<script>
window.FRANCY_PRODUCTS = <?= json_encode(productosParaJavascript($productos), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG); ?>;
</script>

<?php include 'include/templates/footer.php'; ?>
