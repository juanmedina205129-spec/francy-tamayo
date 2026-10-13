CREATE DATABASE IF NOT EXISTS francytamayo
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE francytamayo;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS productos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  categoria ENUM('pinturas','retratos','camisetas') NOT NULL,
  tipo VARCHAR(80) NOT NULL,
  descripcion TEXT NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0,
  imagen VARCHAR(255) NOT NULL,
  estado ENUM('disponible','encargo','agotado') NOT NULL DEFAULT 'disponible',
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_producto_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Los productos se guardan como copia (id, nombre, cantidad, precio) para conservar
-- el historial aunque el producto cambie o se elimine del catálogo.
CREATE TABLE IF NOT EXISTS pedidos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(12) NULL,
  origen ENUM('carrito','contacto') NOT NULL DEFAULT 'carrito',
  nombre VARCHAR(120) NOT NULL,
  telefono VARCHAR(50) NOT NULL,
  email VARCHAR(150) NULL,
  ciudad VARCHAR(120) NULL,
  tipo_encargo VARCHAR(100) NOT NULL,
  fecha_entrega DATE NULL,
  tamano VARCHAR(80) NULL,
  presupuesto VARCHAR(80) NULL,
  detalles TEXT NOT NULL,
  entrega VARCHAR(80) NULL,
  direccion VARCHAR(255) NULL,
  notas TEXT NULL,
  foto_referencia VARCHAR(255) NULL,
  es_regalo TINYINT(1) NOT NULL DEFAULT 0,
  regalo_para VARCHAR(120) NULL,
  regalo_mensaje VARCHAR(500) NULL,
  productos JSON NOT NULL,
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  estado ENUM('nuevo', 'en_proceso', 'cerrado') NOT NULL DEFAULT 'nuevo',
  boceto_estado ENUM('sin_boceto','pendiente','aprobado','cambios') NOT NULL DEFAULT 'sin_boceto',
  boceto_comentario TEXT NULL,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_pedido_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fotos que sube la administradora para que el cliente siga el avance (seguimiento.php).
-- Una foto de etapa "boceto" deja el pedido esperando la aprobación del cliente.
CREATE TABLE IF NOT EXISTS pedido_fotos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  etapa ENUM('boceto','avance','final') NOT NULL DEFAULT 'avance',
  ruta VARCHAR(255) NOT NULL,
  nota VARCHAR(255) NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_pedido_fotos_pedido (pedido_id),
  CONSTRAINT fk_pedido_fotos_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reseñas escritas por el cliente desde seguimiento.php: una por pedido. La valoración de
-- cada producto se calcula a partir de ellas; no existe una calificación editable a mano.
CREATE TABLE IF NOT EXISTS resenas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  nombre VARCHAR(80) NOT NULL,
  estrellas TINYINT NOT NULL,
  comentario TEXT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_resena_pedido (pedido_id),
  CONSTRAINT fk_resenas_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE,
  CONSTRAINT chk_resenas_estrellas CHECK (estrellas BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajustes editables desde el panel. cupo_encargos: máximo de pedidos activos (nuevo o
-- en_proceso) que se aceptan a la vez; 0 pausa los encargos.
CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(50) PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracion (clave, valor) VALUES ('cupo_encargos', '6');

CREATE TABLE IF NOT EXISTS intentos_login (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  username VARCHAR(50) NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_intentos_ip_fecha (ip, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario inicial. Cambia la contraseña desde admin/cambiar-clave.php después de instalar.
-- No se sobrescribe la contraseña si el usuario ya existe.
INSERT IGNORE INTO usuarios (username, password)
VALUES ('admin', '$2y$10$a/HOaUrAhGP0zdUdmOuQWO2R9xRSCErXLMO2tpjNBN8DWzqJACDc6');

-- INSERT IGNORE: volver a ejecutar el script no pisa los cambios hechos desde el panel.
INSERT IGNORE INTO productos
  (nombre, categoria, tipo, descripcion, precio, imagen, estado, destacado, activo, orden)
VALUES
  ('Retrato Golden Retriever', 'pinturas', 'Pintura al óleo', 'Obra original inspirada en mascota.', 280000, 'assets/imagenes/productos/cuadro-golondrina.png', 'disponible', 1, 1, 1),
  ('Perro en acuarela', 'pinturas', 'Acuarela', 'Pieza artesanal en acuarela.', 195000, 'assets/imagenes/productos/cuadro-jilguero.png', 'disponible', 0, 1, 2),
  ('Dos gatitos - óleo', 'pinturas', 'Pintura al óleo', 'Pintura al óleo de mascotas.', 340000, 'assets/imagenes/productos/cuadro-plumas.png', 'agotado', 0, 1, 3),
  ('Ave colorida - acuarela', 'pinturas', 'Acuarela', 'Ilustración de ave en acuarela.', 175000, 'assets/imagenes/productos/cuadro-buho.png', 'disponible', 0, 1, 4),
  ('Martín pescador', 'pinturas', 'Acuarela', 'Ilustración de ave martín pescador.', 160000, 'assets/imagenes/productos/estuche-golondrina.png', 'disponible', 0, 1, 5),
  ('Perro blanco - óleo', 'pinturas', 'Pintura al óleo', 'Retrato de perro al óleo.', 260000, 'assets/imagenes/productos/estuche-plumas.png', 'disponible', 0, 1, 6),
  ('Retrato mascota personalizado', 'retratos', 'Retrato por encargo', 'Retrato personalizado desde foto.', 220000, 'assets/imagenes/productos/cuadro-buho.png', 'encargo', 1, 1, 1),
  ('Retrato canino clásico', 'retratos', 'Retrato por encargo', 'Retrato clásico de mascota.', 250000, 'assets/imagenes/productos/cuadro-jilguero.png', 'encargo', 0, 1, 2),
  ('Retrato doble mascotas', 'retratos', 'Retrato por encargo', 'Retrato doble de mascotas.', 380000, 'assets/imagenes/productos/cojin-jilguero.png', 'encargo', 0, 1, 3),
  ('Camiseta Perro Acuarela', 'camisetas', 'Camiseta', 'Camiseta con diseño animal.', 85000, 'assets/imagenes/productos/estuche-azulejo.png', 'disponible', 1, 1, 1),
  ('Camiseta Gato Minimalista', 'camisetas', 'Camiseta', 'Camiseta con gato minimalista.', 75000, 'assets/imagenes/productos/estuche-plumas.png', 'disponible', 0, 1, 2),
  ('Camiseta Tigre Estampado', 'camisetas', 'Camiseta', 'Camiseta con estampado de tigre.', 90000, 'assets/imagenes/productos/estuche-golondrina.png', 'disponible', 0, 1, 3),
  ('Camiseta Mascota Personalizada', 'camisetas', 'Camiseta por encargo', 'Camiseta personalizada con mascota.', 110000, 'assets/imagenes/productos/estuche-buho.png', 'encargo', 0, 1, 4);
