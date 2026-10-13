-- Nido de Arte: reseñas de clientes, cupo de encargos, fotos del avance, boceto y modo regalo.
-- Es segura de ejecutar más de una vez.
USE francytamayo;

-- La valoración ya no la escribe la administradora: se calcula con las reseñas de los clientes.
ALTER TABLE productos DROP COLUMN IF EXISTS rating;

ALTER TABLE pedidos
  ADD COLUMN IF NOT EXISTS foto_referencia VARCHAR(255) NULL AFTER notas,
  ADD COLUMN IF NOT EXISTS es_regalo TINYINT(1) NOT NULL DEFAULT 0 AFTER foto_referencia,
  ADD COLUMN IF NOT EXISTS regalo_para VARCHAR(120) NULL AFTER es_regalo,
  ADD COLUMN IF NOT EXISTS regalo_mensaje VARCHAR(500) NULL AFTER regalo_para,
  ADD COLUMN IF NOT EXISTS boceto_estado ENUM('sin_boceto','pendiente','aprobado','cambios') NOT NULL DEFAULT 'sin_boceto' AFTER estado,
  ADD COLUMN IF NOT EXISTS boceto_comentario TEXT NULL AFTER boceto_estado;

-- Fotos que sube la administradora para que el cliente siga el avance (seguimiento.php).
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

-- Reseñas escritas por el cliente desde seguimiento.php: una por pedido.
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

-- cupo_encargos: máximo de pedidos activos (nuevo o en_proceso) a la vez; 0 pausa los encargos.
CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(50) PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO configuracion (clave, valor) VALUES ('cupo_encargos', '6');
