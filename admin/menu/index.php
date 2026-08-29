<?php

require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
$db = conectarDB();



$scripts = [ 'datatable', 'admin']; // importante para JS

incluirTemplates('header'); 



// CONSULTA
$query = "SELECT * FROM plato ORDER BY id ASC";
$resultado = mysqli_query($db, $query);

?>

<section class="container admin-platos-section">

    <main class="admin-platos-card">
        <h1 class="admin-platos-title">Listado de Platos</h1>

        <div class="admin-platos-nav">
            <a href="<?= BASE_URL ?>admin/menu/crear.php" class="admin-platos-btn-create">➕ Crear Plato</a>
            <a href="<?= BASE_URL ?>admin/index.php" class="admin-platos-btn-regresar">regresar</a>
        </div>

        <table id="tablaPlatos" class="admin-platos-table display responsive nowrap" style="width:100%">

            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Imagen</th>
                    <th>Orden</th>
                    <th>Activo</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php while ($plato = mysqli_fetch_assoc($resultado)): ?>
                    <tr>
                        <td></td>
                        <td><?= $plato['id']; ?></td>
                        <td><?= htmlspecialchars($plato['nombre']); ?></td>
                        <td>$<?= number_format($plato['valor'], 0); ?></td>
                        <td>
                            <img src="<?= BASE_URL . $plato['imagen']; ?>" class="admin-platos-img">
                        </td>
                        <td><?= $plato['orden']; ?></td>
                        <td class="admin-platos-status">
                            <?= $plato['activo'] ? '✅' : '❌'; ?>
                        </td>

                        <td class="admin-platos-actions">
                            <a href="editar.php?id=<?= $plato['id']; ?>" class="admin-platos-btn-edit">Editar</a>

                            <form method="POST" action="eliminar.php" class="admin-platos-delete-form">
                                <input type="hidden" name="id" value="<?= $plato['id']; ?>">
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
