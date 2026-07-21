<?php
// ============================================================
//  inscripciones.php — Motor de inscripciones (Tarea 9)
//  Admin: inscribe a cualquiera. Profesor: solo ve sus comisiones.
//  El combo de comisión muestra solo materias de la carrera del alumno.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador', 'Profesor']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Inscripcion.php';
require_once __DIR__ . '/../clases/Alumno.php';

$inscModel = new Inscripcion();
$u = $_SESSION['usuario'];
$esProfesor = ($u['rol'] === 'Profesor');
$idDocente  = $u['id_docente'] ?? null;
$mensaje = ''; $tipo = '';

// Inscribir (el motor valida cupo, correlativas y horario)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['inscribir'])) {
    $idAlumno   = (int) ($_POST['id_alumno'] ?? 0);
    $idComision = (int) ($_POST['id_comision'] ?? 0);
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if ($idAlumno <= 0 || $idComision <= 0) {
        $mensaje = 'Elegí alumno y comisión.'; $tipo = 'error';
    } else {
        [$ok, $mensaje] = $inscModel->inscribir($idAlumno, $idComision, $ip);
        $tipo = $ok ? 'exito' : 'error';
    }
}
// Anular (baja lógica)
if (isset($_GET['anular'])) {
    [$ok, $mensaje] = $inscModel->anular((int) $_GET['anular']);
    $tipo = $ok ? 'exito' : 'error';
}

$q = trim($_GET['q'] ?? '');
$inscripciones = $esProfesor
    ? $inscModel->listarPorDocente($idDocente, $q)
    : $inscModel->listar($q);

// Para el combo de comisión filtrado por carrera, primero se elige el alumno
$alSel = (int) ($_GET['id_alumno'] ?? 0);
$alumnos    = (new Alumno())->listarParaCombo();
$comisiones = $alSel > 0 ? $inscModel->comisionesParaAlumno($alSel) : [];
?>
<div class="encabezado-pagina"><h1>Inscripciones</h1>
<p><?= $esProfesor ? 'Inscripciones de tus comisiones.' : 'Valida cupo, correlativas y horario antes de confirmar.' ?></p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<?php if (!$esProfesor): ?>
<div class="tarjeta-form">
<h2>Inscribir alumno a una comisión</h2>
<p class="ayuda">Elegí primero el alumno; solo verás comisiones de su carrera.</p>
<!-- Paso 1: elegir alumno (formulario GET que recarga para traer sus comisiones) -->
<form method="GET" id="formSelAlumno" class="formulario">
    <label>Alumno</label>
    <select name="id_alumno" required onchange="document.getElementById('formSelAlumno').submit();">
        <option value="">-- elegir alumno --</option>
        <?php foreach ($alumnos as $a): ?>
            <option value="<?= (int)$a['id_alumno'] ?>" <?= $alSel==$a['id_alumno']?'selected':'' ?>>
                <?= htmlspecialchars($a['nombre']) ?> (<?= htmlspecialchars($a['legajo']) ?>)</option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($alSel > 0): ?>
    <!-- Paso 2: elegir comisión y confirmar (formulario POST) -->
    <form method="POST" class="formulario">
        <input type="hidden" name="inscribir" value="1">
        <input type="hidden" name="id_alumno" value="<?= $alSel ?>">
        <label>Comisión (solo de su carrera)</label>
        <select name="id_comision" required>
            <option value="">-- elegir comisión --</option>
            <?php foreach ($comisiones as $c): ?>
                <option value="<?= (int)$c['id_comision'] ?>">
                    <?= htmlspecialchars($c['materia']) ?> — <?= htmlspecialchars($c['dia']) ?>
                    <?= substr($c['hora_inicio'],0,5) ?> (<?= (int)$c['vacantes_disponibles'] ?> vac.)</option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($comisiones)): ?><p class="ayuda">No hay comisiones disponibles para su carrera.</p><?php endif; ?>
        <button type="submit">Confirmar inscripción</button>
    </form>
<?php else: ?>
    <p class="ayuda">Elegí un alumno para continuar.</p>
<?php endif; ?>
</div>
<?php endif; ?>

<h2>Inscripciones</h2>
<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por alumno, legajo o materia...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="inscripciones.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Alumno</th><th>Legajo</th><th>Materia</th><th>Día / Horario</th>
        <th>Estado</th><th>Acción</th></tr></thead>
    <tbody>
        <?php if (empty($inscripciones)): ?><tr><td colspan="7" style="text-align:center;color:#9ca3af;">Sin resultados.</td></tr><?php endif; ?>
        <?php foreach ($inscripciones as $i): ?>
            <tr class="<?= $i['estado'] === 'ANULADA' ? 'fila-anulada' : '' ?>">
                <td><?= (int) $i['id_inscripcion'] ?></td>
                <td><?= htmlspecialchars($i['alumno']) ?></td>
                <td><?= htmlspecialchars($i['legajo']) ?></td>
                <td><?= htmlspecialchars($i['materia']) ?></td>
                <td><?= htmlspecialchars($i['dia']) ?> <?= substr($i['hora_inicio'],0,5) ?>-<?= substr($i['hora_fin'],0,5) ?></td>
                <td><span class="badge-<?= strtolower($i['estado']) ?>"><?= htmlspecialchars($i['estado']) ?></span></td>
                <td><?php if ($i['estado'] === 'ACTIVA'): ?>
                    <a href="inscripciones.php?anular=<?= (int)$i['id_inscripcion'] ?>" onclick="return confirm('¿Anular?')">Anular</a>
                    <?php else: ?>—<?php endif; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
