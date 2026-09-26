<?php
require_once __DIR__ . '/../include/funciones.php';
require_once __DIR__ . '/../include/config/database.php';
auth();

$errores = [];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = entradaClave($_POST['actual'] ?? '');
    $nueva = entradaClave($_POST['nueva'] ?? '');
    $confirmacion = entradaClave($_POST['confirmacion'] ?? '');

    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        $errores[] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
    } elseif (!$actual || !$nueva || !$confirmacion) {
        $errores[] = 'Completa todos los campos.';
    } elseif (mb_strlen($nueva) < 10) {
        $errores[] = 'La nueva contraseña debe tener al menos 10 caracteres.';
    } elseif ($nueva !== $confirmacion) {
        $errores[] = 'La confirmación no coincide con la nueva contraseña.';
    } elseif ($nueva === $actual) {
        $errores[] = 'La nueva contraseña debe ser distinta de la actual.';
    }

    if (!$errores) {
        try {
            $db = conectarDB();
            $usuario = $_SESSION['usuario'] ?? '';
            $stmt = $db->prepare('SELECT password FROM usuarios WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $usuario);
            $stmt->execute();
            $fila = $stmt->get_result()->fetch_assoc();

            if (!$fila || !password_verify($actual, $fila['password'])) {
                $errores[] = 'La contraseña actual no es correcta.';
            } else {
                $hash = password_hash($nueva, PASSWORD_DEFAULT);
                $stmt = $db->prepare('UPDATE usuarios SET password = ? WHERE username = ?');
                $stmt->bind_param('ss', $hash, $usuario);
                $stmt->execute();
                session_regenerate_id(true);
                renovarCsrfToken();
                $mensaje = 'Contraseña actualizada correctamente.';
            }
        } catch (Throwable $error) {
            $errores[] = 'No se pudo actualizar la contraseña.';
        }
    }
}

incluirTemplates('header');
?>

<main class="admin-login-page admin-password-page">
    <section class="login-card" aria-labelledby="password-title">
        <div class="login-brand"><span>FT</span><p>Panel administrativo</p></div>
        <h1 id="password-title">Cambiar contraseña</h1>
        <p class="login-copy">Usa una contraseña larga y que no utilices en otros sitios.</p>

        <?php if ($mensaje): ?>
            <div class="login-success" role="status"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        <?php foreach ($errores as $error): ?>
            <div class="login-error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>

        <form method="POST" class="login-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
            <label for="actual">Contraseña actual</label>
            <input id="actual" type="password" name="actual" autocomplete="current-password" required>
            <label for="nueva">Nueva contraseña</label>
            <input id="nueva" type="password" name="nueva" autocomplete="new-password" minlength="10" required>
            <label for="confirmacion">Confirmar nueva contraseña</label>
            <input id="confirmacion" type="password" name="confirmacion" autocomplete="new-password" minlength="10" required>

            <button type="submit" class="login-btn">Guardar contraseña</button>
        </form>
        <a class="login-back" href="<?= BASE_URL ?>admin/index.php">← Volver al panel</a>
    </section>
</main>

<?php include __DIR__ . '/../include/templates/footer.php'; ?>
