<?php
// Campo opcional para adjuntar la foto de la mascota. La vista previa y la validación de
// tipo y tamaño están en assets/JS/app.js; guardar_pedido.php vuelve a validarla.
?>
<div class="photo-field" data-photo-field>
    <label><span>Foto de tu mascota o de referencia <span class="field-optional">(opcional)</span></span>
        <input type="file" name="reference_photo" accept="image/jpeg,image/png,image/webp,image/avif">
        <small>JPG, PNG, WEBP o AVIF de máximo 5 MB. Si tienes más fotos, envíalas después por WhatsApp.</small>
    </label>
    <p class="photo-error" data-photo-error role="alert" hidden></p>
    <div class="photo-preview" data-photo-preview hidden>
        <img alt="Vista previa de la foto seleccionada">
        <div>
            <strong data-photo-name></strong>
            <button type="button" data-photo-remove>Quitar foto</button>
        </div>
    </div>
    <details class="photo-guide">
        <summary>¿Qué foto funciona mejor?</summary>
        <ul>
            <li><strong>Buena luz:</strong> de día y cerca de una ventana, sin flash.</li>
            <li><strong>Ojos visibles:</strong> a la altura de la mascota y mirando hacia la cámara.</li>
            <li><strong>Nítida y completa:</strong> sin movimiento y sin orejas o patas recortadas.</li>
            <li><strong>Una mascota por foto:</strong> si son varias, cuéntanos y te pedimos una de cada una.</li>
        </ul>
    </details>
</div>
