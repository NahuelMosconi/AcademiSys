<?php
// ============================================================
//  Gestión de Usuarios (solo Administrador).
//  Los docentes y los alumnos obtienen su cuenta de acceso AUTOMÁTICAMENTE
//  al darlos de alta (login y contraseña iniciales = DNI). Por eso esta
//  pantalla se usa solo para crear cuentas de ADMINISTRADOR y para dar de
//  baja cuentas. El listado muestra todas las cuentas del sistema.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);   // solo el admin gestiona usuarios
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/../config/Database.php';

$usuarioModel = new Usuario();
$db = Database::conectar();
$mensaje = ''; $tipo = '';

// Id del rol Administrador (resuelto por nombre para no depender de un id fijo).
$idRolAdmin = (int) $db->query("SELECT id_rol FROM Rol WHERE nombre = 'Administrador'")->fetchColumn();

// ----- Alta de un ADMINISTRADOR -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $dni    = trim($_POST['dni'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    if ($nombre === '' || $dni === '' || $email === '') {
        $mensaje = 'Completá todos los campos.'; $tipo = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = 'El email no es válido.'; $tipo = 'error';
    } else {
        // Contraseña inicial = DNI (lo arma la clase). Sin vínculo a docente/alumno.
        [$ok, $mensaje] = $usuarioModel->crear($nombre, $dni, $email, $idRolAdmin, null, null);
        $tipo = $ok ? 'exito' : 'error';
    }
}
if (isset($_GET['baja'])) {
    $usuarioModel->darDeBaja((int) $_GET['baja']);
    $mensaje = 'Usuario dado de baja.'; $tipo = 'exito';
}

$usuarios = $usuarioModel->listar();
?>
<div class="encabezado-pagina"><h1>Gestión de usuarios</h1>
<p>Los docentes y alumnos reciben su cuenta automáticamente al darlos de alta.
   Acá se crean cuentas de <strong>Administrador</strong> y se dan de baja cuentas.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<div class="tarjeta-form">
<h2>Crear administrador</h2>
<p class="ayuda">La contraseña inicial es el DNI; el usuario puede cambiarla desde "Mi perfil".</p>
<form method="POST" class="formulario">
    <label>Nombre completo</label><input type="text" name="nombre" required>
    <label>DNI (será su usuario y contraseña inicial)</label><input type="text" name="dni" required>
    <label>Email</label><input type="email" name="email" required>
    <button type="submit">Crear administrador</button>
</form>
</div>

<h2>Usuarios del sistema</h2>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Nombre</th><th>DNI</th><th>Email</th><th>Rol</th><th>Vínculo</th><th>Estado</th><th>Acción</th></tr></thead>
    <tbody>
        <?php foreach ($usuarios as $us): ?>
            <tr class="<?= $us['activo'] ? '' : 'fila-anulada' ?>">
                <td><?= htmlspecialchars($us['nombre']) ?></td>
                <td><?= htmlspecialchars($us['dni']) ?></td>
                <td><?= htmlspecialchars($us['email']) ?></td>
                <td><?= htmlspecialchars($us['rol']) ?></td>
                <td><?= htmlspecialchars($us['docente'] ?? $us['alumno'] ?? '—') ?></td>
                <td><?= $us['activo'] ? '<span class="badge-activa">Activo</span>' : '<span class="badge-anulada">Baja</span>' ?></td>
                <td><?php if ($us['activo']): ?><a href="usuarios.php?baja=<?= (int)$us['id_usuario'] ?>" onclick="return confirm('¿Dar de baja?')">Baja</a><?php else: ?>—<?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
