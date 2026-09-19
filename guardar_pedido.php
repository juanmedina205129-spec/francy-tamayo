<?php
require_once __DIR__ . '/include/pedidos.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);
if (!is_array($datos)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'mensaje' => 'Solicitud inválida.']);
    exit;
}

function pedidoTexto(array $datos, string $campo): string
{
    return trim((string) ($datos[$campo] ?? ''));
}

$nombre = pedidoTexto($datos, 'name');
$telefono = pedidoTexto($datos, 'phone');
$email = pedidoTexto($datos, 'email');
$ciudad = pedidoTexto($datos, 'city');
$tipo = pedidoTexto($datos, 'commission_type');
$fecha = pedidoTexto($datos, 'desired_date');
$tamano = pedidoTexto($datos, 'size');
$presupuesto = pedidoTexto($datos, 'budget');
$detalles = pedidoTexto($datos, 'commission_details');
$entrega = pedidoTexto($datos, 'delivery');
$direccion = pedidoTexto($datos, 'address');
$notas = pedidoTexto($datos, 'notes');
$productos = $datos['items'] ?? [];

if (!$nombre || !$telefono || !$ciudad || !$tipo || !$detalles || !$entrega || !$direccion || !is_array($productos) || !$productos) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => 'Completa los datos obligatorios y añade al menos un producto.']);
    exit;
}

$productosLimpios = [];
$subtotal = 0.0;
foreach ($productos as $producto) {
    $cantidad = max(1, (int) ($producto['quantity'] ?? 0));
    $precio = max(0, (float) ($producto['price'] ?? 0));
    $nombreProducto = trim((string) ($producto['name'] ?? ''));
    if (!$nombreProducto) continue;
    $productosLimpios[] = ['nombre' => $nombreProducto, 'cantidad' => $cantidad, 'precio' => $precio];
    $subtotal += $cantidad * $precio;
}

if (!$productosLimpios) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => 'El carrito no contiene productos válidos.']);
    exit;
}

try {
    $db = conectarDB();
    prepararTablaPedidos($db);
    $productosJson = json_encode($productosLimpios, JSON_UNESCAPED_UNICODE);
    $fecha = $fecha ?: null;
    $email = $email ?: null;
    $tamano = $tamano ?: null;
    $presupuesto = $presupuesto ?: null;
    $notas = $notas ?: null;
    $stmt = $db->prepare('INSERT INTO pedidos (nombre, telefono, email, ciudad, tipo_encargo, fecha_entrega, tamano, presupuesto, detalles, entrega, direccion, notas, productos, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssssssssssd', $nombre, $telefono, $email, $ciudad, $tipo, $fecha, $tamano, $presupuesto, $detalles, $entrega, $direccion, $notas, $productosJson, $subtotal);
    $stmt->execute();
    echo json_encode(['ok' => true, 'mensaje' => 'Tu encargo fue enviado. Te contactaremos por WhatsApp para confirmarlo.']);
} catch (Throwable $error) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No pudimos guardar el encargo. Inténtalo nuevamente.']);
}
