<?php

function conectarDB():mysqli{
    $db = new mysqli("localhost", "root", "", "restaurante_admin");

    if ($db -> connect_error){
        echo "error de conexion".$db -> connect_error;
        exit;
    }
    return $db;
}