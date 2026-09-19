<?php

/**
 * Utilidades compartidas para las operaciones de catálogo del administrador.
 * Las imágenes se guardan dentro del proyecto, sin depender del nombre con el
 * que este se haya publicado en Apache/XAMPP.
 */
function guardarImagenProducto(array $archivo): array
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($archivo['tmp_name'])) {
        return [null, 'Selecciona una imagen válida.'];
    }

    if (($archivo['size'] ?? 0) > 2500000) {
        return [null, 'La imagen no puede superar 2.5 MB.'];
    }

    $tipoMime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    $extensiones = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    if (!isset($extensiones[$tipoMime])) {
        return [null, 'Formato no válido. Usa JPG, PNG, WEBP o AVIF.'];
    }

    $directorio = dirname(__DIR__) . '/assets/imagenes/productos';
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        return [null, 'No se pudo preparar la carpeta de imágenes.'];
    }

    $nombre = bin2hex(random_bytes(16)) . '.' . $extensiones[$tipoMime];
    if (!move_uploaded_file($archivo['tmp_name'], $directorio . '/' . $nombre)) {
        return [null, 'No se pudo guardar la imagen.'];
    }

    return ['assets/imagenes/productos/' . $nombre, null];
}

function eliminarImagenProductoSubida(?string $ruta): void
{
    if (!$ruta || !preg_match('#^assets/imagenes/productos/[a-f0-9]{32}\.(jpg|png|webp|avif)$#', $ruta)) {
        return;
    }

    $archivo = dirname(__DIR__) . '/' . $ruta;
    if (is_file($archivo)) {
        unlink($archivo);
    }
}
