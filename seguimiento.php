<?php
require 'include/funciones.php';
require 'include/pedidos.php';

iniciarSesion();

$codigo = entradaTexto($_POST['codigo'] ?? $_GET['codigo'] ?? '');
$pedido = null;
$error = '';

// Límite de consultas fallidas por visitante para dificultar que se adivinen códigos.
const SEGUIMIENTO_MAX_FALLOS = 8;
const SEGUIMIENTO_VENTANA = 600;

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
            $pedido = buscarPedidoParaCliente(conectarDB(), $codigo, $telefono);
            if ($pedido) {
                $codigo = $pedido['codigo'];
            } else {
                $fallos[] = time();
                $error = 'No encontramos un pedido con ese código y teléfono. Revisa los datos e inténtalo de nuevo.';
            }
        } catch (Throwable $exception) {
            $error = 'No pudimos consultar tu pedido en este momento. Inténtalo más tarde.';
        }
    }

    $_SESSION['seguimiento_fallos'] = array_values($fallos);
}

incluirTemplates('header');
?>

<main class="tracking-page">
    <section class="tracking-hero">
        <div class="container">
            <span class="section-kicker">Tu pedido</span>
            <h1>Seguimiento de encargos</h1>
            <p>Consulta en qué va tu pieza con el código que recibiste al confirmar el pedido.</p>
        </div>
    </section>

    <section class="tracking-content container">
        <form class="tracking-form" method="POST" action="<?= BASE_URL ?>seguimiento.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label>Código del pedido
                <input name="codigo" required placeholder="FT-XXXXXX" autocomplete="off" autocapitalize="characters" maxlength="12" value="<?= htmlspecialchars($codigo) ?>">
            </label>
            <label>Teléfono / WhatsApp
                <input name="telefono" required placeholder="El mismo que usaste en el pedido" autocomplete="tel" inputmode="tel" maxlength="30">
            </label>
            <button type="submit">Consultar</button>
        </form>

        <?php if ($error): ?>
            <p class="tracking-message is-error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if ($pedido): ?>
            <?php
            $productos = json_decode($pedido['productos'], true) ?: [];
            $pasos = array_keys(PASOS_SEGUIMIENTO);
            $pasoActual = array_search($pedido['estado'], $pasos, true);
            $ultimaActualizacion = $pedido['fecha_actualizacion'] ?: $pedido['fecha_creacion'];
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
                        <li class="<?= $indice < $pasoActual ? 'is-done' : ($indice === $pasoActual ? 'is-current' : '') ?>"<?= $indice === $pasoActual ? ' aria-current="step"' : '' ?>>
                            <span><?= $indice < $pasoActual || $pedido['estado'] === 'cerrado' ? '✓' : $indice + 1 ?></span>
                            <div><strong><?= htmlspecialchars($paso['titulo']) ?></strong><p><?= htmlspecialchars($paso['detalle']) ?></p></div>
                        </li>
                    <?php endforeach; ?>
                </ol>

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

                <a class="tracking-whatsapp" target="_blank" rel="noopener" href="https://wa.me/573184597719?text=<?= rawurlencode('Hola, quiero consultar mi pedido ' . $pedido['codigo'] . '.') ?>">Preguntar por este pedido en WhatsApp ↗</a>
            </article>
        <?php endif; ?>
    </section>
</main>

<?php include 'include/templates/footer.php'; ?>
