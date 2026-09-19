<?php
require_once __DIR__ . '/../include/funciones.php';
require_once __DIR__ . '/../include/pedidos.php';
auth();

$pedidos = [];
$error = '';
try {
    $db = conectarDB();
    prepararTablaPedidos($db);
    $pedidos = $db->query('SELECT * FROM pedidos ORDER BY fecha_creacion DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $exception) {
    $error = 'No fue posible consultar los encargos. Revisa la conexión con la base de datos.';
}

incluirTemplates('header');
?>

<main class="admin-orders-page">
    <section class="container admin-orders" aria-labelledby="orders-title">
        <div class="admin-orders-heading">
            <div>
                <span class="admin-eyebrow">Administración · Pedidos</span>
                <h1 id="orders-title">Encargos personalizados</h1>
                <p>Consulta las solicitudes enviadas desde el carrito y sus datos de contacto.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-outline-link">← Panel de administración</a>
        </div>

        <?php if ($error): ?>
            <p class="admin-orders-message is-error"><?= htmlspecialchars($error) ?></p>
        <?php elseif (!$pedidos): ?>
            <div class="admin-orders-empty"><span>◌</span><h2>Aún no hay encargos</h2><p>Los pedidos confirmados desde el carrito aparecerán aquí.</p></div>
        <?php else: ?>
            <div class="admin-orders-list">
                <?php foreach ($pedidos as $pedido): ?>
                    <?php $productos = json_decode($pedido['productos'], true) ?: []; ?>
                    <article class="admin-order-card">
                        <header><div><span class="admin-order-id">Pedido #<?= (int) $pedido['id'] ?></span><h2><?= htmlspecialchars($pedido['nombre']) ?></h2><p><?= htmlspecialchars(date('d/m/Y · H:i', strtotime($pedido['fecha_creacion']))) ?></p></div><span class="admin-order-status"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $pedido['estado']))) ?></span></header>
                        <div class="admin-order-grid"><section><h3>Contacto</h3><p><strong>WhatsApp:</strong> <?= htmlspecialchars($pedido['telefono']) ?></p><?php if ($pedido['email']): ?><p><strong>Correo:</strong> <?= htmlspecialchars($pedido['email']) ?></p><?php endif; ?><p><strong>Ciudad:</strong> <?= htmlspecialchars($pedido['ciudad']) ?></p></section><section><h3>Encargo personalizado</h3><p><strong>Tipo:</strong> <?= htmlspecialchars($pedido['tipo_encargo']) ?></p><p><strong>Tamaño:</strong> <?= htmlspecialchars($pedido['tamano'] ?: 'Por definir') ?></p><p><strong>Presupuesto:</strong> <?= htmlspecialchars($pedido['presupuesto'] ?: 'Solicita cotización') ?></p><?php if ($pedido['fecha_entrega']): ?><p><strong>Entrega ideal:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($pedido['fecha_entrega']))) ?></p><?php endif; ?></section><section><h3>Pedido a tu medida</h3><ul><?php foreach ($productos as $producto): ?><li><?= htmlspecialchars($producto['nombre'] ?? '') ?> ×<?= (int) ($producto['cantidad'] ?? 0) ?></li><?php endforeach; ?></ul><p><strong>Subtotal:</strong> $<?= number_format((float) $pedido['subtotal'], 0, ',', '.') ?></p></section></div>
                        <div class="admin-order-details"><h3>Detalles de la idea</h3><p><?= nl2br(htmlspecialchars($pedido['detalles'])) ?></p><p><strong>Entrega:</strong> <?= htmlspecialchars($pedido['entrega']) ?> · <?= htmlspecialchars($pedido['direccion']) ?></p><?php if ($pedido['notas']): ?><p><strong>Notas:</strong> <?= nl2br(htmlspecialchars($pedido['notas'])) ?></p><?php endif; ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../include/templates/footer.php'; ?>
