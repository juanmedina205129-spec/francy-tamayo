<?php
require_once __DIR__ . '/../include/funciones.php';

iniciarSesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfValido($_POST['csrf_token'] ?? null)) {
    header('Location: ' . BASE_URL . 'admin/login.php');
    exit;
}

$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;

