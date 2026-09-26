<?php
require_once __DIR__ . '/config/database.php';

// La estructura de la tabla vive únicamente en database/schema.sql.

const ESTADOS_PEDIDO = [
    'nuevo' => 'Nuevo',
    'en_proceso' => 'En proceso',
    'cerrado' => 'Cerrado',
];

// Pasos que ve el cliente en seguimiento.php, en orden.
const PASOS_SEGUIMIENTO = [
    'nuevo' => ['titulo' => 'Recibido', 'detalle' => 'Tenemos tu solicitud y la revisaremos pronto.'],
    'en_proceso' => ['titulo' => 'En proceso', 'detalle' => 'Confirmamos los detalles y tu pieza está en preparación.'],
    'cerrado' => ['titulo' => 'Finalizado', 'detalle' => 'Tu pedido fue entregado o cerrado. ¡Gracias por tu confianza!'],
];

const TIPOS_ENCARGO =['Retrato personalizado', 'Pintura original', 'Camiseta personalizada', 'Otro encargo'];
const TAMANOS_ENCARGO = ['Pequeño', 'Mediano', 'Grande', 'Talla S', 'Talla M', 'Talla L', 'Talla XL'];
const PRESUPUESTOS_ENCARGO = ['Hasta $150.000', '$150.000 — $300.000', '$300.000 — $500.000', 'Más de $500.000'];
const METODOS_ENTREGA = ['Envío a domicilio', 'Recogida acordada'];

/**
 * Busca los productos del carrito en la base de datos y devuelve las líneas del pedido con el
 * precio real. Nunca se confía en el precio que envía el navegador.
 *
 * @return array{0: array, 1: float, 2: ?string} [líneas, subtotal, error]
 */
function prepararLineasPedido(mysqli $db, array $items): array
{
    if (count($items) > 50) {
        return [[], 0.0, 'El carrito tiene demasiados productos.'];
    }

    $stmt = $db->prepare('SELECT id, nombre, precio, estado FROM productos WHERE activo = 1 AND (id = ? OR nombre = ?) LIMIT 1');
    $lineas = [];
    $subtotal = 0.0;

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        $id = (int) entradaTexto($item['id'] ?? '');
        $nombre = entradaTexto($item['name'] ?? '');
        $stmt->bind_param('is', $id, $nombre);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();

        if (!$producto) {
            return [[], 0.0, 'Uno de los productos del carrito ya no está disponible. Revísalo e inténtalo de nuevo.'];
        }
        if ($producto['estado'] === 'agotado') {
            return [[], 0.0, sprintf('"%s" está agotado. Retíralo del carrito para continuar.', $producto['nombre'])];
        }

        $clave = (int) $producto['id'];
        $cantidad = min(20, max(1, (int) entradaTexto($item['quantity'] ?? '1')));
        $precio = (float) $producto['precio'];

        if (isset($lineas[$clave])) {
            $lineas[$clave]['cantidad'] = min(20, $lineas[$clave]['cantidad'] + $cantidad);
        } else {
            $lineas[$clave] = ['id' => $clave, 'nombre' => $producto['nombre'], 'cantidad' => $cantidad, 'precio' => $precio];
        }
    }

    foreach ($lineas as $linea) {
        $subtotal += $linea['cantidad'] * $linea['precio'];
    }

    return [array_values($lineas), $subtotal, null];
}

/**
 * Código público del pedido, por ejemplo "FT-7K3M9Q". Usa un alfabeto sin caracteres que
 * se confundan al dictarlos (0/O, 1/I/L).
 */
function generarCodigoPedido(): string
{
    $alfabeto = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $codigo = '';
    for ($i = 0; $i < 6; $i++) {
        $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
    }
    return 'FT-' . $codigo;
}

function normalizarCodigoPedido(string $codigo): string
{
    $codigo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $codigo));
    // Solo se quita el prefijo si viene completo ("FT" + 6), porque un código puede empezar por FT.
    if (strlen($codigo) === 8 && str_starts_with($codigo, 'FT')) {
        $codigo = substr($codigo, 2);
    }
    return 'FT-' . $codigo;
}

/**
 * Compara dos teléfonos solo por sus dígitos, aceptando que uno incluya el indicativo
 * del país (+57) y el otro no.
 */
function telefonosCoinciden(string $guardado, string $escrito): bool
{
    $a = preg_replace('/\D/', '', $guardado);
    $b = preg_replace('/\D/', '', $escrito);
    if (strlen($a) < 7 || strlen($b) < 7) {
        return false;
    }
    return str_ends_with($a, $b) || str_ends_with($b, $a);
}

function buscarPedidoParaCliente(mysqli $db, string $codigo, string $telefono): ?array
{
    $codigo = normalizarCodigoPedido($codigo);
    $stmt = $db->prepare('SELECT * FROM pedidos WHERE codigo = ? LIMIT 1');
    $stmt->bind_param('s', $codigo);
    $stmt->execute();
    $pedido = $stmt->get_result()->fetch_assoc();

    return $pedido && telefonosCoinciden($pedido['telefono'], $telefono) ? $pedido : null;
}
