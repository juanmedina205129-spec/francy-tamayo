<?php
require_once __DIR__ . '/../include/funciones.php';
require_once __DIR__ . '/../include/pedidos.php';
auth();

$pedidos = [];
$error = '';
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) entradaTexto($_POST['id'] ?? '');
    $estado = entradaTexto($_POST['estado'] ?? '');

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        header('Location: pedidos.php?error=seguridad');
        exit;
    }

    if ($id > 0 && isset(ESTADOS_PEDIDO[$estado])) {
        try {
            $db = conectarDB();
            $stmt = $db->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
            $stmt->bind_param('si', $estado, $id);
            $stmt->execute();
            header('Location: pedidos.php?actualizado=' . $id . '#pedido-' . $id);
            exit;
        } catch (Throwable $exception) {
            header('Location: pedidos.php?error=actualizar');
            exit;
        }
    }

    header('Location: pedidos.php');
    exit;
}

if (isset($_GET['actualizado'])) {
    $mensaje = 'El estado del pedido #' . (int) entradaTexto($_GET['actualizado']) . ' se actualizó.';
} elseif (($_GET['error'] ?? '') === 'seguridad') {
    $error = 'Tu sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.';
} elseif (($_GET['error'] ?? '') === 'actualizar') {
    $error = 'No se pudo actualizar el estado del pedido.';
}

$filtroEstado = entradaTexto($_GET['estado'] ?? '');
if (!isset(ESTADOS_PEDIDO[$filtroEstado])) {
    $filtroEstado = '';
}

try {
    $db = conectarDB();
    if ($filtroEstado) {
        $stmt = $db->prepare('SELECT * FROM pedidos WHERE estado = ? ORDER BY fecha_creacion DESC, id DESC');
        $stmt->bind_param('s', $filtroEstado);
        $stmt->execute();
        $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $pedidos = $db->query('SELECT * FROM pedidos ORDER BY fecha_creacion DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
    }
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
                <p>Consulta las solicitudes enviadas desde el carrito y el formulario de contacto, y actualiza su estado.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-outline-link">← Panel de administración</a>
        </div>

        <nav class="admin-orders-filters" aria-label="Filtrar pedidos por estado">
            <a href="pedidos.php" class="<?= $filtroEstado === '' ? 'active' : '' ?>">Todos</a>
            <?php foreach (ESTADOS_PEDIDO as $valor => $etiqueta): ?>
                <a href="pedidos.php?estado=<?= $valor ?>" class="<?= $filtroEstado === $valor ? 'active' : '' ?>"><?= $etiqueta ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if ($mensaje): ?>
            <p class="admin-orders-message is-success" role="status"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="admin-orders-message is-error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!$pedidos && !$error): ?>
            <div class="admin-orders-empty"><span>◌</span><h2>Aún no hay encargos</h2><p>Los pedidos confirmados desde el carrito o el formulario de contacto aparecerán aquí.</p></div>
        <?php elseif ($pedidos): ?>
            <div class="admin-orders-list">
                <?php foreach ($pedidos as $pedido): ?>
                    <?php $productos = json_decode($pedido['productos'], true) ?: []; ?>
                    <article class="admin-order-card" id="pedido-<?= (int) $pedido['id'] ?>">
                        <header>
                            <div>
                                <span class="admin-order-id">Pedido #<?= (int) $pedido['id'] ?><?= $pedido['codigo'] ? ' · ' . htmlspecialchars($pedido['codigo']) : '' ?> · <?= $pedido['origen'] === 'contacto' ? 'Formulario de contacto' : 'Carrito' ?></span>
                                <h2><?= htmlspecialchars($pedido['nombre']) ?></h2>
                                <p><?= htmlspecialchars(date('d/m/Y · H:i', strtotime($pedido['fecha_creacion']))) ?></p>
                            </div>
                            <form method="POST" class="admin-order-status-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="id" value="<?= (int) $pedido['id'] ?>">
                                <label class="sr-only" for="estado-<?= (int) $pedido['id'] ?>">Estado del pedido</label>
                                <select id="estado-<?= (int) $pedido['id'] ?>" name="estado" class="admin-order-status">
                                    <?php foreach (ESTADOS_PEDIDO as $valor => $etiqueta): ?>
                                        <option value="<?= $valor ?>" <?= $pedido['estado'] === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit">Guardar</button>
                            </form>
                        </header>
                        <div class="admin-order-grid">
                            <section>
                                <h3>Contacto</h3>
                                <p><strong>WhatsApp:</strong> <?= htmlspecialchars($pedido['telefono']) ?></p>
                                <?php if ($pedido['email']): ?><p><strong>Correo:</strong> <?= htmlspecialchars($pedido['email']) ?></p><?php endif; ?>
                                <p><strong>Ciudad:</strong> <?= htmlspecialchars($pedido['ciudad'] ?: 'No indicada') ?></p>
                            </section>
                            <section>
                                <h3>Encargo personalizado</h3>
                                <p><strong>Tipo:</strong> <?= htmlspecialchars($pedido['tipo_encargo']) ?></p>
                                <p><strong>Tamaño:</strong> <?= htmlspecialchars($pedido['tamano'] ?: 'Por definir') ?></p>
                                <p><strong>Presupuesto:</strong> <?= htmlspecialchars($pedido['presupuesto'] ?: 'Solicita cotización') ?></p>
                                <?php if ($pedido['fecha_entrega']): ?><p><strong>Entrega ideal:</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($pedido['fecha_entrega']))) ?></p><?php endif; ?>
                            </section>
                            <section>
                                <h3>Productos</h3>
                                <?php if ($productos): ?>
                                    <ul><?php foreach ($productos as $producto): ?><li><?= htmlspecialchars($producto['nombre'] ?? '') ?> ×<?= (int) ($producto['cantidad'] ?? 0) ?></li><?php endforeach; ?></ul>
                                    <p><strong>Subtotal:</strong> $<?= number_format((float) $pedido['subtotal'], 0, ',', '.') ?></p>
                                <?php else: ?>
                                    <p>Solicitud sin productos del catálogo: requiere cotización.</p>
                                <?php endif; ?>
                            </section>
                        </div>
                        <div class="admin-order-details">
                            <h3>Detalles de la idea</h3>
                            <p><?= nl2br(htmlspecialchars($pedido['detalles'])) ?></p>
                            <?php if ($pedido['entrega'] || $pedido['direccion']): ?>
                                <p><strong>Entrega:</strong> <?= htmlspecialchars($pedido['entrega'] ?: 'Por definir') ?><?= $pedido['direccion'] ? ' · ' . htmlspecialchars($pedido['direccion']) : '' ?></p>
                            <?php endif; ?>
                            <?php if ($pedido['notas']): ?><p><strong>Notas:</strong> <?= nl2br(htmlspecialchars($pedido['notas'])) ?></p><?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../include/templates/footer.php'; ?>
