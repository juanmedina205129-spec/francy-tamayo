<?php
require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
$db = conectarDB();

$scripts = ['crear'];
$errores = [];
$mensaje = isset($_GET['ok']) ? 'Producto creado con exito' : '';

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
    if (!$imagen || empty($imagen['tmp_name'])) $errores['imagen'] = 'La imagen es obligatoria';

    if (empty($errores)) {
        $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/francytamayo/assets/imagenes/productos/';
        if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'avif'];

        if (!in_array($extension, $permitidos, true)) {
            $errores['imagen'] = 'Formato no valido';
        } elseif ($imagen['size'] > 2500000) {
            $errores['imagen'] = 'Maximo 2.5MB';
        } else {
            $nombreImagen = md5(uniqid((string) rand(), true)) . '.' . $extension;
            $ruta = $carpeta . $nombreImagen;

            if (move_uploaded_file($imagen['tmp_name'], $ruta)) {
                $rutaDB = 'assets/imagenes/productos/' . $nombreImagen;
                $stmt = $db->prepare('INSERT INTO productos (nombre, categoria, tipo, descripcion, precio, imagen, estado, rating, destacado, activo, orden) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssdssdiii', $nombre, $categoria, $tipo, $descripcion, $precio, $rutaDB, $estado, $rating, $destacado, $activo, $orden);
                $stmt->execute();

                header('Location: crear.php?ok=1');
                exit;
            }

            $errores['imagen'] = 'No se pudo subir la imagen';
        }
    }
}

incluirTemplates('header');
?>

<section class="container container_admin">
    <div>
        <h1>Crear producto</h1>
        <div class="botonesdeadmin">
            <a href="<?php echo BASE_URL;?>admin/menu/index.php">Ver productos</a>
            <a href="<?php echo BASE_URL;?>admin/index.php">Panel</a>
        </div>
    </div>
    <section class="admin-container admin-container-admin">
        <main class="admin-card">
            <h1>Nuevo producto</h1>

            <?php if ($mensaje): ?>
                <p id="mensajeOk" class="mensajeOk"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php endif; ?>

            <form class="admin-form" method="POST" enctype="multipart/form-data" novalidate>
                <label>Nombre</label>
                <input type="text" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">
                <?php if (isset($errores['nombre'])): ?><p class="error"><?php echo $errores['nombre']; ?></p><?php endif; ?>

                <label>Categoria</label>
                <select name="categoria">
                    <option value="pinturas" <?php echo $categoria === 'pinturas' ? 'selected' : ''; ?>>Pinturas</option>
                    <option value="retratos" <?php echo $categoria === 'retratos' ? 'selected' : ''; ?>>Retratos</option>
                    <option value="camisetas" <?php echo $categoria === 'camisetas' ? 'selected' : ''; ?>>Camisetas</option>
                </select>

                <label>Tipo</label>
                <input type="text" name="tipo" value="<?php echo htmlspecialchars($tipo); ?>" placeholder="Ej: Acuarela, Camiseta, Retrato por encargo">
                <?php if (isset($errores['tipo'])): ?><p class="error"><?php echo $errores['tipo']; ?></p><?php endif; ?>

                <label>Descripcion</label>
                <textarea name="descripcion"><?php echo htmlspecialchars($descripcion); ?></textarea>

                <label>Precio</label>
                <input type="number" name="precio" min="0" value="<?php echo htmlspecialchars((string) $precio); ?>">
                <?php if (isset($errores['precio'])): ?><p class="error"><?php echo $errores['precio']; ?></p><?php endif; ?>

                <label>Estado</label>
                <select name="estado">
                    <option value="disponible" <?php echo $estado === 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="encargo" <?php echo $estado === 'encargo' ? 'selected' : ''; ?>>Por encargo</option>
                    <option value="agotado" <?php echo $estado === 'agotado' ? 'selected' : ''; ?>>Agotado</option>
                </select>

                <label>Valoracion</label>
                <input type="number" name="rating" min="0" max="5" step="0.1" value="<?php echo htmlspecialchars((string) $rating); ?>">

                <label>Orden</label>
                <input type="number" name="orden" value="<?php echo htmlspecialchars((string) $orden); ?>">

                <label><input type="checkbox" name="destacado" <?php echo $destacado ? 'checked' : ''; ?>> Destacado en inicio</label>
                <label><input type="checkbox" name="activo" <?php echo $activo ? 'checked' : ''; ?>> Activo</label>

                <label>Imagen</label>
                <input type="file" name="imagen" accept="image/jpeg, image/png, image/webp, image/avif">
                <?php if (isset($errores['imagen'])): ?><p class="error"><?php echo $errores['imagen']; ?></p><?php endif; ?>

                <img id="preview" style="max-width:200px; display:none;">
                <input type="submit" value="Crear producto" class="admin-btn admin-btn-create-product">
            </form>
        </main>
    </section>
</section>

<?php include '../../include/templates/footer.php'; ?>
