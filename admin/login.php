<?php
require_once __DIR__ . '/../include/funciones.php';
require_once __DIR__ . '/../include/config/database.php';

iniciarSesion();

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit;
}

$errores = [];

// Límite contra fuerza bruta: intentos fallidos permitidos por IP en la ventana indicada.
const LOGIN_MAX_INTENTOS = 5;
const LOGIN_VENTANA_MINUTOS = 15;

function verificarUsuarioAdministrador(mysqli $db, string $username, string $password): bool {
    $stmt = $db->prepare("SELECT username, password FROM usuarios WHERE username = ? LIMIT 1");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $usuario && password_verify($password, $usuario['password']);
}

function intentosFallidosRecientes(mysqli $db, string $ip): int {
    $stmt = $db->prepare('SELECT COUNT(*) AS total FROM intentos_login WHERE ip = ? AND fecha > NOW() - INTERVAL ? MINUTE');
    $ventana = LOGIN_VENTANA_MINUTOS;
    $stmt->bind_param('si', $ip, $ventana);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()['total'];
}

function registrarIntentoFallido(mysqli $db, string $ip, string $username): void {
    $username = mb_substr($username, 0, 50);
    $stmt = $db->prepare('INSERT INTO intentos_login (ip, username) VALUES (?, ?)');
    $stmt->bind_param('ss', $ip, $username);
    $stmt->execute();
    $db->query('DELETE FROM intentos_login WHERE fecha < NOW() - INTERVAL 1 DAY');
}

function limpiarIntentos(mysqli $db, string $ip): void {
    $stmt = $db->prepare('DELETE FROM intentos_login WHERE ip = ?');
    $stmt->bind_param('s', $ip);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = entradaTexto($_POST['username'] ?? '');
    $password = entradaClave($_POST['password'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $credencialesValidas = false;

    if (!$username || !$password) {
        $errores[] = 'Completa usuario y contraseña.';
    }

    try {
        $db = conectarDB();
        if (intentosFallidosRecientes($db, $ip) >= LOGIN_MAX_INTENTOS) {
            $errores[] = 'Demasiados intentos fallidos. Espera ' . LOGIN_VENTANA_MINUTOS . ' minutos antes de volver a intentarlo.';
        } elseif (empty($errores)) {
            $credencialesValidas = verificarUsuarioAdministrador($db, $username, $password);
            if ($credencialesValidas) {
                limpiarIntentos($db, $ip);
            } else {
                registrarIntentoFallido($db, $ip, $username);
            }
        }
    } catch (Throwable $error) {
        $credencialesValidas = false;
        $errores[] = 'No se pudo conectar con la base de datos del administrador.';
    }

    if ($credencialesValidas) {
            session_regenerate_id(true);
            $_SESSION['login'] = true;
            $_SESSION['usuario'] = $username;
            renovarCsrfToken();
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
