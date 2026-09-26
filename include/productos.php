<?php
require_once __DIR__ . '/config/database.php';

function obtenerProductos(?string $categoria = null, bool $soloActivos = true, ?string $estado = null): array
{
    try {
        $db = conectarDB();
        $condiciones = [];
        $tipos = '';
        $valores = [];

        if ($soloActivos) {
            $condiciones[] = 'activo = 1';
        }

        if ($categoria !== null) {
            $condiciones[] = 'categoria = ?';
            $tipos .= 's';
            $valores[] = $categoria;
        }

        if ($estado !== null) {
            $condiciones[] = 'estado = ?';
            $tipos .= 's';
            $valores[] = $estado;
        }

        $sql = 'SELECT * FROM productos';
        if ($condiciones) {
            $sql .= ' WHERE ' . implode(' AND ', $condiciones);
        }
        $sql .= ' ORDER BY orden ASC, id ASC';

        $stmt = $db->prepare($sql);
        if ($valores) {
            $stmt->bind_param($tipos, ...$valores);
        }
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $error) {
        return productosBase($categoria, $soloActivos, $estado);
    }
}

function obtenerProductosDestacados(int $limite = 3): array
{
    try {
        $db = conectarDB();
        $stmt = $db->prepare('SELECT * FROM productos WHERE activo = 1 AND destacado = 1 ORDER BY orden ASC, id ASC LIMIT ?');
        $stmt->bind_param('i', $limite);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } catch (Throwable $error) {
        return array_slice(array_values(array_filter(productosBase(), static fn(array $producto): bool => (bool) $producto['destacado'])), 0, $limite);
    }
}

function contarProductosPorCategoria(): array
{
    $conteo = ['pinturas' => 0, 'retratos' => 0, 'camisetas' => 0];
    try {
        $db = conectarDB();
        $resultado = $db->query('SELECT categoria, COUNT(*) AS total FROM productos WHERE activo = 1 GROUP BY categoria');

        while ($fila = $resultado->fetch_assoc()) {
            $conteo[$fila['categoria']] = (int) $fila['total'];
        }
    } catch (Throwable $error) {
        foreach (productosBase() as $producto) {
            if ((int) $producto['activo'] === 1) {
                $conteo[$producto['categoria']]++;
            }
        }
    }

    return $conteo;
}

function productosBase(?string $categoria = null, bool $soloActivos = true, ?string $estado = null): array
{
    $productos = [
        ['id' => 1, 'nombre' => 'Retrato Golden Retriever', 'categoria' => 'pinturas', 'tipo' => 'Pintura al oleo', 'descripcion' => 'Obra original inspirada en mascota.', 'precio' => 280000, 'imagen' => 'assets/imagenes/productos/cuadro-golondrina.png', 'estado' => 'disponible', 'rating' => 4.9, 'destacado' => 1, 'activo' => 1, 'orden' => 1],
        ['id' => 2, 'nombre' => 'Perro en acuarela', 'categoria' => 'pinturas', 'tipo' => 'Acuarela', 'descripcion' => 'Pieza artesanal en acuarela.', 'precio' => 195000, 'imagen' => 'assets/imagenes/productos/cuadro-jilguero.png', 'estado' => 'disponible', 'rating' => 4.8, 'destacado' => 0, 'activo' => 1, 'orden' => 2],
        ['id' => 3, 'nombre' => 'Dos gatitos - oleo', 'categoria' => 'pinturas', 'tipo' => 'Pintura al oleo', 'descripcion' => 'Pintura al oleo de mascotas.', 'precio' => 340000, 'imagen' => 'assets/imagenes/productos/cuadro-plumas.png', 'estado' => 'agotado', 'rating' => 4.9, 'destacado' => 0, 'activo' => 1, 'orden' => 3],
        ['id' => 4, 'nombre' => 'Ave colorida - acuarela', 'categoria' => 'pinturas', 'tipo' => 'Acuarela', 'descripcion' => 'Ilustracion de ave en acuarela.', 'precio' => 175000, 'imagen' => 'assets/imagenes/productos/cuadro-buho.png', 'estado' => 'disponible', 'rating' => 4.8, 'destacado' => 0, 'activo' => 1, 'orden' => 4],
        ['id' => 5, 'nombre' => 'Martin pescador', 'categoria' => 'pinturas', 'tipo' => 'Acuarela', 'descripcion' => 'Ilustracion de ave martin pescador.', 'precio' => 160000, 'imagen' => 'assets/imagenes/productos/estuche-golondrina.png', 'estado' => 'disponible', 'rating' => 4.7, 'destacado' => 0, 'activo' => 1, 'orden' => 5],
        ['id' => 6, 'nombre' => 'Perro blanco - oleo', 'categoria' => 'pinturas', 'tipo' => 'Pintura al oleo', 'descripcion' => 'Retrato de perro al oleo.', 'precio' => 260000, 'imagen' => 'assets/imagenes/productos/estuche-plumas.png', 'estado' => 'disponible', 'rating' => 4.8, 'destacado' => 0, 'activo' => 1, 'orden' => 6],
        ['id' => 7, 'nombre' => 'Retrato mascota personalizado', 'categoria' => 'retratos', 'tipo' => 'Retrato por encargo', 'descripcion' => 'Retrato personalizado desde foto.', 'precio' => 220000, 'imagen' => 'assets/imagenes/productos/cuadro-buho.png', 'estado' => 'encargo', 'rating' => 5.0, 'destacado' => 1, 'activo' => 1, 'orden' => 1],
        ['id' => 8, 'nombre' => 'Retrato canino clasico', 'categoria' => 'retratos', 'tipo' => 'Retrato por encargo', 'descripcion' => 'Retrato clasico de mascota.', 'precio' => 250000, 'imagen' => 'assets/imagenes/productos/cuadro-jilguero.png', 'estado' => 'encargo', 'rating' => 4.9, 'destacado' => 0, 'activo' => 1, 'orden' => 2],
        ['id' => 9, 'nombre' => 'Retrato doble mascotas', 'categoria' => 'retratos', 'tipo' => 'Retrato por encargo', 'descripcion' => 'Retrato doble de mascotas.', 'precio' => 380000, 'imagen' => 'assets/imagenes/productos/cojin-jilguero.png', 'estado' => 'encargo', 'rating' => 5.0, 'destacado' => 0, 'activo' => 1, 'orden' => 3],
        ['id' => 10, 'nombre' => 'Camiseta Perro Acuarela', 'categoria' => 'camisetas', 'tipo' => 'Camiseta', 'descripcion' => 'Camiseta con diseño animal.', 'precio' => 85000, 'imagen' => 'assets/imagenes/productos/estuche-azulejo.png', 'estado' => 'disponible', 'rating' => 4.7, 'destacado' => 1, 'activo' => 1, 'orden' => 1],
        ['id' => 11, 'nombre' => 'Camiseta Gato Minimalista', 'categoria' => 'camisetas', 'tipo' => 'Camiseta', 'descripcion' => 'Camiseta con gato minimalista.', 'precio' => 75000, 'imagen' => 'assets/imagenes/productos/estuche-plumas.png', 'estado' => 'disponible', 'rating' => 4.6, 'destacado' => 0, 'activo' => 1, 'orden' => 2],
        ['id' => 12, 'nombre' => 'Camiseta Tigre Estampado', 'categoria' => 'camisetas', 'tipo' => 'Camiseta', 'descripcion' => 'Camiseta con estampado de tigre.', 'precio' => 90000, 'imagen' => 'assets/imagenes/productos/estuche-golondrina.png', 'estado' => 'disponible', 'rating' => 4.8, 'destacado' => 0, 'activo' => 1, 'orden' => 3],
        ['id' => 13, 'nombre' => 'Camiseta Mascota Personalizada', 'categoria' => 'camisetas', 'tipo' => 'Camiseta por encargo', 'descripcion' => 'Camiseta personalizada con mascota.', 'precio' => 110000, 'imagen' => 'assets/imagenes/productos/estuche-buho.png', 'estado' => 'encargo', 'rating' => 4.9, 'destacado' => 0, 'activo' => 1, 'orden' => 4],
    ];

    return array_values(array_filter($productos, static function (array $producto) use ($categoria, $soloActivos, $estado): bool {
        if ($soloActivos && (int) $producto['activo'] !== 1) {
            return false;
        }

        if ($categoria !== null && $producto['categoria'] !== $categoria) {
            return false;
        }

        return $estado === null || $producto['estado'] === $estado;
    }));
}

function formatearPrecioProducto(array $producto): string
{
    $precio = '$' . number_format((float) $producto['precio'], 0, ',', '.');
    return $producto['estado'] === 'encargo' ? 'Desde ' . $precio : $precio;
}

function etiquetaEstadoProducto(array $producto): string
{
    return match ($producto['estado']) {
        'encargo' => 'Por encargo',
        'agotado' => 'Agotado',
        default => 'Disponible',
    };
}

function claseEstadoProducto(array $producto): string
{
    return match ($producto['estado']) {
        'disponible' => 'available',
        default => 'custom',
    };
}

function productosParaJavascript(array $productos): array
{
    return array_map(static fn(array $producto): array => [
        'id' => (int) $producto['id'],
        'name' => $producto['nombre'],
        'category' => $producto['tipo'],
        'group' => $producto['categoria'],
        'price' => (float) $producto['precio'],
        'image' => $producto['imagen'],
        'status' => $producto['estado'],
    ], $productos);
}

function renderizarTarjetaProducto(array $producto, bool $enlaceCategoria = false): void
{
    $nombre = htmlspecialchars($producto['nombre']);
    $tipo = htmlspecialchars($producto['tipo']);
    $imagen = htmlspecialchars($producto['imagen']);
    $estado = htmlspecialchars(etiquetaEstadoProducto($producto));
    $claseEstado = htmlspecialchars(claseEstadoProducto($producto));
    $rating = number_format((float) $producto['rating'], 1);
    $estrellasLlenas = (int) round((float) $producto['rating']);
    $estrellas = str_repeat('★', $estrellasLlenas) . str_repeat('☆', 5 - $estrellasLlenas);
    $precio = formatearPrecioProducto($producto);
    $deshabilitado = $producto['estado'] === 'agotado' ? ' disabled' : '';
    $textoBoton = $producto['estado'] === 'agotado' ? 'Agotado' : ($producto['estado'] === 'encargo' ? 'Encargar' : 'Añadir');
    $urlCategoria = BASE_URL . $producto['categoria'] . '.php';
    ?>
    <article class="product-card">
        <div class="product-image">
            <span class="badge <?= $claseEstado ?>"><?= $estado ?></span>
            <img src="<?= BASE_URL . $imagen ?>" alt="<?= $nombre ?>">
        </div>
        <div class="product-body">
            <span><?= $tipo ?></span>
            <h3><?= $nombre ?></h3>
            <p class="rating" aria-label="Valoración <?= $rating ?> de 5"><span aria-hidden="true"><?= $estrellas ?></span> <?= $rating ?></p>
            <div class="product-footer">
                <strong><?= $precio ?></strong>
                <?php if ($enlaceCategoria): ?>
                    <a href="<?= $urlCategoria ?>">Ver mas</a>
                <?php else: ?>
                    <button class="add-to-cart" data-id="<?= (int) $producto['id'] ?>" data-product="<?= $nombre ?>" data-category="<?= $tipo ?>" data-price="<?= (float) $producto['precio'] ?>" data-image="<?= $imagen ?>"<?= $deshabilitado ?>><?= $textoBoton ?></button>
                <?php endif; ?>
            </div>
        </div>
    </article>
    <?php
}
