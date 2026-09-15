<?php

require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
$db = conectarDB();



$scripts = [ 'datatable', 'admin']; // importante para JS

incluirTemplates('header'); 



// CONSULTA
$query = "SELECT * FROM productos ORDER BY orden ASC, id ASC";
$resultado = mysqli_query($db, $query);

?>

<section class="container admin-platos-section">

    <main class="admin-platos-card">
        <h1 class="admin-platos-title">Listado de Productos</h1>

        <div class="admin-platos-nav">
            <a href="<?= BASE_URL ?>admin/menu/crear.php" class="admin-platos-btn-create">Crear producto</a>
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-platos-btn-regresar">regresar</a>
        </div>

        <table id="tablaPlatos" class="admin-platos-table display responsive nowrap" style="width:100%">

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
                            <img src="<?= BASE_URL . $producto['imagen']; ?>" class="admin-platos-img">
                        </td>
                        <td><?= htmlspecialchars(ucfirst($producto['estado'])); ?></td>
                        <td><?= $producto['orden']; ?></td>
                        <td class="admin-platos-status">
                            <?= $producto['activo'] ? 'Sí' : 'No'; ?>
                        </td>

                        <td class="admin-platos-actions">
                            <a href="editar.php?id=<?= $producto['id']; ?>" class="admin-platos-btn-edit">Editar</a>

                            <form method="POST" action="eliminar.php" class="admin-platos-delete-form">
                                <input type="hidden" name="id" value="<?= $producto['id']; ?>">
                                <button type="submit" class="admin-platos-btn-delete">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    </main>
</section>

<?php include '../../include/templates/footer.php'; ?>
