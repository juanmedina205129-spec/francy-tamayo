<?php
$scripts = [ 'Contact']; // global + específico
// include 'include/templates/header.php'

require 'include/funciones.php';
incluirTemplates('header');
?>

<section class="contacto container">

<h2>CONTÁCTANOS</h2>
<p>¿Quieres hacer un pedido o preguntar algo?</p>

<form class="formulario">

<div class="fila">

<div class="campo">
<label>Nombre</label>
<input type="text" placeholder="Tu nombre" id="nombre">
</div>

<div class="campo">
<label>Apellido</label>
<input type="text" placeholder="Tu apellido" id="apellido">
</div>

</div>


<div class="fila">

<div class="campo">
<label>Celular</label>
<input type="text" placeholder="3001234567" id="celular">
</div>

<div class="campo">
<label>Email</label>
<input type="email" placeholder="correo@email.com" id="email">
</div>

</div>


<div class="campo">
<label>Mensaje</label>
<textarea rows="5" placeholder="Escribe tu mensaje..." id="mensaje"></textarea>
</div>

<button class="btn-enviar">ENVIAR MENSAJE</button>

</form>
<!-- PEGA ESTO DEBAJO DEL FORMULARIO EN contacto.html -->

<section class="tabs-section container">

    <div class="tabs">

        <button class="tab-btn active" data-tab="tab1">✨ Nosotros</button>
        <button class="tab-btn" data-tab="tab2">🍽 Servicios</button>
        <button class="tab-btn" data-tab="tab3">📞 Contacto</button>

    </div>

    <div class="contenido-tabs">

        <div id="tab1" class="tab-content active">
            <h3>Una experiencia diferente</h3>
            <p>
                En Ketzal mezclamos sabor, atención y ambiente moderno
                para que cada visita sea especial.
            </p>
        </div>

        <div id="tab2" class="tab-content">
            <h3>Lo que ofrecemos</h3>
            <p>
                Desayunos, almuerzos, combos premium, pedidos especiales
                y servicio rápido.
            </p>
        </div>

        <div id="tab3" class="tab-content">
            <h3>Habla con nosotros</h3>
            <p>
                Medellín, Colombia · WhatsApp 304 392 8321 · @ketzal
            </p>
        </div>

    </div>

</section>
</section>



<section class="menu container">

<h2>HABLEMOS</h2>

<div class="cards">

<div class="card neon-green">
<div class="card-body">
<h3>WhatsApp</h3>
<p>304 392 8321</p>
</div>
</div>

<div class="card">
<div class="card-body">
<h3>Instagram</h3>
<p>@ketzal</p>
</div>
</div>

<div class="card neon-red">
<div class="card-body">
<h3>Ubicación</h3>
<p>Medellín, Colombia</p>
</div>
</div>

</div>

</section>

<?php
include 'include/templates/footer.php'
?>
