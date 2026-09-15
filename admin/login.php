<?php
require_once __DIR__ . '/../include/funciones.php';
require_once __DIR__ . '/../include/config/database.php';

iniciarSesion();

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

$errores = [];

function verificarUsuarioAdministrador(string $username, string $password): bool {
    $db = conectarDB();
    $stmt = $db->prepare("SELECT username, password FROM usuarios WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $usuario && password_verify($password, $usuario['password']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $errores[] = 'Completa usuario y contraseña.';
    }

    try {
        $credencialesValidas = empty($errores) && verificarUsuarioAdministrador($username, $password);
    } catch (Throwable $error) {
        $credencialesValidas = false;
        $errores[] = 'No se pudo conectar con la base de datos del administrador.';
    }

    if ($credencialesValidas) {
            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['usuario'] = $username;
            csrfToken();
            header('Location: index.php');
            exit;
    } elseif (empty($errores)) {
        $errores[] = 'Usuario o contraseña incorrectos.';
    }
}

incluirTemplates('header');
?>

<main class="admin-login-page">
    <section class="login-card" aria-labelledby="login-title">
        <div class="login-brand"><span>FT</span><p>Panel administrativo</p></div>
        <h1 id="login-title">Bienvenida de nuevo</h1>
        <p class="login-copy">Ingresa para organizar el catálogo y administrar tu espacio de trabajo.</p>

        <?php foreach ($errores as $error): ?>
            <div class="login-error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST" class="login-form">
            <label for="username">Usuario</label>
            <input id="username" type="text" name="username" placeholder="Tu usuario" autocomplete="username" required autofocus>
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>

            <button type="submit" class="login-btn">
                Entrar al panel
            </button>
        </form>
        <a class="login-back" href="<?= BASE_URL ?>index.php">← Volver a la tienda</a>
    </section>
</main>

<?php include '../include/templates/footer.php'; ?>
