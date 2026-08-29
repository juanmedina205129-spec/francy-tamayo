<?php

//require 'app.php'
function incluirTemplates ($nombre): void {
    include __DIR__ . "/templates/$nombre.php";
}


function auth() {
    session_start();

    if (!isset($_SESSION['login'])) {
        header("Location: " . BASE_URL . "admin/login.php");
        exit;
    }
}


define('BASE_URL', '/francytamayo/');




