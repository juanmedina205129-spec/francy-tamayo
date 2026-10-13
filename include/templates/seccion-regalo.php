<?php
// Sección "¿Es un regalo?" de los formularios de encargo. Espera $numero (paso del formulario).
// Los campos solo se muestran y se exigen al marcar la casilla (assets/JS/app.js).
?>
<fieldset class="form-section form-section-gift" data-gift-section>
    <legend><span><?= (int) ($numero ?? 0) ?></span> ¿Es un regalo?</legend>
    <label class="gift-check"><input type="checkbox" name="is_gift" value="1"> <span>Sí, es para regalar: quiero que lleve una tarjeta con dedicatoria.</span></label>
    <div class="gift-fields" data-gift-fields hidden>
        <label>¿Para quién es? *<input name="gift_for" maxlength="120" placeholder="Ej.: Para Laura, mi hermana"></label>
        <label>Mensaje para la tarjeta<textarea name="gift_message" rows="3" maxlength="500" placeholder="Ej.: Feliz cumpleaños. Para que Lucas te acompañe siempre."></textarea><small>Máximo 500 caracteres. Va en la tarjeta que acompaña la pieza.</small></label>
    </div>
</fieldset>
