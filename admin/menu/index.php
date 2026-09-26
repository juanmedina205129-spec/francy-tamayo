<?php

require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
$db = conectarDB();



$scripts = [ 'datatable', 'admin']; // importante para JS

incluirTemplates('header', [
    'estilosExtra' => [
        'https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css',
        'https://cdn.datatables.net/responsive/3.0.2/css/responsive.dataTables.min.css',
    ],
    'scriptsExtraHead' => [
        'https://code.jquery.com/jquery-3.7.1.min.js',
        'https://cdn.datatables.net/2.0.8/js/dataTables.min.js',
        'https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.min.js',
    ],
]);



// CONSULTA
$query = "SELECT * FROM productos ORDER BY orden ASC, id ASC";
$resultado = mysqli_query($db, $query);
$mensaje = '';
$mensajeEsError = false;
if (isset($_GET['ok'])) {
    $mensaje = 'El producto se ' . (entradaTexto($_GET['ok']) === 'creado' ? 'creó' : 'actualizó') . ' correctamente.';
} elseif (isset($_GET['eliminado'])) {
    $mensaje = 'El producto se eliminó correctamente.';
} elseif (($_GET['error'] ?? null) === 'seguridad') {
    $mensaje = 'No se pudo completar la acción: tu sesión de seguridad expiró. Recarga la página e inténtalo de nuevo.';
    $mensajeEsError = true;
} elseif (($_GET['error'] ?? null) === 'no_encontrado') {
    $mensaje = 'El producto que buscabas ya no existe (puede que se haya eliminado desde otra pestaña).';
    $mensajeEsError = true;
}

?>

<section class="container admin-productos-section">

    <main class="admin-productos-card">
        <div class="admin-productos-heading">
            <div>
                <span class="admin-productos-eyebrow">Administración · Catálogo</span>
                <h1 class="admin-productos-title">Gestiona tus productos</h1>
                <p>Organiza las piezas que verán tus clientes: agrega, actualiza o retira productos del catálogo.</p>
            </div>
            <span class="admin-productos-total"><?= mysqli_num_rows($resultado); ?> productos</span>
        </div>

        <?php if ($mensaje): ?>
            <p class="admin-productos-notice<?= $mensajeEsError ? ' admin-productos-notice-error' : '' ?>" role="<?= $mensajeEsError ? 'alert' : 'status' ?>"><?= htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <div class="admin-productos-nav">
            <a href="<?= BASE_URL ?>admin/menu/crear.php" class="admin-productos-btn-create"><span aria-hidden="true">＋</span> Agregar producto</a>
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-productos-btn-regresar">← Panel de administración</a>
        </div>

        <table id="tablaProductos" class="admin-productos-table display responsive nowrap" style="width:100%">

            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Tipo</th>
                    <th>Precio</th>
                    <th>Imagen</th>
                    <th>Estado</th>
                    <th>Orden</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($producto = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td></td>
                        <td><?= $producto['id']; ?></td>
                        <td><?= htmlspecialchars($producto['nombre']); ?></td>
                        <td><?= htmlspecialchars(ucfirst($producto['categoria'])); ?></td>
                        <td><?= htmlspecialchars($producto['tipo']); ?></td>
                        <td>$<?= number_format((float) $producto['precio'], 0, ',', '.'); ?></td>
                        <td>
                            <img src="<?= htmlspecialchars(BASE_URL . $producto['imagen']); ?>" alt="<?= htmlspecialchars($producto['nombre']); ?>" class="admin-productos-img">
                        </td>
                        <td><?= htmlspecialchars(ucfirst($producto['estado'])); ?></td>
                        <td><?= $producto['orden']; ?></td>
                        <td class="admin-productos-status">
                            <span class="admin-productos-status-pill <?= $producto['activo'] ? 'is-active' : 'is-inactive'; ?>"><?= $producto['activo'] ? 'Publicado' : 'Oculto'; ?></span>
                        </td>

                        <td class="admin-productos-actions">
                            <a href="<?= BASE_URL ?>admin/menu/editar.php?id=<?= $producto['id']; ?>" class="admin-productos-btn-edit"><span aria-hidden="true">✎</span> Editar</a>
                            <button type="button" class="admin-productos-btn-delete" data-delete-product data-product-id="<?= $producto['id']; ?>" data-product-name="<?= htmlspecialchars($producto['nombre'], ENT_QUOTES); ?>"><span aria-hidden="true">×</span> Eliminar</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <dialog class="admin-delete-dialog" id="delete-product-dialog" aria-labelledby="delete-dialog-title">
            <form method="POST" action="eliminar.php" id="delete-product-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()); ?>">
                <input type="hidden" name="id" id="delete-product-id">
                <span class="admin-delete-dialog-icon" aria-hidden="true">!</span>
                <h2 id="delete-dialog-title">¿Eliminar producto?</h2>
                <p>Vas a retirar <strong id="delete-product-name"></strong> del catálogo. Esta acción no se puede deshacer.</p>
                <div class="admin-delete-dialog-actions">
                    <button type="button" class="admin-dialog-cancel" data-delete-cancel>Cancelar</button>
                    <button type="submit" class="admin-dialog-confirm">Sí, eliminar</button>
                </div>
            </form>
        </dialog>

    </main>
</section>

<?php include '../../include/templates/footer.php'; ?>
