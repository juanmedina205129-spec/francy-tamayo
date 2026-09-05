<?php

define('BASE_URL', '/francytamayo/');

function incluirTemplates(string $nombre): void {
    include __DIR__ . "/templates/$nombre.php";
}

function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_httponly' => true,
            'cookie_samesite' => 'Lax',
        ]);
    }
}

function auth(): void {
    iniciarSesion();

    if (empty($_SESSION['login'])) {
        header('Location: ' . BASE_URL . 'admin/login.php');
        exit;
    }
}

function csrfToken(): string {
    iniciarSesion();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfValido(?string $token): bool {
    iniciarSesion();
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}



