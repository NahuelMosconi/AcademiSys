<?php
require_once __DIR__ . '/config/sesion.php';
require_once __DIR__ . '/clases/Usuario.php';

// Si ya está logueado, va directo al panel
if (estaLogueado()) {
    header('Location: vistas/panel.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = trim($_POST['dni'] ?? '');
    $clave = $_POST['clave'] ?? '';
    if ($dni === '' || $clave === '') {
        $error = 'Completá DNI y contraseña.';
    } else {
        $usuario = (new Usuario())->login($dni, $clave);
        if ($usuario === null) {
            $error = 'DNI o contraseña incorrectos.';
        } else {
            // Regeneramos el ID ANTES de guardar los datos (previene Session Fixation).
            session_regenerate_id(true);
            $_SESSION['usuario'] = $usuario;
            $_SESSION['ultimo_acceso'] = time();
            header('Location: vistas/panel.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademiSys — Ingreso</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="fondo-login">
    <div class="caja-login">
        <img class="logo-login" src="assets/logo-mark.svg" alt="AcademiSys">
        <h1>Academi<span class="marca-azul">Sys</span></h1>
        <p class="subtitulo">Sistema de Gestión Académica</p>

        <?php if (isset($_GET['expirado'])): ?>
            <p class="aviso error">Tu sesión se cerró por inactividad.</p>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <p class="aviso error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>DNI</label>
            <input type="text" name="dni" placeholder="Tu DNI" required>
            <label>Contraseña</label>
            <input type="password" name="clave" placeholder="••••••••" required>
            <button type="submit" style="width:100%;">Ingresar</button>
        </form>
    </div>
</body>
</html>
