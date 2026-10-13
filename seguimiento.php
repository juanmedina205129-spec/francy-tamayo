<?php
require 'include/funciones.php';
require 'include/pedidos.php';
require 'include/resenas.php';

iniciarSesion();

// Límite de consultas fallidas por visitante para dificultar que se adivinen códigos.
const SEGUIMIENTO_MAX_FALLOS = 8;
const SEGUIMIENTO_VENTANA = 600;
const BOCETO_MAX_COMENTARIO = 1000;

// Tras consultar con código + teléfono, el id del pedido queda en la sesión y la página se
// recarga por GET (?ver=1). Así recargar o volver atrás no reenvía el formulario, y las
// acciones del cliente (reseña, respuesta al boceto) actúan solo sobre ese pedido.
function redirigirSeguimiento(string $ancla = ''): never
{
    header('Location: ' . BASE_URL . 'seguimiento.php?ver=1' . $ancla);
    exit;
}

/** Texto de varias líneas de un formulario, con saltos de línea normalizados. */
function textoLargo(mixed $valor): string
{
    return str_replace(["\r\n", "\r"], "\n", entradaTexto($valor));
}

/** @return array{0: string, 1: string} [tipo de aviso, texto] */
function guardarResenaCliente(mysqli $db, array $pedido, array $entrada): array
{
    $estrellas = (int) entradaTexto($entrada['estrellas'] ?? '');
    $nombre = entradaTexto($entrada['nombre'] ?? '');
    $comentario = textoLargo($entrada['comentario'] ?? '');

    if ($pedido['estado'] !== 'cerrado') {
        return ['error', 'Podrás dejar tu reseña cuando el pedido esté finalizado.'];
    }
    if ($estrellas < 1 || $estrellas > 5) {
        return ['error', 'Elige una calificación de 1 a 5 estrellas.'];
    }
    if ($nombre === '') {
        $nombre = primerNombre($pedido['nombre']);
    }
    if (mb_strlen($nombre) > RESENA_MAX_NOMBRE) {
        return ['error', 'El nombre no puede superar ' . RESENA_MAX_NOMBRE . ' caracteres.'];
    }
    if (mb_strlen($comentario) > RESENA_MAX_COMENTARIO) {
        return ['error', 'El comentario no puede superar ' . RESENA_MAX_COMENTARIO . ' caracteres.'];
    }

    if (!guardarResena($db, (int) $pedido['id'], $nombre, $estrellas, $comentario === '' ? null : $comentario)) {
        return ['error', 'Este pedido ya tiene una reseña.'];
    }
    return ['exito', '¡Gracias! Tu reseña quedó publicada.'];
}

/** @return array{0: string, 1: string} [tipo de aviso, texto] */
function responderBocetoCliente(mysqli $db, array $pedido, array $entrada): array
{
    $decision = entradaTexto($entrada['decision'] ?? '');
    $comentario = textoLargo($entrada['comentario'] ?? '');

    if (!in_array($decision, ['aprobar', 'cambios'], true)) {
        return ['error', 'Elige si apruebas el boceto o si quieres pedir cambios.'];
    }
    if ($pedido['boceto_estado'] !== 'pendiente') {
        return ['error', 'Este boceto ya fue respondido.'];
    }
    if ($decision === 'cambios' && $comentario === '') {
        return ['error', 'Cuéntanos qué cambios quieres en el boceto.'];
    }
    if (mb_strlen($comentario) > BOCETO_MAX_COMENTARIO) {
        return ['error', 'El comentario no puede superar ' . BOCETO_MAX_COMENTARIO . ' caracteres.'];
    }

    $estado = $decision === 'aprobar' ? 'aprobado' : 'cambios';
    $comentario = $comentario === '' ? null : $comentario;
    // La condición sobre boceto_estado evita responder dos veces desde dos pestañas.
    $stmt = $db->prepare("UPDATE pedidos SET boceto_estado = ?, boceto_comentario = ? WHERE id = ? AND boceto_estado = 'pendiente'");
    $stmt->bind_param('ssi', $estado, $comentario, $pedido['id']);
    $stmt->execute();
    if ($stmt->affected_rows < 1) {
        return ['error', 'Este boceto ya fue respondido.'];
    }

    return ['exito', $estado === 'aprobado'
        ? '¡Boceto aprobado! Empezamos a trabajar en tu pieza.'
        : 'Recibimos tus cambios. Subiremos un nuevo boceto para que lo revises.'];
}

function primerNombre(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre));
    return mb_substr($partes[0] ?? '', 0, RESENA_MAX_NOMBRE);
}

$codigo = entradaTexto($_POST['codigo'] ?? $_GET['codigo'] ?? '');
$pedido = null;
$error = '';
$accion = entradaTexto($_POST['accion'] ?? 'consultar');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($accion, ['resena', 'boceto'], true)) {
    $pedidoId = (int) ($_SESSION['pedido_consultado'] ?? 0);

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $aviso = ['error', 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.'];
    } elseif ($pedidoId <= 0) {
        $aviso = ['error', 'Consulta tu pedido de nuevo para continuar.'];
    } else {
        try {
            $db = conectarDB();
            $pedidoAccion = buscarPedidoPorId($db, $pedidoId);
            if (!$pedidoAccion) {
                $aviso = ['error', 'No encontramos el pedido. Consúltalo de nuevo.'];
            } elseif ($accion === 'resena') {
                $aviso = guardarResenaCliente($db, $pedidoAccion, $_POST);
            } else {
                $aviso = responderBocetoCliente($db, $pedidoAccion, $_POST);
            }
        } catch (Throwable $exception) {
            error_log('seguimiento: ' . $exception->getMessage());
            $aviso = ['error', 'No pudimos guardar tu respuesta en este momento. Inténtalo más tarde.'];
        }
    }

    $_SESSION['seguimiento_aviso'] = ['tipo' => $aviso[0], 'texto' => $aviso[1]];
    redirigirSeguimiento('#' . $accion);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = entradaTexto($_POST['telefono'] ?? '');
    $fallos = array_filter($_SESSION['seguimiento_fallos'] ?? [], static fn(int $momento): bool => $momento > time() - SEGUIMIENTO_VENTANA);

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $error = 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.';
    } elseif (count($fallos) >= SEGUIMIENTO_MAX_FALLOS) {
        $error = 'Demasiadas consultas sin resultado. Espera unos minutos o escríbenos por WhatsApp.';
    } elseif ($codigo === '' || $telefono === '') {
        $error = 'Escribe el código del pedido y el teléfono con el que lo hiciste.';
    } else {
        try {
            $encontrado = buscarPedidoParaCliente(conectarDB(), $codigo, $telefono);
            if ($encontrado) {
                $_SESSION['seguimiento_fallos'] = array_values($fallos);
                $_SESSION['pedido_consultado'] = (int) $encontrado['id'];
                redirigirSeguimiento();
            }
            $fallos[] = time();
            $error = 'No encontramos un pedido con ese código y teléfono. Revisa los datos e inténtalo de nuevo.';
        } catch (Throwable $exception) {
            $error = 'No pudimos consultar tu pedido en este momento. Inténtalo más tarde.';
        }
    }

    $_SESSION['seguimiento_fallos'] = array_values($fallos);
}

$aviso = $_SESSION['seguimiento_aviso'] ?? null;
unset($_SESSION['seguimiento_aviso']);

$fotos = [];
$resena = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ver']) && !empty($_SESSION['pedido_consultado'])) {
    try {
        $db = conectarDB();
        $pedido = buscarPedidoPorId($db, (int) $_SESSION['pedido_consultado']);
        if ($pedido) {
            $codigo = $pedido['codigo'];
            $fotos = obtenerFotosPedidos($db, [(int) $pedido['id']])[(int) $pedido['id']] ?? [];
            $resena = obtenerResenaPedido($db, (int) $pedido['id']);
        } else {
            unset($_SESSION['pedido_consultado']);
        }
    } catch (Throwable $exception) {
        $pedido = null;
        $error = 'No pudimos consultar tu pedido en este momento. Inténtalo más tarde.';
    }
}

incluirTemplates('header');
?>

<main class="tracking-page">
    <section class="tracking-hero">
        <div class="container">
            <span class="section-kicker">Tu pedido</span>
            <h1>Seguimiento de encargos</h1>
            <p>Consulta en qué va tu pieza, aprueba el boceto y mira las fotos del avance con el código que recibiste al confirmar el pedido. Puedes volver cuando quieras desde «Mi pedido» en el menú.</p>
        </div>
    </section>

    <section class="tracking-content container">
        <div class="recent-orders" id="recent-orders" hidden>
            <span>Tus pedidos en este dispositivo:</span>
            <div data-recent-orders-list></div>
            <small>Elige uno y escribe tu teléfono para consultarlo.</small>
        </div>

        <form class="tracking-form" method="POST" action="<?= BASE_URL ?>seguimiento.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <input type="hidden" name="accion" value="consultar">
            <label>Código del pedido
                <input name="codigo" required placeholder="NA-XXXXXX" autocomplete="off" autocapitalize="characters" maxlength="12" value="<?= htmlspecialchars($codigo) ?>">
            </label>
            <label>Teléfono / WhatsApp
                <input name="telefono" required placeholder="El mismo que usaste en el pedido" autocomplete="tel" inputmode="tel" maxlength="30">
            </label>
            <button type="submit"><?= $pedido ? 'Consultar otro' : 'Consultar' ?></button>
        </form>

        <?php if ($error): ?>
            <p class="tracking-message is-error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($aviso): ?>
            <p class="tracking-message <?= $aviso['tipo'] === 'exito' ? 'is-success' : 'is-error' ?>" role="<?= $aviso['tipo'] === 'exito' ? 'status' : 'alert' ?>"><?= htmlspecialchars($aviso['texto']) ?></p>
        <?php endif; ?>

        <?php if ($pedido): ?>
            <?php
            $productos = json_decode($pedido['productos'], true) ?: [];
            $pasos = array_keys(PASOS_SEGUIMIENTO);
            $pasoActual = array_search($pedido['estado'], $pasos, true);
            $cerrado = $pedido['estado'] === 'cerrado';
            $ultimaActualizacion = $pedido['fecha_actualizacion'] ?: $pedido['fecha_creacion'];
            $boceto = $pedido['boceto_estado'] !== 'sin_boceto' ? ultimoBoceto($fotos) : null;
            // El boceto se muestra aparte, así que no se repite en la galería.
            $galeria = array_values(array_filter($fotos, static fn(array $foto): bool => !$boceto || $foto['id'] !== $boceto['id']));
            $csrf = htmlspecialchars(csrfToken());
            ?>
            <article class="tracking-card" aria-labelledby="tracking-title">
                <header>
                    <div>
                        <span class="tracking-code"><?= htmlspecialchars($pedido['codigo']) ?></span>
                        <h2 id="tracking-title"><?= htmlspecialchars($pedido['tipo_encargo']) ?></h2>
                        <p>Pedido realizado el <?= htmlspecialchars(date('d/m/Y', strtotime($pedido['fecha_creacion']))) ?> · Última actualización <?= htmlspecialchars(date('d/m/Y H:i', strtotime($ultimaActualizacion))) ?></p>
                    </div>
                    <span class="tracking-badge"><?= htmlspecialchars(PASOS_SEGUIMIENTO[$pedido['estado']]['titulo']) ?></span>
                </header>

                <ol class="tracking-steps">
                    <?php foreach (PASOS_SEGUIMIENTO as $estado => $paso): ?>
                        <?php $indice = array_search($estado, $pasos, true); ?>
                        <li class="<?= $indice < $pasoActual || $cerrado ? 'is-done' : ($indice === $pasoActual ? 'is-current' : '') ?>"<?= $indice === $pasoActual && !$cerrado ? ' aria-current="step"' : '' ?>>
                            <span><?= $indice < $pasoActual || $cerrado ? '✓' : $indice + 1 ?></span>
                            <div><strong><?= htmlspecialchars($paso['titulo']) ?></strong><p><?= htmlspecialchars($paso['detalle']) ?></p></div>
                        </li>
                    <?php endforeach; ?>
                    <?php // Último paso: la reseña, que solo se habilita con el pedido finalizado. ?>
                    <li class="<?= $resena ? 'is-done' : ($cerrado ? 'is-current' : '') ?>"<?= $cerrado && !$resena ? ' aria-current="step"' : '' ?>>
                        <span><?= $resena ? '✓' : count($pasos) + 1 ?></span>
                        <div>
                            <strong>Tu opinión</strong>
                            <?php if ($resena): ?>
                                <p>Ya nos dejaste tu reseña. ¡Gracias!</p>
                            <?php elseif ($cerrado): ?>
                                <p>Tu pedido está finalizado. <a href="#resena">Cuéntanos cómo te pareció</a>.</p>
                            <?php else: ?>
                                <p>Cuando el pedido esté finalizado podrás calificarlo y dejar tu reseña.</p>
                            <?php endif; ?>
                        </div>
                    </li>
                </ol>

                <?php if ($pedido['es_regalo']): ?>
                    <div class="tracking-block tracking-gift">
                        <span class="tracking-gift-icon" aria-hidden="true">🎁</span>
                        <div>
                            <h3>Pedido para regalo</h3>
                            <p>Para: <strong><?= htmlspecialchars((string) $pedido['regalo_para']) ?></strong></p>
                            <?php if ($pedido['regalo_mensaje']): ?>
                                <blockquote><?= nl2br(htmlspecialchars($pedido['regalo_mensaje'])) ?></blockquote>
                            <?php else: ?>
                                <p>Sin mensaje para la tarjeta. Si quieres agregar uno, escríbenos por WhatsApp.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($boceto): ?>
                    <div class="tracking-block tracking-sketch is-<?= htmlspecialchars($pedido['boceto_estado']) ?>" id="boceto">
                        <h3>Boceto de tu pieza</h3>
                        <div class="tracking-sketch-layout">
                            <a class="tracking-sketch-image" href="<?= BASE_URL . htmlspecialchars($boceto['ruta']) ?>" target="_blank" rel="noopener">
                                <img src="<?= BASE_URL . htmlspecialchars($boceto['ruta']) ?>" alt="Boceto de tu pedido <?= htmlspecialchars($pedido['codigo']) ?>" loading="lazy">
                            </a>
                            <div class="tracking-sketch-body">
                                <p class="tracking-sketch-date">Subido el <?= htmlspecialchars(date('d/m/Y', strtotime($boceto['fecha']))) ?></p>
                                <?php if ($boceto['nota']): ?><p class="tracking-sketch-note"><?= htmlspecialchars($boceto['nota']) ?></p><?php endif; ?>

                                <?php if ($pedido['boceto_estado'] === 'pendiente'): ?>
                                    <p>Revísalo con calma: empezamos a pintar cuando lo apruebes.</p>
                                    <form method="POST" action="<?= BASE_URL ?>seguimiento.php">
                                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                        <input type="hidden" name="accion" value="boceto">
                                        <input type="hidden" name="decision" value="aprobar">
                                        <button type="submit" class="tracking-button is-primary">✓ Aprobar boceto</button>
                                    </form>
                                    <details class="tracking-sketch-changes">
                                        <summary>Pedir cambios</summary>
                                        <form method="POST" action="<?= BASE_URL ?>seguimiento.php">
                                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                                            <input type="hidden" name="accion" value="boceto">
                                            <input type="hidden" name="decision" value="cambios">
                                            <label>¿Qué te gustaría cambiar? *
                                                <textarea name="comentario" rows="3" required maxlength="<?= BOCETO_MAX_COMENTARIO ?>" placeholder="Ej.: las orejas un poco más grandes y el fondo más claro."></textarea>
                                            </label>
                                            <button type="submit" class="tracking-button">Enviar cambios</button>
                                        </form>
                                    </details>
                                <?php elseif ($pedido['boceto_estado'] === 'aprobado'): ?>
                                    <p class="tracking-sketch-status">✓ Aprobaste este boceto. Tu pieza está en marcha.</p>
                                <?php else: ?>
                                    <p class="tracking-sketch-status">Pediste cambios. Subiremos un nuevo boceto para que lo revises.</p>
                                    <?php if ($pedido['boceto_comentario']): ?><blockquote><?= nl2br(htmlspecialchars($pedido['boceto_comentario'])) ?></blockquote><?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="tracking-block tracking-gallery">
                    <h3>Así va tu pieza</h3>
                    <?php if ($galeria): ?>
                        <ul>
                            <?php foreach ($galeria as $foto): ?>
                                <li>
                                    <a href="<?= BASE_URL . htmlspecialchars($foto['ruta']) ?>" target="_blank" rel="noopener">
                                        <img src="<?= BASE_URL . htmlspecialchars($foto['ruta']) ?>" alt="<?= htmlspecialchars(ETAPAS_FOTO_PEDIDO[$foto['etapa']] ?? 'Foto') ?> del <?= htmlspecialchars(date('d/m/Y', strtotime($foto['fecha']))) ?>" loading="lazy">
                                    </a>
                                    <p><span class="tracking-stage is-<?= htmlspecialchars($foto['etapa']) ?>"><?= htmlspecialchars(ETAPAS_FOTO_PEDIDO[$foto['etapa']] ?? 'Foto') ?></span> <time datetime="<?= htmlspecialchars(date('Y-m-d', strtotime($foto['fecha']))) ?>"><?= htmlspecialchars(date('d/m/Y', strtotime($foto['fecha']))) ?></time></p>
                                    <?php if ($foto['nota']): ?><p class="tracking-gallery-note"><?= htmlspecialchars($foto['nota']) ?></p><?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="tracking-gallery-empty">Aquí aparecerán las fotos del proceso: el boceto, los avances y la pieza terminada. Las subimos a medida que trabajamos en tu pedido.</p>
                    <?php endif; ?>
                </div>

                <?php if ($pedido['foto_referencia']): ?>
                    <div class="tracking-block tracking-reference">
                        <a href="<?= BASE_URL . htmlspecialchars($pedido['foto_referencia']) ?>" target="_blank" rel="noopener"><img src="<?= BASE_URL . htmlspecialchars($pedido['foto_referencia']) ?>" alt="Foto de referencia que enviaste" loading="lazy"></a>
                        <div><h3>Tu foto de referencia</h3><p>La recibimos con el pedido y trabajamos a partir de ella.</p></div>
                    </div>
                <?php endif; ?>

                <?php if ($productos): ?>
                    <div class="tracking-products">
                        <h3>Productos</h3>
                        <?php foreach ($productos as $producto): ?>
                            <p><span><?= htmlspecialchars($producto['nombre'] ?? '') ?> ×<?= (int) ($producto['cantidad'] ?? 0) ?></span><strong>$<?= number_format((float) ($producto['precio'] ?? 0) * (int) ($producto['cantidad'] ?? 0), 0, ',', '.') ?></strong></p>
                        <?php endforeach; ?>
                        <p class="tracking-total"><span>Subtotal</span><strong>$<?= number_format((float) $pedido['subtotal'], 0, ',', '.') ?></strong></p>
                    </div>
                <?php else: ?>
                    <p class="tracking-note">Encargo a medida: el precio final se confirma contigo por WhatsApp.</p>
                <?php endif; ?>

                <div class="tracking-block tracking-review" id="resena">
                    <?php if ($resena): ?>
                        <h3>Tu reseña</h3>
                        <p class="tracking-review-stars" aria-label="<?= (int) $resena['estrellas'] ?> de 5 estrellas"><span aria-hidden="true"><?= estrellasTexto($resena['estrellas']) ?></span></p>
                        <?php if ($resena['comentario']): ?><blockquote><?= nl2br(htmlspecialchars($resena['comentario'])) ?></blockquote><?php endif; ?>
                        <p class="tracking-review-meta">Publicada como <strong><?= htmlspecialchars($resena['nombre']) ?></strong> el <?= htmlspecialchars(date('d/m/Y', strtotime($resena['fecha']))) ?>. ¡Gracias por contarnos!</p>
                    <?php elseif (!$cerrado): ?>
                        <h3>Tu opinión</h3>
                        <p class="tracking-review-locked"><span aria-hidden="true">🔒</span> Podrás calificar tu pieza y dejar una reseña aquí mismo cuando el pedido esté en estado <strong>Finalizado</strong>.</p>
                    <?php else: ?>
                        <h3>¿Cómo te pareció?</h3>
                        <p>Tu opinión aparece en la tienda y ayuda a otras personas a decidirse. Es una sola reseña por pedido.</p>
                        <form method="POST" action="<?= BASE_URL ?>seguimiento.php" class="review-form">
                            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                            <input type="hidden" name="accion" value="resena">
                            <fieldset class="star-field">
                                <legend>Tu calificación *</legend>
                                <?php // De 5 a 1 en el HTML: el CSS invierte la fila para poder colorear "esta y las anteriores". ?>
                                <div class="star-input">
                                    <?php for ($estrella = 5; $estrella >= 1; $estrella--): ?>
                                        <input type="radio" id="estrella-<?= $estrella ?>" name="estrellas" value="<?= $estrella ?>" required>
                                        <label for="estrella-<?= $estrella ?>" title="<?= $estrella ?> de 5"><span class="sr-only"><?= $estrella ?> <?= $estrella === 1 ? 'estrella' : 'estrellas' ?></span><span aria-hidden="true">★</span></label>
                                    <?php endfor; ?>
                                </div>
                            </fieldset>
                            <label>Nombre con el que aparece
                                <input name="nombre" maxlength="<?= RESENA_MAX_NOMBRE ?>" value="<?= htmlspecialchars(primerNombre($pedido['nombre'])) ?>" autocomplete="given-name">
                            </label>
                            <label><span>Comentario <span class="field-optional">(opcional)</span></span>
                                <textarea name="comentario" rows="4" maxlength="<?= RESENA_MAX_COMENTARIO ?>" placeholder="Cuéntanos qué tal quedó tu pieza y cómo fue el proceso."></textarea>
                                <small>Máximo <?= RESENA_MAX_COMENTARIO ?> caracteres.</small>
                            </label>
                            <button type="submit" class="tracking-button is-primary">Publicar reseña</button>
                        </form>
                    <?php endif; ?>
                </div>

                <a class="tracking-whatsapp" target="_blank" rel="noopener" href="https://wa.me/573184597719?text=<?= rawurlencode('Hola, quiero consultar mi pedido ' . $pedido['codigo'] . '.') ?>">Preguntar por este pedido en WhatsApp ↗</a>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
