<?php

require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
$db = conectarDB();

incluirTemplates('header'); 


$scripts = ['editar']; // JS para preview de imagen




$mensaje = '';
$errores = [];

// ---------------------------
// Obtener ID del plato
// ---------------------------
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}
$id = intval($_GET['id']);

// ---------------------------
// Traer datos del plato
// ---------------------------
$stmt = $db->prepare("SELECT * FROM plato WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$plato = $resultado->fetch_assoc();

if (!$plato) {
    echo "Plato no encontrado";
    exit;
}

// ---------------------------
// Valores por defecto
// ---------------------------
$nombre = $plato['nombre'];
$descripcion = $plato['descripcion'];
$precio = $plato['valor'];
$activo = $plato['activo'];
$orden = $plato['orden'];
$rutaImagen = $plato['imagen'];

// ---------------------------
// Procesar formulario POST
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    $orden = intval($_POST['orden']);
    $imagen = $_FILES['imagen'];

    // VALIDACIONES
    if (!$nombre) $errores['nombre'] = "El nombre es obligatorio";
    if (!$descripcion) $errores['descripcion'] = "La descripción es obligatoria";
    if (!is_numeric($precio) || $precio <= 0) $errores['precio'] = "Precio inválido";

    // Subir nueva imagen si se cargó
    if (!empty($imagen['tmp_name'])) {
        $carpeta = $_SERVER['DOCUMENT_ROOT'] . '/restauranteSabados/assets/imagenes/platos/';
        if (!is_dir($carpeta)) mkdir($carpeta, 0755, true);

        $extension = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','webp','avif'];

        if (!in_array($extension, $permitidos)) {
            $errores['imagen'] = "Formato no válido";
        } elseif ($imagen['size'] > 2000000) {
            $errores['imagen'] = "Máximo 2MB";
        } else {
            $nombreImagen = md5(uniqid(rand(), true)) . "." . $extension;
            $ruta = $carpeta . $nombreImagen;

            if (move_uploaded_file($imagen['tmp_name'], $ruta)) {
                $rutaImagen = 'assets/imagenes/platos/' . $nombreImagen;
            }
        }
    }


    // Actualizar BD si no hay errores
    if (empty($errores)) {
        $stmt = $db->prepare("UPDATE plato SET nombre=?, descripcion=?, valor=?, activo=?, orden=?, imagen=? WHERE id=?");
        $stmt->bind_param("ssdissi", $nombre, $descripcion, $precio, $activo, $orden, $rutaImagen, $id);
        $stmt->execute();

        header("Location: index.php?ok=1");
        exit;
    }
}
?>



    <section class="container container_admin3">
        <div>
            <h1>EDITAR</h1>
            <div class="botondeadmin3">

            <a href="<?php echo BASE_URL;?>admin/menu/index.php">tabla</a>


            </div>
        </div>
    </section>

<section class="admin-container admin-container-admin">
    <main class="admin-card">
        <h1>Editar Plato</h1>

        <?php if ($mensaje): ?>
            <p class="mensajeOk"><?= $mensaje ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="admin-form">

            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($nombre) ?>">
            <?php if (isset($errores['nombre'])): ?>
                <p class="error"><?= $errores['nombre'] ?></p>
            <?php endif; ?>

            <label>Descripción</label>
            <textarea name="descripcion"><?= htmlspecialchars($descripcion) ?></textarea>
            <?php if (isset($errores['descripcion'])): ?>
                <p class="error"><?= $errores['descripcion'] ?></p>
            <?php endif; ?>

            <label>Precio</label>
            <input type="number" name="precio" min="0" value="<?= $precio ?>">
            <?php if (isset($errores['precio'])): ?>
                <p class="error"><?= $errores['precio'] ?></p>
            <?php endif; ?>

            <label>Orden</label>
            <input type="number" name="orden" value="<?= $orden ?>">

            <label>
                <input type="checkbox" name="activo" <?= $activo ? 'checked' : '' ?>> Activo
            </label>

            <label>Imagen</label>
            <input type="file" name="imagen" accept="image/*" id="inputImagen">
            <img id="previewImagen" src="<?= BASE_URL . ltrim($rutaImagen,'/') ?>?t=<?= time() ?>" width="120" style="margin-top:5px;">

            <input type="submit" value="Actualizar Plato" class="admin-btn">
        </form>
    </main>
</section>

<?php include '../../include/templates/footer.php'; ?>




