<?php
require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
require_once __DIR__ . '/../../include/producto_admin.php';
$db = conectarDB();

$scripts = ['crear'];
$errores = [];

$nombre = '';
$descripcion = '';
$precio = '';
$categoria = 'pinturas';
$tipo = '';
$estado = 'disponible';
$rating = '4.8';
$orden = '0';
$destacado = 0;
$activo = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $errores['general'] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
    }
    $nombre = entradaTexto($_POST['nombre'] ?? '');
    $descripcion = entradaTexto($_POST['descripcion'] ?? '');
    $precio = (float) entradaTexto($_POST['precio'] ?? '0');
    $categoria = entradaTexto($_POST['categoria'] ?? 'pinturas');
    $tipo = entradaTexto($_POST['tipo'] ?? '');
    $estado = entradaTexto($_POST['estado'] ?? 'disponible');
    $rating = (float) entradaTexto($_POST['rating'] ?? '4.8');
    $orden = (int) entradaTexto($_POST['orden'] ?? '0');
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $imagen = $_FILES['imagen'] ?? null;

    if (!$nombre) $errores['nombre'] = 'El nombre es obligatorio';
    if (!$tipo) $errores['tipo'] = 'El tipo es obligatorio';
    if (!in_array($categoria, ['pinturas', 'retratos', 'camisetas'], true)) $errores['categoria'] = 'Categoria invalida';
    if (!in_array($estado, ['disponible', 'encargo', 'agotado'], true)) $errores['estado'] = 'Estado invalido';
    if ($precio <= 0 || $precio > 99999999) $errores['precio'] = 'Precio invalido (entre 1 y 99.999.999)';
    if ($orden < -1000000 || $orden > 1000000) $errores['orden'] = 'El orden debe estar entre -1.000.000 y 1.000.000';
    if ($rating < 0 || $rating > 5) $errores['rating'] = 'La valoracion debe estar entre 0 y 5';
    if (!$imagen || empty($imagen['tmp_name'])) $errores['imagen'] = 'La imagen es obligatoria';

    if (empty($errores)) {
        [$rutaDB, $errorImagen] = guardarImagenProducto($imagen);
        if ($errorImagen) {
            $errores['imagen'] = $errorImagen;
        } else {
            try {
                $stmt = $db->prepare('INSERT INTO productos (nombre, categoria, tipo, descripcion, precio, imagen, estado, rating, destacado, activo, orden) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssdssdiii', $nombre, $categoria, $tipo, $descripcion, $precio, $rutaDB, $estado, $rating, $destacado, $activo, $orden);
                $stmt->execute();

                header('Location: index.php?ok=creado');
                exit;
            } catch (mysqli_sql_exception $error) {
                eliminarImagenProductoSubida($rutaDB);
                $errores['general'] = $error->getCode() === 1062 ? 'Ya existe un producto con ese nombre.' : 'No se pudo guardar el producto en la base de datos.';
            }
        }
    }
}

incluirTemplates('header');
?>

<section class="container container_admin admin-product-page">
    <div class="admin-product-page-header">
        <div>
            <span class="admin-product-eyebrow">Administración · Catálogo</span>
            <h1>Agregar producto</h1>
            <p>Completa la información para publicar una nueva pieza en el catálogo.</p>
        </div>
        <div class="botonesdeadmin">
            <a href="<?php echo BASE_URL;?>admin/menu/index.php">← Volver al catálogo</a>
            <a href="<?php echo BASE_URL;?>admin/index.php">Panel</a>
        </div>
    </div>
</section>
    <section class="admin-container admin-container-admin admin-product-form-area">
        <main class="admin-card">
            <span class="admin-card-eyebrow">Nueva pieza</span>
            <h2>Información del producto</h2>
            <p class="admin-card-intro">Los campos con información precisa ayudan a que tus clientes encuentren y entiendan mejor cada producto.</p>

            <?php if (isset($errores['general'])): ?><p class="error" role="alert"><?php echo htmlspecialchars($errores['general']); ?></p><?php endif; ?>

            <form class="admin-form" method="POST" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <label for="nombre">Nombre</label>
                <input id="nombre" type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">
                <?php if (isset($errores['nombre'])): ?><p class="error"><?php echo $errores['nombre']; ?></p><?php endif; ?>

                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria">
                    <option value="pinturas" <?php echo $categoria === 'pinturas' ? 'selected' : ''; ?>>Pinturas</option>
                    <option value="retratos" <?php echo $categoria === 'retratos' ? 'selected' : ''; ?>>Retratos</option>
                    <option value="camisetas" <?php echo $categoria === 'camisetas' ? 'selected' : ''; ?>>Camisetas</option>
                </select>
                    <?php if (isset($errores['categoria'])): ?><p class="error"><?= htmlspecialchars($errores['categoria']) ?></p><?php endif; ?>

                <label for="tipo">Tipo</label>
                <input id="tipo" type="text" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>" placeholder="Ej: Acuarela, Camiseta, Retrato por encargo">
                <?php if (isset($errores['tipo'])): ?><p class="error"><?php echo $errores['tipo']; ?></p><?php endif; ?>

                <label for="descripcion">Descripcion</label>
                <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($descripcion); ?></textarea>

                <label for="precio">Precio</label>
                <input id="precio" type="number" name="precio" min="1" value="<?php echo htmlspecialchars((string) $precio); ?>">
                <?php if (isset($errores['precio'])): ?><p class="error"><?php echo $errores['precio']; ?></p><?php endif; ?>

                <label for="estado">Estado</label>
                <select id="estado" name="estado">
                    <option value="disponible" <?php echo $estado === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="encargo" <?php echo $estado === 'encargo' ? 'selected' : ''; ?>>Por encargo</option>
                    <option value="agotado" <?php echo $estado === 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                </select>
                    <?php if (isset($errores['estado'])): ?><p class="error"><?= htmlspecialchars($errores['estado']) ?></p><?php endif; ?>

                <label for="rating">Valoracion</label>
                <input id="rating" type="number" name="rating" min="0" max="5" step="0.1" value="<?php echo htmlspecialchars((string) $rating); ?>">
                <?php if (isset($errores['rating'])): ?><p class="error"><?= htmlspecialchars($errores['rating']) ?></p><?php endif; ?>

                <label for="orden">Orden</label>
                <input id="orden" type="number" name="orden" value="<?php echo htmlspecialchars((string) $orden); ?>">
                <?php if (isset($errores['orden'])): ?><p class="error"><?= htmlspecialchars($errores['orden']) ?></p><?php endif; ?>

                <label><input type="checkbox" name="destacado" <?php echo $destacado ? 'checked' : ''; ?>> Destacado en inicio</label>
                <label><input type="checkbox" name="activo" <?php echo $activo ? 'checked' : ''; ?>> Activo</label>

                <label for="imagen">Imagen</label>
                <input id="imagen" type="file" name="imagen" accept="image/jpeg, image/png, image/webp, image/avif">
                <?php if (isset($errores['imagen'])): ?><p class="error"><?php echo $errores['imagen']; ?></p><?php endif; ?>

                <img id="preview" alt="Vista previa de la imagen seleccionada" style="max-width:200px; display:none;">
                <input type="submit" value="Crear producto" class="admin-btn admin-btn-create-product">
            </form>
        </main>
    </section>

<?php include '../../include/templates/footer.php'; ?>
