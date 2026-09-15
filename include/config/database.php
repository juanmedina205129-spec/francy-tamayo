<?php

function conectarDB():mysqli{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $db = new mysqli(
        getenv('DB_HOST') ?: '127.0.0.1',
        getenv('DB_USER') ?: 'root',
        getenv('DB_PASSWORD') ?: '',
        getenv('DB_NAME') ?: 'restaurante_admin'
    );

    $db->set_charset('utf8mb4');
    return $db;
}
