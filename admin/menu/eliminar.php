<?php

require_once __DIR__ . '/../../include/funciones.php';
auth();

require_once __DIR__ . '/../../include/config/database.php';
require_once __DIR__ . '/../../include/producto_admin.php';
$db = conectarDB();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        header('Location: index.php?error=seguridad');
        exit;
    }

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

    // obtener imagen para eliminar archivo fisico
    $stmt = $db->prepare("SELECT imagen FROM productos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $producto = $resultado->fetch_assoc();

    if ($producto) {

        // ELIMINAR DE LA BASE DE DATOS
        $stmt = $db->prepare("DELETE FROM productos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        eliminarImagenProductoSubida($producto['imagen']);
    }

    // REDIRECCIÓN
    header("Location: index.php?eliminado=1");
    exit;
}
