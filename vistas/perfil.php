<?php
// ============================================================
//  Mi Perfil (cualquier usuario logueado). Muestra los datos de la cuenta y
//  permite cambiar la contraseña. Si es profesor, lista sus materias.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirLogin();   // cualquier usuario logueado
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/../clases/Comision.php';

$u = $_SESSION['usuario'];
$esProfesor = ($u['rol'] === 'Profesor');
$mensaje = ''; $tipo = '';

// Cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual = $_POST['actual'] ?? '';
    $nueva  = $_POST['nueva'] ?? '';
    $repetir = $_POST['repetir'] ?? '';
    if ($nueva !== $repetir) {
        $mensaje = 'La nueva contraseña y su repetición no coinciden.'; $tipo = 'error';
    } else {
        [$ok, $mensaje] = (new Usuario())->cambiarPassword($u['id_usuario'], $actual, $nueva);
        $tipo = $ok ? 'exito' : 'error';
    }
}

// Si es profesor, traemos sus comisiones/materias
$misComisiones = [];
if ($esProfesor && $u['id_docente']) {
    $misComisiones = (new Comision())->listarPorDocente((int)$u['id_docente']);
}
?>
<div class="encabezado-pagina"><h1>Mi perfil</h1><p>Tus datos y configuración de cuenta.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<div class="tarjeta-form">
<h2>Mis datos</h2>
<table class="tabla" style="box-shadow:none;border:none;">
    <tr><td><strong>Nombre</strong></td><td><?= htmlspecialchars($u['nombre']) ?></td></tr>
    <tr><td><strong>DNI</strong></td><td><?= htmlspecialchars($u['dni']) ?></td></tr>
    <tr><td><strong>Email</strong></td><td><?= htmlspecialchars($u['email']) ?></td></tr>
    <tr><td><strong>Rol</strong></td><td><?= htmlspecialchars($u['rol']) ?></td></tr>
</table>
</div>

<?php if ($esProfesor): ?>
<div class="tarjeta-form">
<h2>Mis materias y comisiones</h2>
<?php if (empty($misComisiones)): ?>
    <p class="ayuda">No tenés comisiones asignadas todavía.</p>
<?php else: ?>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Materia</th><th>Aula</th><th>Día / Horario</th><th>Vacantes</th></tr></thead>
    <tbody>
        <?php foreach ($misComisiones as $c): ?>
            <tr><td><?= htmlspecialchars($c['materia']) ?></td><td><?= htmlspecialchars($c['aula']) ?></td>
            <td><?= htmlspecialchars($c['dia']) ?> <?= substr($c['hora_inicio'],0,5) ?>-<?= substr($c['hora_fin'],0,5) ?></td>
            <td><?= (int)$c['vacantes_disponibles'] ?>/<?= (int)$c['cupo_maximo'] ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
</div>
<?php endif; ?>

<div class="tarjeta-form">
<h2>Cambiar contraseña</h2>
<form method="POST" class="formulario">
    <label>Contraseña actual</label><input type="password" name="actual" required>
    <label>Nueva contraseña</label><input type="password" name="nueva" required>
    <label>Repetir nueva contraseña</label><input type="password" name="repetir" required>
    <button type="submit">Cambiar contraseña</button>
</form>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
