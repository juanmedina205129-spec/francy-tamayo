<?php
$scripts = ['Contact'];
require 'include/funciones.php';
incluirTemplates('header');
?>

<main class="contact-page">
    <section class="contact-hero">
        <div class="container">
            <span class="section-kicker">Pedido a tu medida</span>
            <h1>Encargo personalizado</h1>
            <p>Cuéntanos qué imaginas y prepararemos una propuesta hecha especialmente para ti.</p>
        </div>
    </section>

    <section class="contact-content container">
        <form class="contact-form" id="contact-form">
            <div class="form-heading">
                <div>
                    <span class="form-kicker">Hablemos de tu idea</span>
                    <h2>Datos de contacto y encargo</h2>
                </div>
                <span class="contact-response">Respondemos por WhatsApp</span>
            </div>
            <p class="form-intro">Completa la información que tengas. Podrás enviarnos imágenes de referencia por WhatsApp al confirmar.</p>

            <fieldset class="form-section form-section-client">
                <legend><span>1</span> Tus datos</legend>
                <div class="form-grid">
                    <label>Nombre completo *<input name="name" autocomplete="name" required placeholder="Tu nombre completo"></label>
                    <label>Teléfono / WhatsApp *<input name="phone" autocomplete="tel" required placeholder="+57 300 000 0000"></label>
                </div>
                <div class="form-grid">
                    <label>Correo electrónico<input name="email" autocomplete="email" type="email" placeholder="correo@ejemplo.com"></label>
                    <label>Ciudad<input name="city" autocomplete="address-level2" placeholder="Palmira, Valle del Cauca"></label>
                </div>
            </fieldset>

            <fieldset class="form-section form-section-commission">
                <legend><span>2</span> Tu encargo personalizado</legend>
                <div class="form-grid">
                    <label>¿Qué deseas encargar? *<select name="commission_type" required><option value="">Seleccionar...</option><option>Retrato personalizado</option><option>Pintura original</option><option>Camiseta personalizada</option><option>Otro encargo</option></select></label>
                    <label>Fecha ideal de entrega<input name="desired_date" type="date"></label>
                </div>
                <div class="form-grid">
                    <label>Tamaño o talla<select name="size"><option value="">Aún no lo sé</option><option>Pequeño</option><option>Mediano</option><option>Grande</option><option>Talla S</option><option>Talla M</option><option>Talla L</option><option>Talla XL</option></select></label>
                    <label>Presupuesto aproximado<select name="budget"><option value="">Prefiero recibir cotización</option><option>Hasta $150.000</option><option>$150.000 — $300.000</option><option>$300.000 — $500.000</option><option>Más de $500.000</option></select></label>
                </div>
                <label>Idea, estilo y detalles *<textarea name="commission_details" rows="5" required placeholder="Ej.: retrato de mi mascota en acuarela, con fondo de flores y una dedicatoria."></textarea></label>
            </fieldset>

            <button class="contact-submit" type="submit">Enviar solicitud por WhatsApp</button>
            <p class="contact-feedback" id="contact-feedback" role="status"></p>
        </form>

        <aside class="contact-aside">
            <span class="contact-aside-icon" aria-hidden="true">✦</span>
            <h2>Una pieza hecha para ti</h2>
            <p>Revisaremos tu solicitud y confirmaremos disponibilidad, precio y tiempos antes de iniciar.</p>
            <ul>
                <li><span>1</span> Cuéntanos tu idea</li>
                <li><span>2</span> Recibe tu cotización</li>
                <li><span>3</span> Confirma por WhatsApp</li>
            </ul>
            <a target="_blank" rel="noopener" href="https://wa.me/573184597719">Hablar directamente por WhatsApp ↗</a>
        </aside>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
