<?php
require_once __DIR__ . '/../include/funciones.php';
require_once __DIR__ . '/../include/pedidos.php';
require_once __DIR__ . '/../include/imagenes.php';
auth();

/** Guarda el aviso que se muestra tras la redirección y vuelve al listado. */
function volverAPedidos(string $tipo, string $texto, int $pedidoId = 0): never
{
    $_SESSION['pedidos_aviso'] = ['tipo' => $tipo, 'texto' => $texto];
    header('Location: pedidos.php' . ($pedidoId > 0 ? '#pedido-' . $pedidoId : ''));
    exit;
}

// Todos los formularios de la página envían un campo oculto "accion".
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        volverAPedidos('error', 'Tu sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.');
    }

    $accion = entradaTexto($_POST['accion'] ?? '');
    $id = (int) entradaTexto($_POST['id'] ?? '');

    try {
        $db = conectarDB();

        if ($accion === 'estado') {
            $estado = entradaTexto($_POST['estado'] ?? '');
            if ($id <= 0 || !isset(ESTADOS_PEDIDO[$estado])) {
                volverAPedidos('error', 'Elige un estado válido para el pedido.', $id);
            }
            $stmt = $db->prepare('UPDATE pedidos SET estado = ? WHERE id = ?');
            $stmt->bind_param('si', $estado, $id);
            $stmt->execute();
            volverAPedidos('exito', "El estado del pedido #$id se actualizó.", $id);
        }

        if ($accion === 'cupo') {
            $cupo = entradaTexto($_POST['cupo'] ?? '');
            if (!ctype_digit($cupo) || (int) $cupo > CUPO_ENCARGOS_MAXIMO) {
                volverAPedidos('error', 'El cupo debe ser un número entre 0 y ' . CUPO_ENCARGOS_MAXIMO . '.');
            }
            guardarCupoEncargos($db, (int) $cupo);
            volverAPedidos('exito', (int) $cupo === 0
                ? 'Los encargos quedaron pausados: la tienda no recibe pedidos nuevos.'
                : 'El cupo se actualizó a ' . (int) $cupo . ((int) $cupo === 1 ? ' encargo activo.' : ' encargos activos.'));
        }

        if ($accion === 'foto') {
            $etapa = entradaTexto($_POST['etapa'] ?? '');
            $nota = entradaTexto($_POST['nota'] ?? '');
            if (!buscarPedidoPorId($db, $id)) {
                volverAPedidos('error', 'El pedido ya no existe.');
            }
            if (!isset(ETAPAS_FOTO_PEDIDO[$etapa])) {
                volverAPedidos('error', 'Elige la etapa de la foto.', $id);
            }
            if (mb_strlen($nota) > 255) {
                volverAPedidos('error', 'La nota para el cliente no puede superar 255 caracteres.', $id);
            }

            [$ruta, $errorFoto] = guardarImagenSubida($_FILES['foto'] ?? [], CARPETA_FOTOS_PEDIDO, MAXIMO_FOTO_PEDIDO);
            if ($errorFoto) {
                volverAPedidos('error', $errorFoto, $id);
            }

            try {
                $nota = $nota === '' ? null : $nota;
                $stmt = $db->prepare('INSERT INTO pedido_fotos (pedido_id, etapa, ruta, nota) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('isss', $id, $etapa, $ruta, $nota);
                $stmt->execute();

                // Un boceto nuevo vuelve a pedir la aprobación del cliente.
                if ($etapa === 'boceto') {
                    $stmt = $db->prepare("UPDATE pedidos SET boceto_estado = 'pendiente', boceto_comentario = NULL WHERE id = ?");
                    $stmt->bind_param('i', $id);
                    $stmt->execute();
                }
            } catch (Throwable $exception) {
                eliminarImagenSubida($ruta, CARPETA_FOTOS_PEDIDO);
                throw $exception;
            }

            volverAPedidos('exito', $etapa === 'boceto'
                ? "Boceto subido al pedido #$id. El cliente ya puede aprobarlo o pedir cambios."
                : "Foto subida al pedido #$id. El cliente ya puede verla en el seguimiento.", $id);
        }

        if ($accion === 'eliminar_foto') {
            $fotoId = (int) entradaTexto($_POST['foto_id'] ?? '');
            $stmt = $db->prepare('SELECT * FROM pedido_fotos WHERE id = ?');
            $stmt->bind_param('i', $fotoId);
            $stmt->execute();
            $foto = $stmt->get_result()->fetch_assoc();
            if (!$foto) {
                volverAPedidos('error', 'La foto ya no existe.', $id);
            }

            $id = (int) $foto['pedido_id'];
            $stmt = $db->prepare('DELETE FROM pedido_fotos WHERE id = ?');
            $stmt->bind_param('i', $fotoId);
            $stmt->execute();
            eliminarImagenSubida($foto['ruta'], CARPETA_FOTOS_PEDIDO);

            // Sin ningún boceto publicado ya no hay nada que el cliente pueda aprobar.
            if ($foto['etapa'] === 'boceto') {
                $stmt = $db->prepare("UPDATE pedidos SET boceto_estado = 'sin_boceto', boceto_comentario = NULL WHERE id = ? AND boceto_estado = 'pendiente' AND NOT EXISTS (SELECT 1 FROM pedido_fotos WHERE pedido_id = ? AND etapa = 'boceto')");
                $stmt->bind_param('ii', $id, $id);
                $stmt->execute();
            }

            volverAPedidos('exito', "La foto se retiró del pedido #$id.", $id);
        }
    } catch (Throwable $exception) {
        error_log('admin/pedidos: ' . $exception->getMessage());
        volverAPedidos('error', 'No se pudo guardar el cambio. Revisa la conexión con la base de datos.', $id);
    }

    volverAPedidos('error', 'Acción no reconocida.');
}

$aviso = $_SESSION['pedidos_aviso'] ?? null;
unset($_SESSION['pedidos_aviso']);

$filtroEstado = entradaTexto($_GET['estado'] ?? '');
if (!isset(ESTADOS_PEDIDO[$filtroEstado])) {
    $filtroEstado = '';
}

$pedidos = [];
$fotosPorPedido = [];
$cupo = null;
$error = '';

try {
    $db = conectarDB();
    $cupo = estadoCupoEncargos($db);
    if ($filtroEstado) {
        $stmt = $db->prepare('SELECT * FROM pedidos WHERE estado = ? ORDER BY fecha_creacion DESC, id DESC');
        $stmt->bind_param('s', $filtroEstado);
        $stmt->execute();
        $pedidos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $pedidos = $db->query('SELECT * FROM pedidos ORDER BY fecha_creacion DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
    }
    $fotosPorPedido = obtenerFotosPedidos($db, array_column($pedidos, 'id'));
} catch (Throwable $exception) {
    $error = 'No fue posible consultar los encargos. Revisa la conexión con la base de datos.';
}

$csrf = htmlspecialchars(csrfToken());

incluirTemplates('header');
?>

<main class="admin-orders-page">
    <section class="container admin-orders" aria-labelledby="orders-title">
        <div class="admin-orders-heading">
            <div>
                <span class="admin-eyebrow">Administración · Pedidos</span>
                <h1 id="orders-title">Encargos personalizados</h1>
                <p>Consulta las solicitudes del carrito y del formulario de contacto, actualiza su estado y comparte el boceto y las fotos del avance con cada cliente.</p>
            </div>
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-outline-link">← Panel de administración</a>
        </div>

        <?php if ($aviso): ?>
            <p class="admin-orders-message <?= $aviso['tipo'] === 'exito' ? 'is-success' : 'is-error' ?>" role="<?= $aviso['tipo'] === 'exito' ? 'status' : 'alert' ?>"><?= htmlspecialchars($aviso['texto']) ?></p>
        <?php endif; ?>

        <?php if ($error): ?>
            <p class="admin-orders-message is-error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($cupo): ?>
            <?php $porcentaje = $cupo['cupo'] > 0 ? min(100, (int) round($cupo['ocupados'] / $cupo['cupo'] * 100)) : 100; ?>
            <div class="admin-quota <?= $cupo['lleno'] ? 'is-full' : '' ?>">
                <div class="admin-quota-status">
                    <span class="admin-eyebrow">Cupo de encargos</span>
                    <p class="admin-quota-count"><b><?= $cupo['ocupados'] ?></b> de <b><?= $cupo['cupo'] ?></b> encargos activos</p>
                    <div class="admin-quota-bar" role="progressbar" aria-label="Cupo de encargos ocupado" aria-valuemin="0" aria-valuemax="<?= max(1, $cupo['cupo']) ?>" aria-valuenow="<?= min($cupo['ocupados'], max(1, $cupo['cupo'])) ?>"><span style="width:<?= $porcentaje ?>%"></span></div>
                    <p class="admin-quota-note">
                        <?php if ($cupo['cupo'] === 0): ?>
                            Encargos pausados: la tienda no recibe pedidos nuevos.
                        <?php elseif ($cupo['lleno']): ?>
                            Agenda completa: la tienda no recibe pedidos hasta que cierres alguno o subas el cupo.
                        <?php else: ?>
                            <?= $cupo['disponibles'] === 1 ? 'Queda 1 cupo libre.' : 'Quedan ' . $cupo['disponibles'] . ' cupos libres.' ?> Cuentan los pedidos en estado Nuevo y En proceso.
                        <?php endif; ?>
                    </p>
                </div>
                <form method="POST" class="admin-quota-form">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="hidden" name="accion" value="cupo">
                    <label for="cupo-encargos">Máximo de encargos a la vez</label>
                    <div>
                        <input id="cupo-encargos" type="number" name="cupo" min="0" max="<?= CUPO_ENCARGOS_MAXIMO ?>" step="1" required value="<?= $cupo['cupo'] ?>">
                        <button type="submit">Guardar cupo</button>
                    </div>
                    <small>De 0 a <?= CUPO_ENCARGOS_MAXIMO ?>. Con 0 se pausan los encargos.</small>
                </form>
            </div>
        <?php endif; ?>

        <nav class="admin-orders-filters" aria-label="Filtrar pedidos por estado">
            <a href="pedidos.php" class="<?= $filtroEstado === '' ? 'active' : '' ?>">Todos</a>
            <?php foreach (ESTADOS_PEDIDO as $valor => $etiqueta): ?>
                <a href="pedidos.php?estado=<?= $valor ?>" class="<?= $filtroEstado === $valor ? 'active' : '' ?>"><?= $etiqueta ?></a>
            <?php endforeach; ?>
        </nav>

        <?php if (!$pedidos && !$error): ?>
            <div class="admin-orders-empty"><span>◌</span><h2><?= $filtroEstado ? 'No hay encargos en este estado' : 'Aún no hay encargos' ?></h2><p>Los pedidos confirmados desde el carrito o el formulario de contacto aparecerán aquí.</p></div>
        <?php elseif ($pedidos): ?>
            <div class="admin-orders-list">
                <?php foreach ($pedidos as $pedido): ?>
                    <?php
                    $pedidoId = (int) $pedido['id'];
                    $productos = json_decode($pedido['productos'], true) ?: [];
                    $fotos = $fotosPorPedido[$pedidoId] ?? [];
                    ?>
                    <article class="admin-order-card" id="pedido-<?= $pedidoId ?>">
                        <header>
                            <div>
                                <span class="admin-order-id">Pedido #<?= $pedidoId ?><?= $pedido['codigo'] ? ' · ' . htmlspecialchars($pedido['codigo']) : '' ?> · <?= $pedido['origen'] === 'contacto' ? 'Formulario de contacto' : 'Carrito' ?></span>
                                <h2><?= htmlspecialchars($pedido['nombre']) ?></h2>
                                <p><?= htmlspecialchars(date('d/m/Y · H:i', strtotime($pedido['fecha_creacion']))) ?></p>
                            </div>
                            <form method="POST" class="admin-order-status-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="accion" value="estado">
                                <input type="hidden" name="id" value="<?= $pedidoId ?>">
                                <label class="sr-only" for="estado-<?= $pedidoId ?>">Estado del pedido</label>
                                <select id="estado-<?= $pedidoId ?>" name="estado" class="admin-order-status">
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
                            <?php if ($pedido['foto_referencia']): ?>
                                <a class="admin-order-reference" href="<?= BASE_URL . htmlspecialchars($pedido['foto_referencia']) ?>" target="_blank" rel="noopener" title="Abrir la foto de referencia en tamaño completo">
                                    <img src="<?= BASE_URL . htmlspecialchars($pedido['foto_referencia']) ?>" alt="Foto de referencia enviada por <?= htmlspecialchars($pedido['nombre']) ?>" loading="lazy">
                                    <span>Foto de referencia ↗</span>
                                </a>
                            <?php endif; ?>
                            <div>
                                <h3>Detalles de la idea</h3>
                                <p><?= nl2br(htmlspecialchars($pedido['detalles'])) ?></p>
                                <?php if ($pedido['entrega'] || $pedido['direccion']): ?>
                                    <p><strong>Entrega:</strong> <?= htmlspecialchars($pedido['entrega'] ?: 'Por definir') ?><?= $pedido['direccion'] ? ' · ' . htmlspecialchars($pedido['direccion']) : '' ?></p>
                                <?php endif; ?>
                                <?php if ($pedido['notas']): ?><p><strong>Notas:</strong> <?= nl2br(htmlspecialchars($pedido['notas'])) ?></p><?php endif; ?>
                                <?php if ($pedido['es_regalo']): ?>
                                    <div class="admin-order-gift">
                                        <p><strong>🎁 Es un regalo para:</strong> <?= htmlspecialchars((string) $pedido['regalo_para']) ?></p>
                                        <p><strong>Mensaje para la tarjeta:</strong> <?= $pedido['regalo_mensaje'] ? nl2br(htmlspecialchars($pedido['regalo_mensaje'])) : 'Sin mensaje.' ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="admin-order-progress">
                            <div class="admin-order-progress-head">
                                <h3>Boceto y fotos del avance</h3>
                                <span class="admin-sketch-status is-<?= htmlspecialchars($pedido['boceto_estado']) ?>">Boceto: <?= htmlspecialchars(ESTADOS_BOCETO[$pedido['boceto_estado']] ?? $pedido['boceto_estado']) ?></span>
                            </div>
                            <?php if ($pedido['boceto_comentario']): ?>
                                <blockquote class="admin-sketch-comment"><strong>Cambios que pidió el cliente:</strong> <?= nl2br(htmlspecialchars($pedido['boceto_comentario'])) ?></blockquote>
                            <?php endif; ?>

                            <?php if ($fotos): ?>
                                <ul class="admin-order-photos">
                                    <?php foreach ($fotos as $foto): ?>
                                        <li>
                                            <a href="<?= BASE_URL . htmlspecialchars($foto['ruta']) ?>" target="_blank" rel="noopener"><img src="<?= BASE_URL . htmlspecialchars($foto['ruta']) ?>" alt="<?= htmlspecialchars(ETAPAS_FOTO_PEDIDO[$foto['etapa']] ?? 'Foto') ?> del pedido #<?= $pedidoId ?>" loading="lazy"></a>
                                            <p><b><?= htmlspecialchars(ETAPAS_FOTO_PEDIDO[$foto['etapa']] ?? 'Foto') ?></b> · <?= htmlspecialchars(date('d/m/Y', strtotime($foto['fecha']))) ?></p>
                                            <?php if ($foto['nota']): ?><p class="admin-order-photo-note"><?= htmlspecialchars($foto['nota']) ?></p><?php endif; ?>
                                            <form method="POST" onsubmit="return confirm('¿Retirar esta foto? El cliente dejará de verla.')">
                                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                                <input type="hidden" name="accion" value="eliminar_foto">
                                                <input type="hidden" name="id" value="<?= $pedidoId ?>">
                                                <input type="hidden" name="foto_id" value="<?= (int) $foto['id'] ?>">
                                                <button type="submit">Retirar</button>
                                            </form>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p class="admin-order-photos-empty">Aún no has subido fotos de este pedido.</p>
                            <?php endif; ?>

                            <form method="POST" enctype="multipart/form-data" class="admin-photo-form">
                                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                <input type="hidden" name="accion" value="foto">
                                <input type="hidden" name="id" value="<?= $pedidoId ?>">
                                <label>Etapa
                                    <select name="etapa" required>
                                        <?php foreach (ETAPAS_FOTO_PEDIDO as $valor => $etiqueta): ?>
                                            <option value="<?= $valor ?>" <?= $valor === 'avance' ? 'selected' : '' ?>><?= $etiqueta ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Foto (máx. 5 MB)
                                    <input type="file" name="foto" required accept="image/jpeg,image/png,image/webp,image/avif">
                                </label>
                                <label class="admin-photo-note">Nota para el cliente
                                    <input type="text" name="nota" maxlength="255" placeholder="Ej.: primera capa de color">
                                </label>
                                <button type="submit">Subir foto</button>
                                <small>Una foto de etapa «Boceto» le pide al cliente que la apruebe o solicite cambios.</small>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<?php include __DIR__ . '/../include/templates/footer.php'; ?>
