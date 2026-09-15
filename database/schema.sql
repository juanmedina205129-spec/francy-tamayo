CREATE DATABASE IF NOT EXISTS restaurante_admin
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE restaurante_admin;

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
  rating DECIMAL(2,1) NOT NULL DEFAULT 4.8,
  destacado TINYINT(1) NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  orden INT NOT NULL DEFAULT 0,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_producto_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO usuarios (username, password)
VALUES ('admin', '$2y$10$a/HOaUrAhGP0zdUdmOuQWO2R9xRSCErXLMO2tpjNBN8DWzqJACDc6')
ON DUPLICATE KEY UPDATE password = VALUES(password);

INSERT INTO productos
  (nombre, categoria, tipo, descripcion, precio, imagen, estado, rating, destacado, activo, orden)
VALUES
  ('Retrato Golden Retriever', 'pinturas', 'Pintura al oleo', 'Obra original inspirada en mascota.', 280000, 'assets/imagenes/productos/cuadro-golondrina.png', 'disponible', 4.9, 1, 1, 1),
  ('Perro en acuarela', 'pinturas', 'Acuarela', 'Pieza artesanal en acuarela.', 195000, 'assets/imagenes/productos/cuadro-jilguero.png', 'disponible', 4.8, 0, 1, 2),
  ('Dos gatitos - oleo', 'pinturas', 'Pintura al oleo', 'Pintura al oleo de mascotas.', 340000, 'assets/imagenes/productos/cuadro-plumas.png', 'agotado', 4.9, 0, 1, 3),
  ('Ave colorida - acuarela', 'pinturas', 'Acuarela', 'Ilustracion de ave en acuarela.', 175000, 'assets/imagenes/productos/cuadro-buho.png', 'disponible', 4.8, 0, 1, 4),
  ('Martin pescador', 'pinturas', 'Acuarela', 'Ilustracion de ave martin pescador.', 160000, 'assets/imagenes/productos/estuche-golondrina.png', 'disponible', 4.7, 0, 1, 5),
  ('Perro blanco - oleo', 'pinturas', 'Pintura al oleo', 'Retrato de perro al oleo.', 260000, 'assets/imagenes/productos/estuche-plumas.png', 'disponible', 4.8, 0, 1, 6),
  ('Retrato mascota personalizado', 'retratos', 'Retrato por encargo', 'Retrato personalizado desde foto.', 220000, 'assets/imagenes/productos/cuadro-buho.png', 'encargo', 5.0, 1, 1, 1),
  ('Retrato canino clasico', 'retratos', 'Retrato por encargo', 'Retrato clasico de mascota.', 250000, 'assets/imagenes/productos/cuadro-jilguero.png', 'encargo', 4.9, 0, 1, 2),
  ('Retrato doble mascotas', 'retratos', 'Retrato por encargo', 'Retrato doble de mascotas.', 380000, 'assets/imagenes/productos/cojin-jilguero.png', 'encargo', 5.0, 0, 1, 3),
  ('Camiseta Perro Acuarela', 'camisetas', 'Camiseta', 'Camiseta con diseño animal.', 85000, 'assets/imagenes/productos/estuche-azulejo.png', 'disponible', 4.7, 1, 1, 1),
  ('Camiseta Gato Minimalista', 'camisetas', 'Camiseta', 'Camiseta con gato minimalista.', 75000, 'assets/imagenes/productos/estuche-plumas.png', 'disponible', 4.6, 0, 1, 2),
  ('Camiseta Tigre Estampado', 'camisetas', 'Camiseta', 'Camiseta con estampado de tigre.', 90000, 'assets/imagenes/productos/estuche-golondrina.png', 'disponible', 4.8, 0, 1, 3),
  ('Camiseta Mascota Personalizada', 'camisetas', 'Camiseta por encargo', 'Camiseta personalizada con mascota.', 110000, 'assets/imagenes/productos/estuche-buho.png', 'encargo', 4.9, 0, 1, 4)
ON DUPLICATE KEY UPDATE
  categoria = VALUES(categoria),
  tipo = VALUES(tipo),
  descripcion = VALUES(descripcion),
  precio = VALUES(precio),
  imagen = VALUES(imagen),
  estado = VALUES(estado),
  rating = VALUES(rating),
  destacado = VALUES(destacado),
  activo = VALUES(activo),
  orden = VALUES(orden);
