<?php

// La tienda opera en Colombia. Sin esto PHP usa la zona del php.ini (en XAMPP, Europe/Berlin)
// y fechas como "hoy" o la hora de un pedido quedan desfasadas varias horas.
const ZONA_HORARIA = 'America/Bogota';
date_default_timezone_set(ZONA_HORARIA);

/**
 * Lee un valor de $_GET, $_POST o de un JSON como texto. Si llega una lista u otro tipo
 * (por ejemplo "nombre[]=x"), devuelve cadena vacía en lugar de provocar un error.
 */
function entradaTexto(mixed $valor): string {
    return is_scalar($valor) ? trim((string) $valor) : '';
}

/** Igual que entradaTexto() pero sin recortar espacios: en una contraseña son significativos. */
function entradaClave(mixed $valor): string {
    return is_string($valor) ? $valor : '';
}

/**
 * Ruta pública del proyecto (por ejemplo "/francytamayo/" en XAMPP o "/" en un dominio propio).
 * Se puede fijar con la variable de entorno APP_BASE_URL; si no existe se calcula comparando
 * la carpeta del proyecto con el DOCUMENT_ROOT de Apache.
 */
function calcularBaseUrl(): string {
    $configurada = getenv('APP_BASE_URL');
    if ($configurada) {
        return '/' . trim($configurada, '/') . (trim($configurada, '/') === '' ? '' : '/');
    }

    $raizProyecto = str_replace('\\', '/', (string) realpath(dirname(__DIR__)));
    $raizDocumentos = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));

    if ($raizDocumentos === '' || stripos($raizProyecto, $raizDocumentos) !== 0) {
        return '/';
    }

    $relativa = trim(substr($raizProyecto, strlen($raizDocumentos)), '/');
    return $relativa === '' ? '/' : '/' . $relativa . '/';
}

define('BASE_URL', calcularBaseUrl());

function incluirTemplates(string $nombre, array $datos = []): void {
    // La sesión se inicia antes de imprimir HTML para no depender del output_buffering de PHP.
    iniciarSesion();
    extract($datos);
    include __DIR__ . "/templates/$nombre.php";
}

function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
            'cookie_secure' => ($_SERVER['HTTPS'] ?? 'off') !== 'off',
            'use_strict_mode' => true,
        ]);
    }
}

function auth(): void {
    iniciarSesion();

    if (empty($_SESSION['login'])) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

function csrfToken(): string {
    iniciarSesion();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Se llama al iniciar sesión o cambiar privilegios para no reutilizar el token anterior. */
function renovarCsrfToken(): void {
    iniciarSesion();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function csrfValido(mixed $token): bool {
    iniciarSesion();
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
