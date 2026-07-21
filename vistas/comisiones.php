<?php
// ============================================================
//  Pantalla de Comisiones (solo Administrador). Alta, edición y baja de
//  comisiones. Al crear, un trigger valida horario y solapamiento de aula/docente;
//  al editar, la validación equivalente se hace en la clase Comision.
//  La baja NO se permite si la comisión tiene alumnos inscriptos activos.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Comision.php';
require_once __DIR__ . '/../clases/Materia.php';
require_once __DIR__ . '/../clases/Docente.php';
require_once __DIR__ . '/../clases/Aula.php';
require_once __DIR__ . '/../clases/PeriodoLectivo.php';
require_once __DIR__ . '/../clases/CicloLectivo.php';

$comisionModel = new Comision();
$mensaje = ''; $tipo = '';

// ¿Editando? (link "Editar" -> ?editar=id). Prefill del formulario.
$editId = (int)($_GET['editar'] ?? 0);
$datos = ['id_comision'=>0,'id_materia'=>'','id_docente'=>'','id_aula'=>'','id_periodo'=>'','dia'=>'','hora_inicio'=>'','hora_fin'=>''];
if ($editId > 0) { $e = $comisionModel->buscar($editId); if ($e) $datos = $e; }

// ----- Alta o edición (el mismo formulario; si trae id oculto, es edición) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = (int) ($_POST['id_comision'] ?? 0);
    $idMateria = (int) ($_POST['id_materia'] ?? 0);
    $idDocente = (int) ($_POST['id_docente'] ?? 0);
    $idAula    = (int) ($_POST['id_aula'] ?? 0);
    $idPeriodo = (int) ($_POST['id_periodo'] ?? 0);
    $dia       = trim($_POST['dia'] ?? '');
    $hi        = $_POST['hora_inicio'] ?? '';
    $hf        = $_POST['hora_fin'] ?? '';
    if ($idMateria && $idDocente && $idAula && $idPeriodo && $dia && $hi && $hf) {
        if ($id > 0) [$ok, $mensaje] = $comisionModel->actualizar($id, $idMateria, $idDocente, $idAula, $idPeriodo, $dia, $hi, $hf);
        else         [$ok, $mensaje] = $comisionModel->crear($idMateria, $idDocente, $idAula, $idPeriodo, $dia, $hi, $hf);
        $tipo = $ok ? 'exito' : 'error';
        if ($ok) { $datos = ['id_comision'=>0,'id_materia'=>'','id_docente'=>'','id_aula'=>'','id_periodo'=>'','dia'=>'','hora_inicio'=>'','hora_fin'=>'']; }
        else     { $datos = ['id_comision'=>$id,'id_materia'=>$idMateria,'id_docente'=>$idDocente,'id_aula'=>$idAula,'id_periodo'=>$idPeriodo,'dia'=>$dia,'hora_inicio'=>$hi,'hora_fin'=>$hf]; }
    } else {
        $mensaje = 'Completá todos los campos.'; $tipo = 'error';
    }
}
// ----- Eliminar (baja lógica con control de integridad) -----
if (isset($_GET['eliminar'])) {
    [$ok, $mensaje] = $comisionModel->darDeBaja((int) $_GET['eliminar']);
    $tipo = $ok ? 'exito' : 'error';
}

$q = trim($_GET['q'] ?? '');
$comisiones = $comisionModel->listar($q);
$materias = (new Materia())->listarParaCombo();
$docentes = (new Docente())->listar();
$aulas    = (new Aula())->listar();
$periodos = (new PeriodoLectivo())->listar();
$cicloActual = (new CicloLectivo())->actual();
$editando = ((int)($datos['id_comision'] ?? 0)) > 0;
$hIni = $datos['hora_inicio'] ? substr($datos['hora_inicio'],0,5) : '';
$hFin = $datos['hora_fin'] ? substr($datos['hora_fin'],0,5) : '';
?>

<div class="encabezado-pagina"><h1>Comisiones</h1>
<p>Dictado de materias<?php if ($cicloActual): ?> del ciclo lectivo actual <strong><?= (int)$cicloActual['anio'] ?></strong><?php endif; ?> (docente, aula, período y horario).</p></div>

<?php if ($mensaje !== ''): ?>
    <p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<div class="tarjeta-form">
<h2><?= $editando ? 'Editar comisión' : 'Crear comisión' ?></h2>
<form method="POST" class="formulario form-grid">
    <input type="hidden" name="id_comision" value="<?= (int)$datos['id_comision'] ?>">
    <div><label>Materia</label>
    <select name="id_materia" required><option value="">--</option>
        <?php foreach ($materias as $m): ?><option value="<?= (int)$m['id_materia'] ?>" <?= ($datos['id_materia']==$m['id_materia'])?'selected':'' ?>><?= htmlspecialchars($m['nombre']) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Docente</label>
    <select name="id_docente" required><option value="">--</option>
        <?php foreach ($docentes as $d): ?><option value="<?= (int)$d['id_docente'] ?>" <?= ($datos['id_docente']==$d['id_docente'])?'selected':'' ?>><?= htmlspecialchars($d['nombre']) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Aula</label>
    <select name="id_aula" required><option value="">--</option>
        <?php foreach ($aulas as $a): ?><option value="<?= (int)$a['id_aula'] ?>" <?= ($datos['id_aula']==$a['id_aula'])?'selected':'' ?>><?= htmlspecialchars($a['nombre']) ?> (cupo <?= (int)$a['cupo_maximo'] ?>)</option><?php endforeach; ?>
    </select></div>
    <div><label>Período</label>
    <select name="id_periodo" required><option value="">--</option>
        <?php foreach ($periodos as $p): ?><option value="<?= (int)$p['id_periodo'] ?>" <?= ($datos['id_periodo']==$p['id_periodo'])?'selected':'' ?>><?= htmlspecialchars($p['nombre']) ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Día</label>
    <select name="dia" required>
        <?php foreach (['Lunes','Martes','Miércoles','Jueves','Viernes'] as $d): ?><option <?= ($datos['dia']===$d)?'selected':'' ?>><?= $d ?></option><?php endforeach; ?>
    </select></div>
    <div><label>Hora inicio</label><input type="time" name="hora_inicio" value="<?= htmlspecialchars($hIni) ?>" required></div>
    <div><label>Hora fin</label><input type="time" name="hora_fin" value="<?= htmlspecialchars($hFin) ?>" required></div>
    <div class="form-full"><button type="submit"><?= $editando ? 'Guardar cambios' : 'Crear comisión' ?></button>
        <?php if ($editando): ?><a href="comisiones.php" class="limpiar" style="margin-left:12px;">Cancelar</a><?php endif; ?></div>
</form>
</div>

<h2>Comisiones activas</h2>
<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por materia, docente, aula o día...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="comisiones.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Materia</th><th>Docente</th><th>Aula</th>
        <th>Día / Horario</th><th>Vacantes</th><th>Acciones</th></tr></thead>
    <tbody>
        <?php if (empty($comisiones)): ?><tr><td colspan="7" style="text-align:center;color:#9ca3af;">Sin resultados.</td></tr><?php endif; ?>
        <?php foreach ($comisiones as $c): ?>
            <tr>
                <td><?= (int) $c['id_comision'] ?></td>
                <td><?= htmlspecialchars($c['materia']) ?></td>
                <td><?= htmlspecialchars($c['docente']) ?></td>
                <td><?= htmlspecialchars($c['aula']) ?></td>
                <td><?= htmlspecialchars($c['dia']) ?>
                    <?= substr($c['hora_inicio'],0,5) ?>-<?= substr($c['hora_fin'],0,5) ?></td>
                <td><?= (int) $c['vacantes_disponibles'] ?>/<?= (int) $c['cupo_maximo'] ?></td>
                <td><a href="comisiones.php?editar=<?= (int)$c['id_comision'] ?>">Editar</a> |
                    <a href="comisiones.php?eliminar=<?= (int)$c['id_comision'] ?>"
                       onclick="return confirm('¿Eliminar esta comisión?')">Eliminar</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
