<?php
require_once __DIR__ . '/imagenes.php';

/**
 * Utilidades compartidas para las operaciones de catálogo del administrador.
 * Las imágenes se guardan dentro del proyecto, sin depender del nombre con el
 * que este se haya publicado en Apache/XAMPP.
 */
const CARPETA_IMAGENES_PRODUCTO = 'productos';
const MAXIMO_IMAGEN_PRODUCTO = 2500000; // 2,5 MB

function guardarImagenProducto(array $archivo): array
{
    return guardarImagenSubida($archivo, CARPETA_IMAGENES_PRODUCTO, MAXIMO_IMAGEN_PRODUCTO);
}

function eliminarImagenProductoSubida(?string $ruta): void
{
    eliminarImagenSubida($ruta, CARPETA_IMAGENES_PRODUCTO);
}
