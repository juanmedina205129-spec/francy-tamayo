-- Código público para que el cliente consulte el estado de su pedido (seguimiento.php).
-- Es segura de ejecutar más de una vez.
USE francytamayo;

ALTER TABLE pedidos
  ADD COLUMN IF NOT EXISTS codigo VARCHAR(12) NULL AFTER id,
  ADD COLUMN IF NOT EXISTS fecha_actualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER fecha_creacion,
  ADD UNIQUE INDEX IF NOT EXISTS uq_pedido_codigo (codigo);
