<?php
// ============================================================
//  Pantalla de Materias (solo Administrador). Alta de materias vinculadas a
//  una carrera y listado con buscador.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Materia.php';
require_once __DIR__ . '/../clases/Carrera.php';

$materiaModel = new Materia(); $carreraModel = new Carrera();
$mensaje=''; $tipo='';
// ----- Alta de materia (al enviar el formulario) -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre=trim($_POST['nombre']??''); $anio=(int)($_POST['anio']??0); $idC=(int)($_POST['id_carrera']??0);
    if ($nombre==='' || $anio<=0 || $idC<=0) { $mensaje='Completá todos los campos.'; $tipo='error'; }
    else { $materiaModel->crear($nombre,$anio,$idC); $mensaje='Materia agregada.'; $tipo='exito'; }
}
if (isset($_GET['baja'])) { [$ok, $mensaje] = $materiaModel->darDeBaja((int)$_GET['baja']); $tipo = $ok ? 'exito' : 'error'; }
$q = trim($_GET['q'] ?? '');
$materias=$materiaModel->listar($q); $carreras=$carreraModel->listar();
?>
<div class="encabezado-pagina"><h1>Materias</h1><p>Asignaturas por carrera y año.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>
<div class="tarjeta-form">
<h2>Agregar materia</h2>
<form method="POST" class="formulario">
    <label>Nombre</label><input type="text" name="nombre" required>
    <label>Año</label><input type="number" name="anio" min="1" max="6" required>
    <label>Carrera</label>
    <select name="id_carrera" required><option value="">--</option>
        <?php foreach ($carreras as $c): ?><option value="<?= (int)$c['id_carrera'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?>
    </select>
    <button type="submit">Agregar</button>
</form>
</div>
<h2>Listado</h2>
<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="🔍 Buscar por materia o carrera...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="materias.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Nombre</th><th>Año</th><th>Carrera</th><th>Acción</th></tr></thead>
    <tbody>
        <?php foreach ($materias as $m): ?>
            <tr><td><?= (int)$m['id_materia'] ?></td><td><?= htmlspecialchars($m['nombre']) ?></td>
            <td><?= (int)$m['anio'] ?>°</td><td><?= htmlspecialchars($m['carrera']) ?></td>
            <td><a href="materias.php?baja=<?= (int)$m['id_materia'] ?>" onclick="return confirm('¿Baja?')">Baja</a></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
