<?php
// ============================================================
//  Pantalla de Comisiones (solo Administrador). Crea comisiones (un trigger en
//  la base valida que no se solapen aula ni docente) y las lista con buscador.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Comision.php';
require_once __DIR__ . '/../clases/Materia.php';
require_once __DIR__ . '/../clases/Docente.php';
require_once __DIR__ . '/../clases/Aula.php';
require_once __DIR__ . '/../clases/PeriodoLectivo.php';

$comisionModel = new Comision();
$mensaje = ''; $tipo = '';

// ----- Alta de comisión (el trigger de la base valida solapamientos) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idMateria = (int) ($_POST['id_materia'] ?? 0);
    $idDocente = (int) ($_POST['id_docente'] ?? 0);
    $idAula    = (int) ($_POST['id_aula'] ?? 0);
    $idPeriodo = (int) ($_POST['id_periodo'] ?? 0);
    $dia       = trim($_POST['dia'] ?? '');
    $hi        = $_POST['hora_inicio'] ?? '';
    $hf        = $_POST['hora_fin'] ?? '';
    if ($idMateria && $idDocente && $idAula && $idPeriodo && $dia && $hi && $hf) {
        [$ok, $mensaje] = $comisionModel->crear($idMateria, $idDocente, $idAula, $idPeriodo, $dia, $hi, $hf);
        $tipo = $ok ? 'exito' : 'error';
    } else {
        $mensaje = 'Completá todos los campos.'; $tipo = 'error';
    }
}
if (isset($_GET['baja'])) {
    [$ok, $mensaje] = $comisionModel->darDeBaja((int) $_GET['baja']);
    $tipo = $ok ? 'exito' : 'error';
}

$q = trim($_GET['q'] ?? '');
$comisiones = $comisionModel->listar($q);
$materias = (new Materia())->listarParaCombo();
$docentes = (new Docente())->listar();
$aulas    = (new Aula())->listar();
$periodos = (new PeriodoLectivo())->listar();
?>

<div class="encabezado-pagina"><h1>Comisiones</h1><p>Dictado de materias con docente, aula, período y horario.</p></div>

<?php if ($mensaje !== ''): ?>
    <p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<div class="tarjeta-form">
<h2>Crear comisión</h2>
<form method="POST" class="formulario form-grid">
    <div><label>Materia</label>
    <select name="id_materia" required><option value="">--</option>
        <?php foreach ($materias as $m): ?><option value="<?= (int)$m['id_materia'] ?>"><?= htmlspecialchars($m['nombre']) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Docente</label>
    <select name="id_docente" required><option value="">--</option>
        <?php foreach ($docentes as $d): ?><option value="<?= (int)$d['id_docente'] ?>"><?= htmlspecialchars($d['nombre']) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Aula</label>
    <select name="id_aula" required><option value="">--</option>
        <?php foreach ($aulas as $a): ?><option value="<?= (int)$a['id_aula'] ?>"><?= htmlspecialchars($a['nombre']) ?> (cupo <?= (int)$a['cupo_maximo'] ?>)</option><?php endforeach; ?>
    </select></div>
    <div><label>Período</label>
    <select name="id_periodo" required><option value="">--</option>
        <?php foreach ($periodos as $p): ?><option value="<?= (int)$p['id_periodo'] ?>"><?= htmlspecialchars($p['nombre']) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Día</label>
    <select name="dia" required>
        <?php foreach (['Lunes','Martes','Miércoles','Jueves','Viernes'] as $d): ?><option><?= $d ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Hora inicio</label><input type="time" name="hora_inicio" required></div>
    <div><label>Hora fin</label><input type="time" name="hora_fin" required></div>
    <div class="form-full"><button type="submit">Crear comisión</button></div>
</form>
</div>

<h2>Comisiones activas</h2>
<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="🔍 Buscar por materia, docente, aula o día...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="comisiones.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Materia</th><th>Docente</th><th>Aula</th>
        <th>Día / Horario</th><th>Vacantes</th><th>Acción</th></tr></thead>
    <tbody>
        <?php foreach ($comisiones as $c): ?>
            <tr>
                <td><?= (int) $c['id_comision'] ?></td>
                <td><?= htmlspecialchars($c['materia']) ?></td>
                <td><?= htmlspecialchars($c['docente']) ?></td>
                <td><?= htmlspecialchars($c['aula']) ?></td>
                <td><?= htmlspecialchars($c['dia']) ?>
                    <?= substr($c['hora_inicio'],0,5) ?>-<?= substr($c['hora_fin'],0,5) ?></td>
                <td><?= (int) $c['vacantes_disponibles'] ?>/<?= (int) $c['cupo_maximo'] ?></td>
                <td><a href="comisiones.php?baja=<?= (int)$c['id_comision'] ?>"
                       onclick="return confirm('¿Dar de baja esta comisión?')">Baja</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
