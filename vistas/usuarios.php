<?php
// ============================================================
//  Pantalla de Gestión de Usuarios (solo Administrador). Crea cuentas de acceso
//  (login y contraseña inicial = DNI), asigna rol y vincula a un docente o alumno.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);   // solo el admin gestiona usuarios
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Usuario.php';
require_once __DIR__ . '/../clases/Docente.php';
require_once __DIR__ . '/../config/Database.php';

$usuarioModel = new Usuario();
$mensaje = ''; $tipo = '';

// ----- Alta de usuario (cuando se envía el formulario) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $dni    = trim($_POST['dni'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $idRol  = (int) ($_POST['id_rol'] ?? 0);
    // El vínculo depende del rol: Profesor -> docente; Alumno -> alumno. NULL si no aplica.
    $idDoc  = ($_POST['id_docente'] ?? '') !== '' ? (int)$_POST['id_docente'] : null;
    $idAlu  = ($_POST['id_alumno'] ?? '') !== '' ? (int)$_POST['id_alumno'] : null;
    if ($nombre===''||$dni===''||$email===''||$idRol<=0) {
        $mensaje = 'Completá todos los campos.'; $tipo = 'error';
    } elseif ($idRol === 2 && $idDoc === null) {        // rol 2 = Profesor
        $mensaje = 'Un profesor debe vincularse a un docente.'; $tipo = 'error';
    } elseif ($idRol === 3 && $idAlu === null) {        // rol 3 = Alumno
        $mensaje = 'Un alumno debe vincularse a un alumno cargado.'; $tipo = 'error';
    } else {
        // La clase crea el usuario con contraseña inicial = DNI y el vínculo correcto.
        [$ok, $mensaje] = $usuarioModel->crear($nombre, $dni, $email, $idRol, $idDoc, $idAlu);
        $tipo = $ok ? 'exito' : 'error';
    }
}
if (isset($_GET['baja'])) {
    $usuarioModel->darDeBaja((int)$_GET['baja']);
    $mensaje = 'Usuario dado de baja.'; $tipo = 'exito';
}

$usuarios = $usuarioModel->listar();
$docentes = (new Docente())->listar();
$db = Database::conectar();
$roles = $db->query("SELECT id_rol, nombre FROM Rol ORDER BY id_rol")->fetchAll();
?>
<div class="encabezado-pagina"><h1>Gestión de usuarios</h1>
<p>Crear cuentas de acceso. La contraseña inicial es el DNI; cada usuario puede cambiarla luego.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<div class="tarjeta-form">
<h2>Crear usuario</h2>
<form method="POST" class="formulario">
    <label>Nombre completo</label><input type="text" name="nombre" required>
    <label>DNI (será su usuario y contraseña inicial)</label><input type="text" name="dni" required>
    <label>Email</label><input type="email" name="email" required>
    <label>Rol</label>
    <select name="id_rol" id="selRol" required onchange="document.getElementById('boxDoc').style.display = this.value==='2' ? 'block':'none';">
        <option value="">-- elegir --</option>
        <?php foreach ($roles as $r): ?><option value="<?= (int)$r['id_rol'] ?>"><?= htmlspecialchars($r['nombre']) ?></option><?php endforeach; ?>
    </select>
    <div id="boxDoc" style="display:none;">
        <label>Docente vinculado (solo si es Profesor)</label>
        <select name="id_docente">
            <option value="">-- elegir docente --</option>
            <?php foreach ($docentes as $d): ?><option value="<?= (int)$d['id_docente'] ?>"><?= htmlspecialchars($d['nombre']) ?> (<?= htmlspecialchars($d['dni']) ?>)</option><?php endforeach; ?>
        </select>
    </div>
    <div id="boxAlu" style="display:none;">
        <label>Alumno vinculado (solo si es rol Alumno)</label>
        <select name="id_alumno">
            <option value="">-- elegir alumno --</option>
            <?php foreach ($alumnosSinUsuario as $a): ?><option value="<?= (int)$a['id_alumno'] ?>"><?= htmlspecialchars($a['nombre']) ?> (<?= htmlspecialchars($a['legajo']) ?>)</option><?php endforeach; ?>
        </select>
        <p class="ayuda">La carrera ya está asignada al alumno. Las materias y horarios salen de sus inscripciones (asignalas desde Inscripciones).</p>
    </div>
    <button type="submit">Crear usuario</button>
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
