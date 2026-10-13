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

// Los pedidos nuevos usan el primer prefijo. Los demás son de la marca anterior y solo se
// aceptan al consultar el seguimiento de pedidos antiguos.
const PREFIJOS_CODIGO_PEDIDO = ['NA', 'FT'];

/**
 * Código público del pedido, por ejemplo "NA-7K3M9Q". Usa un alfabeto sin caracteres que
 * se confundan al dictarlos (0/O, 1/I/L).
 */
function generarCodigoPedido(): string
{
    $alfabeto = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $codigo = '';
    for ($i = 0; $i < 6; $i++) {
        $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
    }
    return PREFIJOS_CODIGO_PEDIDO[0] . '-' . $codigo;
}

/**
 * Códigos guardados que pueden corresponder a lo que escribió el cliente: con o sin guion,
 * en minúsculas, con prefijo "NA" o "FT" (pedidos antiguos) o solo los 6 caracteres.
 * La variante con el prefijo escrito va primero.
 *
 * @return string[]
 */
function variantesCodigoPedido(string $codigo): array
{
    $codigo = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $codigo));
    $variantes = [];

    // Solo se interpreta como prefijo si el código viene completo (prefijo + 6), porque los
    // 6 caracteres aleatorios también pueden empezar por "NA" o "FT".
    if (strlen($codigo) === 8 && in_array(substr($codigo, 0, 2), PREFIJOS_CODIGO_PEDIDO, true)) {
        $variantes[] = substr($codigo, 0, 2) . '-' . substr($codigo, 2);
        $codigo = substr($codigo, 2);
    }

    if (strlen($codigo) === 6) {
        foreach (PREFIJOS_CODIGO_PEDIDO as $prefijo) {
            $variantes[] = $prefijo . '-' . $codigo;
        }
    }

    return array_values(array_unique($variantes));
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
    $stmt = $db->prepare('SELECT * FROM pedidos WHERE codigo = ? LIMIT 1');

    foreach (variantesCodigoPedido($codigo) as $variante) {
        $stmt->bind_param('s', $variante);
        $stmt->execute();
        $pedido = $stmt->get_result()->fetch_assoc();

        if ($pedido && telefonosCoinciden($pedido['telefono'], $telefono)) {
            return $pedido;
        }
    }

    return null;
}

function buscarPedidoPorId(mysqli $db, int $id): ?array
{
    $stmt = $db->prepare('SELECT * FROM pedidos WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc() ?: null;
}

/* ---------- Cupo de encargos ---------- */

// La artista trabaja sola: solo se aceptan pedidos nuevos mientras los activos (estado
// "nuevo" o "en_proceso") no lleguen al cupo. Se cambia desde admin/pedidos.php.
const CUPO_ENCARGOS_DEFECTO = 6;

// Con true, el cupo lleno bloquea los pedidos: contacto.php y carrito.php muestran "Agenda completa"
// con el botón deshabilitado y guardar_pedido.php responde 409. Con false los pedidos siempre se
// guardan y los formularios solo muestran una nota (útil para pruebas).
const CUPO_BLOQUEA_PEDIDOS = true;
const CUPO_ENCARGOS_MAXIMO = 100;

/** Máximo de encargos activos a la vez. 0 significa que los encargos están pausados. */
function obtenerCupoEncargos(mysqli $db, bool $bloquear = false): int
{
    // Con FOR UPDATE (dentro de una transacción) dos pedidos simultáneos no pueden ocupar
    // el mismo cupo: el segundo espera a que termine el primero.
    $stmt = $db->prepare("SELECT valor FROM configuracion WHERE clave = 'cupo_encargos'" . ($bloquear ? ' FOR UPDATE' : ''));
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();

    if (!$fila || !ctype_digit((string) $fila['valor'])) {
        return CUPO_ENCARGOS_DEFECTO;
    }
    return min(CUPO_ENCARGOS_MAXIMO, (int) $fila['valor']);
}

function guardarCupoEncargos(mysqli $db, int $cupo): void
{
    $valor = (string) min(CUPO_ENCARGOS_MAXIMO, max(0, $cupo));
    $stmt = $db->prepare("INSERT INTO configuracion (clave, valor) VALUES ('cupo_encargos', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    $stmt->bind_param('s', $valor);
    $stmt->execute();
}

/**
 * @return array{cupo: int, ocupados: int, disponibles: int, lleno: bool}
 */
function estadoCupoEncargos(mysqli $db, bool $bloquear = false): array
{
    $cupo = obtenerCupoEncargos($db, $bloquear);
    $ocupados = (int) $db->query("SELECT COUNT(*) AS total FROM pedidos WHERE estado IN ('nuevo', 'en_proceso')")->fetch_assoc()['total'];

    return [
        'cupo' => $cupo,
        'ocupados' => $ocupados,
        'disponibles' => max(0, $cupo - $ocupados),
        'lleno' => $ocupados >= $cupo,
    ];
}

/** Estado del cupo para las páginas públicas; null si la base de datos no está disponible. */
function estadoCupoPublico(): ?array
{
    static $consultado = false, $estado = null;
    if (!$consultado) {
        $consultado = true;
        try {
            $db = conectarDB();
            $estado = estadoCupoEncargos($db);
        } catch (Throwable $error) {
            $estado = null;
        }
    }
    return $estado;
}

function mensajeCupoLleno(?array $estado = null): string
{
    if ($estado && $estado['cupo'] === 0) {
        return 'Por ahora no estamos recibiendo encargos nuevos. Escríbenos por WhatsApp y te avisamos cuando se abra la agenda.';
    }
    return 'Nuestra agenda de encargos está completa por ahora. Escríbenos por WhatsApp y te avisamos apenas se libere un cupo.';
}

/* ---------- Fotos del pedido y boceto ---------- */

const CARPETA_FOTOS_PEDIDO = 'pedidos';
const MAXIMO_FOTO_PEDIDO = 5000000; // 5 MB

const ETAPAS_FOTO_PEDIDO = [
    'boceto' => 'Boceto',
    'avance' => 'Avance',
    'final' => 'Pieza final',
];

// Lo que ve la administradora. Subir una foto de etapa "boceto" deja el pedido en "pendiente".
const ESTADOS_BOCETO = [
    'sin_boceto' => 'Sin boceto',
    'pendiente' => 'Esperando respuesta del cliente',
    'aprobado' => 'Aprobado por el cliente',
    'cambios' => 'El cliente pidió cambios',
];

/**
 * Fotos de varios pedidos en una sola consulta, de la más antigua a la más reciente.
 *
 * @param int[] $pedidoIds
 * @return array<int, array[]> indexado por id de pedido
 */
function obtenerFotosPedidos(mysqli $db, array $pedidoIds): array
{
    $pedidoIds = array_values(array_unique(array_map('intval', $pedidoIds)));
    if (!$pedidoIds) {
        return [];
    }

    $marcadores = implode(',', array_fill(0, count($pedidoIds), '?'));
    $stmt = $db->prepare("SELECT * FROM pedido_fotos WHERE pedido_id IN ($marcadores) ORDER BY fecha ASC, id ASC");
    $stmt->bind_param(str_repeat('i', count($pedidoIds)), ...$pedidoIds);
    $stmt->execute();

    $fotos = [];
    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $foto) {
        $fotos[(int) $foto['pedido_id']][] = $foto;
    }
    return $fotos;
}

/** El boceto más reciente de una lista de fotos de un pedido, o null si no hay ninguno. */
function ultimoBoceto(array $fotos): ?array
{
    $bocetos = array_filter($fotos, static fn(array $foto): bool => $foto['etapa'] === 'boceto');
    return $bocetos ? end($bocetos) : null;
}
