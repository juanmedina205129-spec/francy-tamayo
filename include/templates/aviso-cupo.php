<?php
// Aviso del cupo de encargos sobre el formulario. Espera $cupo = estadoCupoPublico()
// (null cuando no hay base de datos: en ese caso no se muestra nada).
// Con CUPO_BLOQUEA_PEDIDOS desactivado solo se muestra una nota fija y el pedido se envía igual.
if (!CUPO_BLOQUEA_PEDIDOS) {
    ?>
    <p class="cupo-nota"><span aria-hidden="true">ⓘ</span> Aceptamos un número limitado de encargos a la vez. Si la agenda está llena cuando recibamos tu pedido, te avisaremos por WhatsApp para acordar contigo la fecha.</p>
    <?php
    return;
}
if (empty($cupo)) {
    return;
}
?>
<?php if ($cupo['lleno']): ?>
    <div class="cupo-aviso is-full" role="status">
        <span class="cupo-aviso-icon" aria-hidden="true">◷</span>
        <div>
            <strong>Agenda completa</strong>
            <p><?= htmlspecialchars(mensajeCupoLleno($cupo)) ?></p>
            <a target="_blank" rel="noopener" href="https://wa.me/573184597719?text=<?= rawurlencode('Hola, quiero que me avisen cuando haya cupo para un encargo.') ?>">Avísenme por WhatsApp ↗</a>
        </div>
    </div>
<?php else: ?>
    <div class="cupo-aviso" role="status">
        <span class="cupo-aviso-icon" aria-hidden="true">✦</span>
        <p><strong><?= $cupo['disponibles'] === 1 ? 'Queda 1 cupo de encargo disponible' : 'Quedan ' . (int) $cupo['disponibles'] . ' cupos de encargo disponibles' ?>.</strong> Trabajamos pocas piezas a la vez para dedicarle tiempo a cada una.</p>
    </div>
<?php endif; ?>
