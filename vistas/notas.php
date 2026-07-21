<?php
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador', 'Profesor']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Acta.php';
require_once __DIR__ . '/../clases/Alumno.php';
require_once __DIR__ . '/../clases/Materia.php';

$actaModel = new Acta();
$u = $_SESSION['usuario'];
$esProfesor = ($u['rol'] === 'Profesor');
$idDocente = $u['id_docente'] ?? null;
$mensaje = ''; $tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_nota'])) {
    $idAlumno  = (int) ($_POST['id_alumno'] ?? 0);
    $idMateria = (int) ($_POST['id_materia'] ?? 0);
    $tipoNota  = trim($_POST['tipo'] ?? '');
    $nota      = (float) ($_POST['nota'] ?? -1);
    // Seguridad: si es profesor, solo puede cargar en SUS materias
    if ($esProfesor && !$actaModel->docenteDictaMateria($idDocente, $idMateria)) {
        $mensaje = 'No podés cargar notas de una materia que no dictás.'; $tipo = 'error';
    } elseif ($idAlumno<=0 || $idMateria<=0 || $tipoNota==='') {
        $mensaje = 'Completá todos los campos.'; $tipo = 'error';
    } else {
        [$ok, $mensaje] = $actaModel->registrar($idAlumno, $idMateria, $tipoNota, $nota);
        $tipo = $ok ? 'exito' : 'error';
    }
}

$q = trim($_GET['q'] ?? '');
// Materia seleccionada (para cargar sus alumnos) — se elige primero la materia
$matSel = (int) ($_GET['id_materia'] ?? 0);

if ($esProfesor) {
    $actas    = $actaModel->listarPorDocente($idDocente, $q);
    $materias = $actaModel->materiasDelDocente($idDocente);
    // Solo alumnos de la materia elegida (de las comisiones del profe)
    $alumnos  = $matSel > 0 ? $actaModel->alumnosDeMateriaDelDocente($idDocente, $matSel) : [];
} else {
    $actas    = $actaModel->listar($q);
    $materias = (new Materia())->listarParaCombo();
    $alumnos  = (new Alumno())->listarParaCombo();
}
?>
<div class="encabezado-pagina"><h1>Notas finales</h1>
<p><?= $esProfesor ? 'Cargás notas solo de tus materias y alumnos.' : 'Carga de calificaciones por tipo.' ?></p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<div class="tarjeta-form">
<h2>Cargar nota</h2>
<?php if ($esProfesor && empty($materias)): ?>
    <p class="ayuda">Todavía no tenés materias/comisiones asignadas.</p>
<?php else: ?>
<!-- Paso 1: elegir materia (form GET que recarga para traer los alumnos) -->
<form method="GET" id="formSelMateria" class="formulario">
    <label>Materia</label>
    <select name="id_materia" required onchange="document.getElementById('formSelMateria').submit();">
        <option value="">-- elegir materia --</option>
        <?php foreach ($materias as $m): ?>
            <option value="<?= (int)$m['id_materia'] ?>" <?= $matSel==$m['id_materia']?'selected':'' ?>>
                <?= htmlspecialchars($m['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($matSel > 0): ?>
    <!-- Paso 2: elegir alumno, tipo y nota (form POST) -->
    <form method="POST" class="formulario">
        <input type="hidden" name="guardar_nota" value="1">
        <input type="hidden" name="id_materia" value="<?= $matSel ?>">
        <label>Alumno</label>
        <select name="id_alumno" required>
            <option value="">-- elegir alumno --</option>
            <?php foreach ($alumnos as $a): ?>
                <option value="<?= (int)$a['id_alumno'] ?>"><?= htmlspecialchars($a['nombre']) ?> (<?= htmlspecialchars($a['legajo']) ?>)</option>
            <?php endforeach; ?>
        </select>
        <?php if (empty($alumnos)): ?><p class="ayuda">No hay alumnos inscriptos en esa materia.</p><?php endif; ?>

        <label>Tipo de nota</label>
        <select name="tipo" required>
            <?php foreach (Acta::TIPOS as $t): ?><option value="<?= $t ?>"><?= $t ?></option><?php endforeach; ?>
        </select>
        <label>Nota (0 a 10)</label>
        <input type="number" name="nota" min="0" max="10" step="0.01" required>
        <button type="submit">Registrar nota</button>
    </form>
<?php else: ?>
    <p class="ayuda">Elegí una materia para ver sus alumnos.</p>
<?php endif; ?>
<?php endif; ?>
</div>

<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por alumno, legajo, materia o tipo...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="notas.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>

<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Alumno</th><th>Legajo</th><th>Materia</th><th>Tipo</th><th>Nota</th><th>Fecha</th></tr></thead>
    <tbody>
        <?php if (empty($actas)): ?><tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin resultados.</td></tr><?php endif; ?>
        <?php foreach ($actas as $a): ?>
            <tr><td><?= htmlspecialchars($a['alumno']) ?></td><td><?= htmlspecialchars($a['legajo']) ?></td>
            <td><?= htmlspecialchars($a['materia']) ?></td><td><?= htmlspecialchars($a['tipo']) ?></td>
            <td><?= htmlspecialchars($a['nota_final']) ?></td><td><?= htmlspecialchars($a['fecha']) ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
