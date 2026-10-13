<?php
require_once __DIR__ . '/config/database.php';

// Las reseñas las escribe el cliente en seguimiento.php, una por pedido. La administradora
// no puede crear ni editar valoraciones: todo lo que se muestra sale de la tabla `resenas`.

const RESENA_MAX_NOMBRE = 80;
const RESENA_MAX_COMENTARIO = 1000;

/**
 * Promedio y número de reseñas por producto, a partir de los productos guardados en el JSON
 * de cada pedido reseñado. La consulta se hace una sola vez por petición.
 *
 * @return array<int, array{promedio: float, total: int}> indexado por id de producto
 */
function obtenerValoracionesProductos(): array
{
    static $valoraciones = null;
    if ($valoraciones !== null) {
        return $valoraciones;
    }

    $valoraciones = [];
    try {
        // La conexión se guarda en una variable: si se encadena conectarDB()->prepare(),
        // PHP la cierra antes de llegar a execute().
        $db = conectarDB();
        $stmt = $db->prepare('SELECT r.estrellas, p.productos FROM resenas r INNER JOIN pedidos p ON p.id = r.pedido_id');
        $stmt->execute();
        $resultado = $stmt->get_result();

        $sumas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $productos = json_decode((string) $fila['productos'], true);
            if (!is_array($productos)) {
                continue;
            }
            // Un pedido cuenta una vez por producto, aunque lleve varias unidades.
            $ids = array_unique(array_map(static fn($producto): int => (int) ($producto['id'] ?? 0), array_filter($productos, 'is_array')));
            foreach ($ids as $id) {
                if ($id <= 0) {
                    continue;
                }
                $sumas[$id]['suma'] = ($sumas[$id]['suma'] ?? 0) + (int) $fila['estrellas'];
                $sumas[$id]['total'] = ($sumas[$id]['total'] ?? 0) + 1;
            }
        }

        foreach ($sumas as $id => $dato) {
            $valoraciones[$id] = ['promedio' => $dato['suma'] / $dato['total'], 'total' => $dato['total']];
        }
    } catch (Throwable $error) {
        // Sin base de datos (o sin la tabla) el catálogo se muestra "Aún sin reseñas".
    }

    return $valoraciones;
}

/**
 * @return array{promedio: ?float, total: int} promedio null cuando todavía no hay reseñas
 */
function obtenerResumenResenas(): array
{
    try {
        $db = conectarDB();
        $stmt = $db->prepare('SELECT AVG(estrellas) AS promedio, COUNT(*) AS total FROM resenas');
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $total = (int) ($fila['total'] ?? 0);

        return ['promedio' => $total ? (float) $fila['promedio'] : null, 'total' => $total];
    } catch (Throwable $error) {
        return ['promedio' => null, 'total' => 0];
    }
}

/** Reseñas con comentario, de la más reciente a la más antigua, con el tipo de encargo del pedido. */
function obtenerResenasRecientes(int $limite = 3): array
{
    try {
        $db = conectarDB();
        $stmt = $db->prepare("SELECT r.nombre, r.estrellas, r.comentario, r.fecha, p.tipo_encargo FROM resenas r INNER JOIN pedidos p ON p.id = r.pedido_id WHERE r.comentario IS NOT NULL AND r.comentario <> '' ORDER BY r.fecha DESC, r.id DESC LIMIT ?");
        $stmt->bind_param('i', $limite);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $error) {
        return [];
    }
}

function obtenerResenaPedido(mysqli $db, int $pedidoId): ?array
{
    $stmt = $db->prepare('SELECT * FROM resenas WHERE pedido_id = ? LIMIT 1');
    $stmt->bind_param('i', $pedidoId);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc() ?: null;
}

/** Devuelve false si el pedido ya tenía reseña (pedido_id es único). */
function guardarResena(mysqli $db, int $pedidoId, string $nombre, int $estrellas, ?string $comentario): bool
{
    $stmt = $db->prepare('INSERT INTO resenas (pedido_id, nombre, estrellas, comentario) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isis', $pedidoId, $nombre, $estrellas, $comentario);
    try {
        $stmt->execute();
        return true;
    } catch (mysqli_sql_exception $duplicada) {
        if ($duplicada->getCode() === 1062) {
            return false;
        }
        throw $duplicada;
    }
}

/** Convierte una valoración (0 a 5) en texto, por ejemplo 4.2 → "★★★★☆". */
function estrellasTexto(float|int|string|null $valor): string
{
    $llenas = min(5, max(0, (int) round((float) $valor)));
    return str_repeat('★', $llenas) . str_repeat('☆', 5 - $llenas);
}
