
<!-- FOOTER -->
<footer class="footer">

<div class="footer-content container">

<div>
<h3>Francy Tamayo</h3>
<p>Ilustracion de vida silvestre, arte hecho a mano, pinturas originales, retratos de mascotas y camisetas unicas.</p>
</div>

<div>
<h4>Navegación</h4>
<a href="<?php echo BASE_URL; ?>index.php">Inicio</a>
<a href="<?php echo BASE_URL; ?>pinturas.php">Pinturas</a>
<a href="<?php echo BASE_URL; ?>retratos.php">Retratos</a>
<a href="<?php echo BASE_URL; ?>camisetas.php">Camisetas</a>
<a href="<?php echo BASE_URL; ?>como-funciona.php">Como funciona</a>
<a href="<?php echo BASE_URL; ?>contacto.php">Contacto</a>
</div>

<div>
<h4>Encuéntranos</h4>
<p>Palmira, Valle del Cauca</p>
<p>+57 318 459 7719</p>
<p>@francy.tamayo</p>
</div>

</div>

<div class="footer-bottom">
<?php echo date ('Y')?> Todos los derechos reservados
</div>

</footer>

<script src="<?php echo BASE_URL; ?>assets/JS/app.js"></script>

<?php if(isset($scripts) && is_array($scripts)): ?>
    <?php foreach($scripts as $script): ?>
        <script src="<?php echo BASE_URL. "assets/JS/$script.js"; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
