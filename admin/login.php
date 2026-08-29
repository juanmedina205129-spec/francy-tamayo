<?php
require_once __DIR__ . '/../include/funciones.php';
require __DIR__ . '/../include/config/database.php';
$db = conectarDB();

session_start();

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!$username || !$password) {
        $errores[] = "Todos los campos son obligatorios";
    }

    if (empty($errores)) {

        $stmt = $db->prepare("SELECT * FROM usuarioss WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();

        if ($usuario && password_verify($password, $usuario['password'])) {

            // 🔐 LOGIN OK
            $_SESSION['login'] = true;
            $_SESSION['usuario'] = $usuario['username'];

            header("Location: index.php");
            exit;

        } else {
            $errores[] = "Usuario o contraseña incorrectos";
        }
    }
}

incluirTemplates('header');
?>

<div class="menu-container login-container ">
    <div class="login-card">

        <h1> Administrador Login</h1>

        <?php foreach ($errores as $error): ?>
            <div class="login-error"><?= $error ?></div>
        <?php endforeach; ?>

        <form method="POST" class="login-form">
            <input type="text" name="username" placeholder="Usuario">
            <input type="password" name="password" placeholder="Contraseña">

            <button type="submit" class="login-btn">
                Ingresar
            </button>
        </form>

    </div>
</div>

<?php include '../include/templates/footer.php'; ?>
