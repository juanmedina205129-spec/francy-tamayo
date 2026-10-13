<?php

/**
 * Guardado de imágenes subidas desde formularios (productos del catálogo y fotos de pedidos).
 * Los archivos quedan en assets/imagenes/<carpeta>/ con un nombre aleatorio, y el tipo se
 * comprueba leyendo el contenido: nunca se confía en la extensión ni en el tipo que dice
 * el navegador.
 */
const TIPOS_IMAGEN_PERMITIDOS = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/avif' => 'avif',
];

/**
 * @return array{0: ?string, 1: ?string} [ruta relativa al proyecto, error]
 */
function guardarImagenSubida(array $archivo, string $carpeta, int $maximoBytes): array
{
    if (!preg_match('/^[a-z]+$/', $carpeta)) {
        return [null, 'No se pudo preparar la carpeta de imágenes.'];
    }

    $limite = 'La imagen no puede superar ' . str_replace('.', ',', (string) round($maximoBytes / 1000000, 1)) . ' MB.';
    $error = $archivo['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
        return [null, $limite];
    }
    if ($error !== UPLOAD_ERR_OK || !is_string($archivo['tmp_name'] ?? null) || $archivo['tmp_name'] === '') {
        return [null, 'Selecciona una imagen válida.'];
    }
    if (($archivo['size'] ?? 0) > $maximoBytes) {
        return [null, $limite];
    }

    $tipoMime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!isset(TIPOS_IMAGEN_PERMITIDOS[$tipoMime])) {
        return [null, 'Formato no válido. Usa JPG, PNG, WEBP o AVIF.'];
    }

    $directorio = dirname(__DIR__) . '/assets/imagenes/' . $carpeta;
    if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
        return [null, 'No se pudo preparar la carpeta de imágenes.'];
    }

    $nombre = bin2hex(random_bytes(16)) . '.' . TIPOS_IMAGEN_PERMITIDOS[$tipoMime];
    if (!move_uploaded_file($archivo['tmp_name'], $directorio . '/' . $nombre)) {
        return [null, 'No se pudo guardar la imagen.'];
    }

    return ['assets/imagenes/' . $carpeta . '/' . $nombre, null];
}

/**
 * Borra una imagen guardada con guardarImagenSubida(). Ignora cualquier ruta que no tenga
 * exactamente esa forma, así que las imágenes de ejemplo del catálogo nunca se eliminan.
 */
function eliminarImagenSubida(?string $ruta, string $carpeta): void
{
    $patron = '#^assets/imagenes/' . preg_quote($carpeta, '#') . '/[a-f0-9]{32}\.(jpg|png|webp|avif)$#';
    if (!$ruta || !preg_match('/^[a-z]+$/', $carpeta) || !preg_match($patron, $ruta)) {
        return;
    }

    $archivo = dirname(__DIR__) . '/' . $ruta;
    if (is_file($archivo)) {
        unlink($archivo);
    }
}
