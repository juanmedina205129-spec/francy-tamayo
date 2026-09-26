<?php
require_once __DIR__ . '/../include/funciones.php';
require_once __DIR__ . '/../include/productos.php';
require_once __DIR__ . '/../include/pedidos.php';
auth();

$conteo = contarProductosPorCategoria();
$conteoEstados = ['disponible' => 0, 'encargo' => 0, 'agotado' => 0];
$pedidosNuevos = 0;
try {
    $db = conectarDB();
    $resultadoEstados = $db->query('SELECT estado, COUNT(*) AS total FROM productos WHERE activo = 1 GROUP BY estado');
    while ($fila = $resultadoEstados->fetch_assoc()) {
        $conteoEstados[$fila['estado']] = (int) $fila['total'];
    }
    $pedidosNuevos = (int) $db->query("SELECT COUNT(*) AS total FROM pedidos WHERE estado = 'nuevo'")->fetch_assoc()['total'];
} catch (Throwable $error) {
    // El dashboard sigue mostrando el catálogo base si la base de datos aún no está disponible.
}
$catalogo = [
    ['nombre' => 'Pinturas', 'cantidad' => $conteo['pinturas'], 'detalle' => 'Obras disponibles en el catalogo', 'enlace' => BASE_URL . 'pinturas.php', 'icono' => '✦'],
    ['nombre' => 'Retratos', 'cantidad' => $conteo['retratos'], 'detalle' => 'Encargos de mascotas y animales', 'enlace' => BASE_URL . 'retratos.php', 'icono' => '♡'],
    ['nombre' => 'Camisetas', 'cantidad' => $conteo['camisetas'], 'detalle' => 'Diseños listos para personalizar', 'enlace' => BASE_URL . 'camisetas.php', 'icono' => '◌'],
];

$totalProductos = array_sum(array_column($catalogo, 'cantidad'));
$usuario = htmlspecialchars($_SESSION['usuario'] ?? 'Administradora');

incluirTemplates('header');
?>

<main class="admin-dashboard-page">
    <section class="admin-dashboard container" aria-labelledby="dashboard-title">
        <div class="admin-welcome">
            <div>
                <span class="admin-eyebrow">Administración · Francy Tamayo</span>
                <h1 id="dashboard-title">Hola, <?= $usuario ?></h1>
                <p>Desde aquí tienes una vista clara del catálogo público y accesos rápidos para revisarlo.</p>
            </div>
            <a href="<?= BASE_URL ?>index.php" class="admin-outline-link">Ver tienda ↗</a>
        </div>

        <section class="admin-overview" aria-label="Resumen del catálogo">
            <article class="admin-total-card">
                <span class="admin-card-icon">✦</span>
                <div><p>Productos publicados</p><strong><?= $totalProductos ?></strong></div>
                <div class="admin-state-counts"><span><?= $conteoEstados['disponible'] ?> disponibles</span><span><?= $conteoEstados['encargo'] ?> por encargo</span><span><?= $conteoEstados['agotado'] ?> agotados</span></div>
                <small><?= $pedidosNuevos ?> encargos nuevos por revisar</small>
            </article>
            <?php foreach ($catalogo as $categoria): ?>
                <article class="admin-stat-card">
                    <span class="admin-card-icon"><?= $categoria['icono'] ?></span>
                    <p><?= $categoria['nombre'] ?></p>
                    <strong><?= $categoria['cantidad'] ?></strong>
                    <span><?= $categoria['detalle'] ?></span>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-actions-panel" aria-labelledby="admin-actions-title">
            <div class="admin-panel-heading">
                <div>
                    <span class="admin-eyebrow">Acciones rápidas</span>
                    <h2 id="admin-actions-title">Revisa el catálogo</h2>
                </div>
                <p>Los enlaces se abren en la tienda para comprobar cómo ven los clientes cada sección.</p>
            </div>

            <div class="admin-action-grid">
                <a href="<?= BASE_URL ?>admin/menu/index.php" class="admin-action-card admin-action-featured">
                    <span>▦</span>
                    <div><strong>Gestionar productos</strong><small>Agregar, editar o eliminar del catálogo</small></div>
                    <b>→</b>
                </a>
                <?php foreach ($catalogo as $categoria): ?>
                    <a href="<?= $categoria['enlace'] ?>" class="admin-action-card">
                        <span><?= $categoria['icono'] ?></span>
                        <div><strong><?= $categoria['nombre'] ?></strong><small>Ver sección pública</small></div>
                        <b>→</b>
                    </a>
                <?php endforeach; ?>
                <a href="<?= BASE_URL ?>admin/pedidos.php" class="admin-action-card admin-action-featured">
                    <span>✉</span>
                    <div><strong>Encargos personalizados</strong><small>Consultar pedidos del carrito y del formulario de contacto</small></div>
                    <b>→</b>
                </a>
                <a href="<?= BASE_URL ?>admin/cambiar-clave.php" class="admin-action-card">
                    <span>⚿</span>
                    <div><strong>Cambiar contraseña</strong><small>Actualiza la clave de acceso al panel</small></div>
                    <b>→</b>
                </a>
            </div>
        </section>
    </section>
</main>

<?php include __DIR__ . '/../include/templates/footer.php'; ?>
