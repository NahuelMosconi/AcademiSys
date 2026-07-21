<?php
// ============================================================
//  Gestión de Carreras (solo Administrador). Alta, edición y baja de carreras.
//  La baja NO se permite si la carrera tiene alumnos o materias.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Carrera.php';

$carreraModel = new Carrera();
$mensaje = ''; $tipo = '';

// ¿Estamos editando? (link "Editar" -> ?editar=id). Prefill del formulario.
$editId = (int)($_GET['editar'] ?? 0);
$datos = ['id_carrera'=>0, 'nombre'=>'', 'duracion_anios'=>''];
if ($editId > 0) { $e = $carreraModel->buscar($editId); if ($e) $datos = $e; }

// Alta o edición (el mismo formulario; si trae id oculto, es edición).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['id_carrera'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $dur    = (int)($_POST['duracion'] ?? 0);
    if ($nombre === '' || $dur <= 0) {
        $mensaje = 'Completá el nombre y la duración.'; $tipo = 'error';
        $datos = ['id_carrera'=>$id, 'nombre'=>$nombre, 'duracion_anios'=>$dur];
    } else {
        try {
            if ($id > 0) { $carreraModel->actualizar($id, $nombre, $dur); $mensaje = 'Carrera modificada.'; }
            else         { $carreraModel->crear($nombre, $dur);           $mensaje = 'Carrera agregada.'; }
            $tipo = 'exito';
            $datos = ['id_carrera'=>0, 'nombre'=>'', 'duracion_anios'=>''];   // volver a modo alta
        } catch (PDOException $ex) {
            $m = $ex->getMessage();
            $mensaje = strpos($m,'nombre') !== false ? 'Ya existe una carrera con ese nombre.' : 'No se pudo guardar: '.$m;
            $tipo = 'error';
            $datos = ['id_carrera'=>$id, 'nombre'=>$nombre, 'duracion_anios'=>$dur];
        }
    }
}
// Eliminar (baja lógica con control de integridad).
if (isset($_GET['eliminar'])) {
    [$ok, $mensaje] = $carreraModel->darDeBaja((int)$_GET['eliminar']);
    $tipo = $ok ? 'exito' : 'error';
}

$carreras = $carreraModel->listar();
$editando = ((int)($datos['id_carrera'] ?? 0)) > 0;
?>
<div class="encabezado-pagina"><h1>Carreras</h1><p>Alta, edición y baja de carreras.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<div class="tarjeta-form">
<h2><?= $editando ? 'Editar carrera' : 'Agregar carrera' ?></h2>
<form method="POST" class="formulario">
    <input type="hidden" name="id_carrera" value="<?= (int)$datos['id_carrera'] ?>">
    <label>Nombre</label>
    <input type="text" name="nombre" value="<?= htmlspecialchars($datos['nombre']) ?>" required>
    <label>Duración (años)</label>
    <input type="number" name="duracion" min="1" max="10" value="<?= htmlspecialchars((string)$datos['duracion_anios']) ?>" required>
    <button type="submit"><?= $editando ? 'Guardar cambios' : 'Agregar' ?></button>
    <?php if ($editando): ?><a href="carreras.php" class="limpiar" style="margin-left:12px;">Cancelar</a><?php endif; ?>
</form>
</div>

<h2>Listado</h2>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Nombre</th><th>Duración</th><th>Acciones</th></tr></thead>
    <tbody>
        <?php if (empty($carreras)): ?><tr><td colspan="4" style="text-align:center;color:#9ca3af;">Sin carreras.</td></tr><?php endif; ?>
        <?php foreach ($carreras as $c): ?>
            <tr>
                <td><?= (int)$c['id_carrera'] ?></td>
                <td><?= htmlspecialchars($c['nombre']) ?></td>
                <td><?= (int)$c['duracion_anios'] ?> años</td>
                <td>
                    <a href="carreras.php?editar=<?= (int)$c['id_carrera'] ?>">Editar</a> |
                    <a href="carreras.php?eliminar=<?= (int)$c['id_carrera'] ?>" onclick="return confirm('¿Eliminar esta carrera?')">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
