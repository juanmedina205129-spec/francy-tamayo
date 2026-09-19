<?php
require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
require_once __DIR__ . '/../../include/producto_admin.php';
$db = conectarDB();

$scripts = ['editar'];
$errores = [];

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];
$stmt = $db->prepare('SELECT * FROM productos WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
    echo 'Producto no encontrado';
    exit;
}

$nombre = $producto['nombre'];
$descripcion = $producto['descripcion'];
$precio = $producto['precio'];
$categoria = $producto['categoria'];
$tipo = $producto['tipo'];
$estado = $producto['estado'];
$rating = $producto['rating'];
$orden = $producto['orden'];
$destacado = (int) $producto['destacado'];
$activo = (int) $producto['activo'];
$rutaImagen = $producto['imagen'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $errores['general'] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
    }
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = (float) ($_POST['precio'] ?? 0);
    $categoria = $_POST['categoria'] ?? 'pinturas';
    $tipo = trim($_POST['tipo'] ?? '');
    $estado = $_POST['estado'] ?? 'disponible';
    $rating = (float) ($_POST['rating'] ?? 4.8);
    $orden = (int) ($_POST['orden'] ?? 0);
    $destacado = isset($_POST['destacado']) ? 1 : 0;
    $activo = isset($_POST['activo']) ? 1 : 0;
    $imagen = $_FILES['imagen'] ?? null;

    if (!$nombre) $errores['nombre'] = 'El nombre es obligatorio';
    if (!$tipo) $errores['tipo'] = 'El tipo es obligatorio';
    if (!in_array($categoria, ['pinturas', 'retratos', 'camisetas'], true)) $errores['categoria'] = 'Categoria invalida';
    if (!in_array($estado, ['disponible', 'encargo', 'agotado'], true)) $errores['estado'] = 'Estado invalido';
    if ($precio <= 0) $errores['precio'] = 'Precio invalido';
    if ($rating < 0 || $rating > 5) $errores['rating'] = 'La valoracion debe estar entre 0 y 5';

    $imagenAnterior = $rutaImagen;
    if ($imagen && !empty($imagen['tmp_name'])) {
        [$nuevaRuta, $errorImagen] = guardarImagenProducto($imagen);
        if ($errorImagen) {
            $errores['imagen'] = $errorImagen;
        } else {
            $rutaImagen = $nuevaRuta;
        }
    }

    if (empty($errores)) {
        try {
            $stmt = $db->prepare('UPDATE productos SET nombre=?, categoria=?, tipo=?, descripcion=?, precio=?, imagen=?, estado=?, rating=?, destacado=?, activo=?, orden=? WHERE id=?');
            $stmt->bind_param('ssssdssdiiii', $nombre, $categoria, $tipo, $descripcion, $precio, $rutaImagen, $estado, $rating, $destacado, $activo, $orden, $id);
            $stmt->execute();
            if ($rutaImagen !== $imagenAnterior) eliminarImagenProductoSubida($imagenAnterior);

            header('Location: index.php?ok=editado');
            exit;
        } catch (mysqli_sql_exception $error) {
            if ($rutaImagen !== $imagenAnterior) eliminarImagenProductoSubida($rutaImagen);
            $rutaImagen = $imagenAnterior;
            $errores['general'] = $error->getCode() === 1062 ? 'Ya existe un producto con ese nombre.' : 'No se pudo actualizar el producto en la base de datos.';
        }
    }
}

incluirTemplates('header');
?>

<section class="container container_admin3 admin-product-page">
    <div class="admin-product-page-header">
        <div>
            <span class="admin-product-eyebrow">Administración · Catálogo</span>
            <h1>Editar producto</h1>
            <p>Actualiza los detalles de <strong><?= htmlspecialchars($nombre) ?></strong> y guarda los cambios cuando estén listos.</p>
        </div>
        <div class="botondeadmin3">
            <a href="<?php echo BASE_URL;?>admin/menu/index.php">← Volver al catálogo</a>
        </div>
    </div>
</section>

<section class="admin-container admin-container-admin admin-product-form-area">
    <main class="admin-card">
        <span class="admin-card-eyebrow">Edición</span>
        <h1>Información del producto</h1>
        <p class="admin-card-intro">Modifica solo lo necesario. Puedes conservar la imagen actual o cargar una nueva.</p>
        <?php if (isset($errores['general'])): ?><p class="error" role="alert"><?= htmlspecialchars($errores['general']) ?></p><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
            <?php if (isset($errores['nombre'])): ?><p class="error"><?= $errores['nombre'] ?></p><?php endif; ?>

            <label>Categoria</label>
            <select name="categoria">
                <option value="pinturas" <?= $categoria === 'pinturas' ? 'selected' : '' ?>>Pinturas</option>
                <option value="retratos" <?= $categoria === 'retratos' ? 'selected' : '' ?>>Retratos</option>
                <option value="camisetas" <?= $categoria === 'camisetas' ? 'selected' : '' ?>>Camisetas</option>
            </select>

            <label>Tipo</label>
            <input type="text" name="tipo" value="<?= htmlspecialchars($tipo) ?>">
            <?php if (isset($errores['tipo'])): ?><p class="error"><?= $errores['tipo'] ?></p><?php endif; ?>

            <label>Descripcion</label>
            <textarea name="descripcion"><?= htmlspecialchars((string) $descripcion) ?></textarea>

            <label>Precio</label>
            <input type="number" name="precio" min="0" value="<?= htmlspecialchars((string) $precio) ?>">
            <?php if (isset($errores['precio'])): ?><p class="error"><?= $errores['precio'] ?></p><?php endif; ?>

            <label>Estado</label>
            <select name="estado">
                <option value="disponible" <?= $estado === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                <option value="encargo" <?= $estado === 'encargo' ? 'selected' : '' ?>>Por encargo</option>
                <option value="agotado" <?= $estado === 'agotado' ? 'selected' : '' ?>>Agotado</option>
            </select>

            <label>Valoracion</label>
            <input type="number" name="rating" min="0" max="5" step="0.1" value="<?= htmlspecialchars((string) $rating) ?>">

            <label>Orden</label>
            <input type="number" name="orden" value="<?= htmlspecialchars((string) $orden) ?>">

            <label><input type="checkbox" name="destacado" <?= $destacado ? 'checked' : '' ?>> Destacado en inicio</label>
            <label><input type="checkbox" name="activo" <?= $activo ? 'checked' : '' ?>> Activo</label>

            <label>Imagen</label>
            <input type="file" name="imagen" accept="image/*" id="inputImagen">
            <?php if (isset($errores['imagen'])): ?><p class="error"><?= $errores['imagen'] ?></p><?php endif; ?>
            <img id="previewImagen" src="<?= BASE_URL . ltrim($rutaImagen, '/') ?>?t=<?= time() ?>" width="120" style="margin-top:5px;">

            <input type="submit" value="Guardar cambios" class="admin-btn admin-btn-save-product">
        </form>
    </main>
</section>

<?php include '../../include/templates/footer.php'; ?>
