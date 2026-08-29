<?php

require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
$db = conectarDB();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 🔐 VALIDAR ID
    if (!isset($_POST['id'])) {
        header("Location: index.php");
        exit;
    }

    $id = intval($_POST['id']);

    if ($id <= 0) {
        header("Location: index.php");
        exit;
    }

    // obtener imagen para eliminar archivo físico
    $stmt = $db->prepare("SELECT imagen FROM plato WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $plato = $resultado->fetch_assoc();

    if ($plato) {

        // ELIMINAR IMAGEN DEL SERVIDOR 
        if (!empty($plato['imagen'])) {
            $rutaImagen = $_SERVER['DOCUMENT_ROOT'] . '/restauranteSabados/' . $plato['imagen'];

            if (file_exists($rutaImagen)) {
                unlink($rutaImagen);
            }
        }

        // ELIMINAR DE LA BASE DE DATOS
        $stmt = $db->prepare("DELETE FROM plato WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    // REDIRECCIÓN
    header("Location: index.php?eliminado=1");
    exit;
}
