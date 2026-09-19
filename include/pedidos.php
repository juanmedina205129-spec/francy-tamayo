<?php
require_once __DIR__ . '/config/database.php';

function prepararTablaPedidos(mysqli $db): void
{
    $db->query("CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(120) NOT NULL,
        telefono VARCHAR(50) NOT NULL,
        email VARCHAR(150) NULL,
        ciudad VARCHAR(120) NOT NULL,
        tipo_encargo VARCHAR(100) NOT NULL,
        fecha_entrega DATE NULL,
        tamano VARCHAR(80) NULL,
        presupuesto VARCHAR(80) NULL,
        detalles TEXT NOT NULL,
        entrega VARCHAR(80) NOT NULL,
        direccion VARCHAR(255) NOT NULL,
        notas TEXT NULL,
        productos JSON NOT NULL,
        subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
        estado ENUM('nuevo', 'en_proceso', 'cerrado') NOT NULL DEFAULT 'nuevo',
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}
