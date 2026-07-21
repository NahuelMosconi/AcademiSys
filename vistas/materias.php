<?php
// ============================================================
//  Pantalla de Materias (solo Administrador). Alta, edición y baja de materias
//  vinculadas a una carrera, con buscador.
//  La baja NO se permite si la materia tiene comisiones (profesor) o alumnos.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Materia.php';
require_once __DIR__ . '/../clases/Carrera.php';

$materiaModel = new Materia(); $carreraModel = new Carrera();
$mensaje=''; $tipo='';

// ¿Editando? (link "Editar" -> ?editar=id). Prefill del formulario.
$editId = (int)($_GET['editar'] ?? 0);
$datos = ['id_materia'=>0, 'nombre'=>'', 'anio'=>'', 'id_carrera'=>''];
if ($editId > 0) { $e = $materiaModel->buscar($editId); if ($e) $datos = $e; }

// ----- Alta o edición (el mismo formulario; si trae id oculto, es edición) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id_materia'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $anio   = (int)($_POST['anio'] ?? 0);
    $idC    = (int)($_POST['id_carrera'] ?? 0);
    if ($nombre==='' || $anio<=0 || $idC<=0) {
        $mensaje='Completá todos los campos.'; $tipo='error';
        $datos=['id_materia'=>$id, 'nombre'=>$nombre, 'anio'=>$anio, 'id_carrera'=>$idC];
    } else {
        if ($id > 0) { $materiaModel->actualizar($id, $nombre, $anio, $idC); $mensaje='Materia modificada.'; }
        else         { $materiaModel->crear($nombre, $anio, $idC);           $mensaje='Materia agregada.'; }
        $tipo='exito';
        $datos=['id_materia'=>0, 'nombre'=>'', 'anio'=>'', 'id_carrera'=>''];   // volver a modo alta
    }
}
// ----- Eliminar (baja lógica con control de integridad) -----
if (isset($_GET['eliminar'])) { [$ok, $mensaje] = $materiaModel->darDeBaja((int)$_GET['eliminar']); $tipo = $ok ? 'exito' : 'error'; }

$q = trim($_GET['q'] ?? '');
$materias = $materiaModel->listar($q); $carreras = $carreraModel->listar();
$editando = ((int)($datos['id_materia'] ?? 0)) > 0;
?>
<div class="encabezado-pagina"><h1>Materias</h1><p>Asignaturas por carrera y año.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>
<div class="tarjeta-form">
<h2><?= $editando ? 'Editar materia' : 'Agregar materia' ?></h2>
<form method="POST" class="formulario">
    <input type="hidden" name="id_materia" value="<?= (int)$datos['id_materia'] ?>">
    <label>Nombre</label><input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
    <label>Año</label><input type="number" name="anio" min="1" max="6" value="<?= htmlspecialchars((string)$datos['anio']) ?>" required>
    <label>Carrera</label>
    <select name="id_carrera" required><option value="">--</option>
        <?php foreach ($carreras as $c): ?><option value="<?= (int)$c['id_carrera'] ?>" <?= ($datos['id_carrera']==$c['id_carrera'])?'selected':'' ?>><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
    </select>
    <button type="submit"><?= $editando ? 'Guardar cambios' : 'Agregar' ?></button>
    <?php if ($editando): ?><a href="materias.php" class="limpiar" style="margin-left:12px;">Cancelar</a><?php endif; ?>
</form>
</div>
<h2>Listado</h2>
<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por materia o carrera...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="materias.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Nombre</th><th>Año</th><th>Carrera</th><th>Acciones</th></tr></thead>
    <tbody>
        <?php if (empty($materias)): ?><tr><td colspan="5" style="text-align:center;color:#9ca3af;">Sin resultados.</td></tr><?php endif; ?>
        <?php foreach ($materias as $m): ?>
            <tr><td><?= (int)$m['id_materia'] ?></td><td><?= htmlspecialchars($m['nombre']) ?></td>
            <td><?= (int)$m['anio'] ?>°</td><td><?= htmlspecialchars($m['carrera']) ?></td>
            <td><a href="materias.php?editar=<?= (int)$m['id_materia'] ?>">Editar</a> |
                <a href="materias.php?eliminar=<?= (int)$m['id_materia'] ?>" onclick="return confirm('¿Eliminar esta materia?')">Eliminar</a></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
