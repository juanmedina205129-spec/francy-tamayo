-- Migración para bases de datos creadas con la versión anterior de schema.sql.
-- Es segura de ejecutar más de una vez.
USE francytamayo;

-- Los encargos del formulario de contacto no tienen entrega, dirección ni ciudad obligatorias.
ALTER TABLE pedidos
  MODIFY ciudad VARCHAR(120) NULL,
  MODIFY entrega VARCHAR(80) NULL,
  MODIFY direccion VARCHAR(255) NULL,
  ADD COLUMN IF NOT EXISTS origen ENUM('carrito','contacto') NOT NULL DEFAULT 'carrito' AFTER id;

-- Registro de intentos fallidos para limitar ataques de fuerza bruta en el login.
CREATE TABLE IF NOT EXISTS intentos_login (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip VARCHAR(45) NOT NULL,
  username VARCHAR(50) NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_intentos_ip_fecha (ip, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
