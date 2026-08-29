
<?php

require_once __DIR__ . '/../include/funciones.php';
auth();

require_once __DIR__ . '/../include/config/database.php';
$db = conectarDB();

incluirTemplates('header'); 





// ======================
// CONSULTAS ESTADÍSTICAS
// ======================
$resultTotal = mysqli_query($db, "SELECT COUNT(*) AS total FROM plato");
$total = mysqli_fetch_assoc($resultTotal)['total'];

$resultActivos = mysqli_query($db, "SELECT COUNT(*) AS activos FROM plato WHERE activo=1");
$activos = mysqli_fetch_assoc($resultActivos)['activos'];

$resultInactivos = mysqli_query($db, "SELECT COUNT(*) AS inactivos FROM plato WHERE activo=0");
$inactivos = mysqli_fetch_assoc($resultInactivos)['inactivos'];
?>

<section class="admin-container admin-container-dashboard">

    <main class="admin-card dashboard-card">
        <h1>Dashboard Administrador</h1>

        <!-- ==================== -->
        <!-- ESTADÍSTICAS DE PLATOS -->
        <!-- ==================== -->
        <div class="adm-stats">
            <div class="stat-card stat-total">
                <p>Total Platos</p>
                <h2><?= $total ?></h2>
            </div>
            <div class="stat-card stat-activos">
                <p>Activos</p>
                <h2><?= $activos ?></h2>
            </div>
            <div class="stat-card stat-inactivos">
                <p>Inactivos</p>
                <h2><?= $inactivos ?></h2>
            </div>
        </div>

        <!-- ==================== -->
        <!-- BOTONES DE ACCIÓN -->
        <!-- ==================== -->
        <div class="adm-buttons">
            <a href="menu/crear.php" class="btn-crear">➕ Crear Plato</a>
            <a href="menu/index.php" class="btn-editar">✏️ Editar Listado</a>
            <a href="<?php echo BASE_URL; ?>index.php" class="btn-salir">🚪 Salir</a>
        </div>

    </main>
</section>

<?php include '../include/templates/footer.php'; ?>
