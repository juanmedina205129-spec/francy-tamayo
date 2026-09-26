<?php
require_once __DIR__ . '/include/funciones.php';
require_once __DIR__ . '/include/pedidos.php';

header('Content-Type: application/json; charset=utf-8');

function responderPedido(int $codigo, bool $ok, string $mensaje, array $extra = []): never
{
    http_response_code($codigo);
    echo json_encode(['ok' => $ok, 'mensaje' => $mensaje] + $extra, JSON_UNESCAPED_UNICODE);
    exit;
}

function pedidoTexto(array $datos, string $campo): string
{
    return entradaTexto($datos[$campo] ?? '');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderPedido(405, false, 'Método no permitido.');
}

// El token obliga a cargar primero una página de la tienda (y por tanto a tener sesión),
// lo que permite limitar la frecuencia de envíos por visitante.
if (!csrfValido($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    responderPedido(403, false, 'Tu sesión expiró. Recarga la página e inténtalo de nuevo.');
}

$esperaMinima = 30;
$ultimoPedido = (int) ($_SESSION['ultimo_pedido'] ?? 0);
if ($ultimoPedido && time() - $ultimoPedido < $esperaMinima) {
    responderPedido(429, false, 'Ya recibimos una solicitud hace un momento. Espera unos segundos antes de enviar otra.');
}

$datos = json_decode(file_get_contents('php://input'), true);
if (!is_array($datos)) {
    responderPedido(400, false, 'Solicitud inválida.');
}

$origen = ($datos['origen'] ?? 'carrito') === 'contacto' ? 'contacto' : 'carrito';
$campos = [
    'nombre' => pedidoTexto($datos, 'name'),
    'telefono' => pedidoTexto($datos, 'phone'),
    'email' => pedidoTexto($datos, 'email'),
    'ciudad' => pedidoTexto($datos, 'city'),
    'tipo' => pedidoTexto($datos, 'commission_type'),
    'fecha' => pedidoTexto($datos, 'desired_date'),
    'tamano' => pedidoTexto($datos, 'size'),
    'presupuesto' => pedidoTexto($datos, 'budget'),
    'detalles' => pedidoTexto($datos, 'commission_details'),
    'entrega' => pedidoTexto($datos, 'delivery'),
    'direccion' => pedidoTexto($datos, 'address'),
    'notas' => pedidoTexto($datos, 'notes'),
];

$limites = ['nombre' => 120, 'telefono' => 50, 'email' => 150, 'ciudad' => 120, 'detalles' => 5000, 'direccion' => 255, 'notas' => 2000];
$etiquetasCampos = ['nombre' => 'nombre', 'telefono' => 'teléfono', 'email' => 'correo electrónico', 'ciudad' => 'ciudad', 'detalles' => 'detalles', 'direccion' => 'dirección', 'notas' => 'indicaciones adicionales'];
$errores = [];

foreach ($limites as $campo => $maximo) {
    if (mb_strlen($campos[$campo]) > $maximo) {
        $errores[] = "El campo {$etiquetasCampos[$campo]} supera los $maximo caracteres.";
    }
}

$obligatorios = ['nombre', 'telefono', 'tipo', 'detalles'];
if ($origen === 'carrito') {
    array_push($obligatorios, 'ciudad', 'entrega', 'direccion');
}
foreach ($obligatorios as $campo) {
    if ($campos[$campo] === '') {
        $errores[] = 'Completa los datos obligatorios.';
        break;
    }
}

if ($campos['telefono'] !== '' && !preg_match('/^\+?[0-9\s().-]{7,20}$/', $campos['telefono'])) {
    $errores[] = 'Escribe un número de teléfono válido.';
}
if ($campos['email'] !== '' && !filter_var($campos['email'], FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Escribe un correo electrónico válido.';
}
if ($campos['fecha'] !== '') {
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $campos['fecha']);
    if (!$fecha || $fecha->format('Y-m-d') !== $campos['fecha']) {
        $errores[] = 'La fecha de entrega no es válida.';
    } elseif ($fecha < new DateTimeImmutable('today')) {
        $errores[] = 'La fecha de entrega no puede estar en el pasado.';
    }
}
if (!in_array($campos['tipo'], TIPOS_ENCARGO, true)) {
    $errores[] = 'Selecciona un tipo de encargo válido.';
}
if ($campos['tamano'] !== '' && !in_array($campos['tamano'], TAMANOS_ENCARGO, true)) {
    $errores[] = 'Selecciona un tamaño válido.';
}
if ($campos['presupuesto'] !== '' && !in_array($campos['presupuesto'], PRESUPUESTOS_ENCARGO, true)) {
    $errores[] = 'Selecciona un presupuesto válido.';
}
if ($campos['entrega'] !== '' && !in_array($campos['entrega'], METODOS_ENTREGA, true)) {
    $errores[] = 'Selecciona un método de entrega válido.';
}

$items = $datos['items'] ?? [];
if ($origen === 'carrito' && (!is_array($items) || !$items)) {
    $errores[] = 'Añade al menos un producto al carrito.';
}

if ($errores) {
    responderPedido(422, false, $errores[0]);
}

try {
    $db = conectarDB();

    $lineas = [];
    $subtotal = 0.0;
    if ($origen === 'carrito') {
        [$lineas, $subtotal, $errorCarrito] = prepararLineasPedido($db, $items);
        if ($errorCarrito) {
            responderPedido(422, false, $errorCarrito);
        }
    }

    $valores = array_map(static fn(string $valor): ?string => $valor === '' ? null : $valor, $campos);
    $productosJson = json_encode($lineas, JSON_UNESCAPED_UNICODE);

    $stmt = $db->prepare('INSERT INTO pedidos (codigo, origen, nombre, telefono, email, ciudad, tipo_encargo, fecha_entrega, tamano, presupuesto, detalles, entrega, direccion, notas, productos, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $codigo = '';
    $stmt->bind_param(
        'sssssssssssssssd',
        $codigo,
        $origen,
        $valores['nombre'],
        $valores['telefono'],
        $valores['email'],
        $valores['ciudad'],
        $valores['tipo'],
        $valores['fecha'],
        $valores['tamano'],
        $valores['presupuesto'],
        $valores['detalles'],
        $valores['entrega'],
        $valores['direccion'],
        $valores['notas'],
        $productosJson,
        $subtotal
    );
    // El código es aleatorio; si por casualidad ya existe se genera otro.
    for ($intento = 1; ; $intento++) {
        $codigo = generarCodigoPedido();
        try {
            $stmt->execute();
            break;
        } catch (mysqli_sql_exception $duplicado) {
            if ($duplicado->getCode() !== 1062 || $intento >= 5) {
                throw $duplicado;
            }
        }
    }
    $_SESSION['ultimo_pedido'] = time();

    responderPedido(200, true, 'Tu encargo fue enviado. Te contactaremos por WhatsApp para confirmarlo.', [
        'codigo' => $codigo,
        'subtotal' => $subtotal,
        'productos' => $lineas,
        'seguimiento' => BASE_URL . 'seguimiento.php?codigo=' . rawurlencode($codigo),
    ]);
} catch (Throwable $error) {
    error_log('guardar_pedido: ' . $error->getMessage());
    responderPedido(500, false, 'No pudimos guardar el encargo. Inténtalo nuevamente.');
}
