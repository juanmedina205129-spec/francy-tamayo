<?php

require_once __DIR__ . '/../include/funciones.php';
auth();

require_once __DIR__ . '/../include/config/database.php';
$db = conectarDB();

incluirTemplates('header'); 


// include '../../include/templates/header.php'
?>

<body>
    <section class="container container_admin">
        <div>
            <h1>ACTUALIZAR MENU</h1>
        </div>
    </section>
</body>





<?php
include '../../include/templates/footer.php'
?>