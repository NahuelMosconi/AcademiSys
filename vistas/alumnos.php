<?php
// ============================================================
//  Pantalla de Alumnos (solo Administrador). Lista los alumnos con buscador
//  y permite ir al alta/edición y dar de baja (baja lógica).
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Alumno.php';

$alumnoModel = new Alumno();
$mensaje = ''; $tipo = '';

if (isset($_GET['baja'])) {
    $alumnoModel->darDeBaja((int) $_GET['baja']);
    $mensaje = 'Alumno dado de baja (baja lógica).'; $tipo = 'exito';
}
if (isset($_GET['ok'])) { $mensaje = 'Operación realizada correctamente.'; $tipo = 'exito'; }

// Filtro de búsqueda (viene por GET ?q=texto)
$q = trim($_GET['q'] ?? '');
$alumnos = $alumnoModel->listar($q);
?>

<div class="encabezado-pagina"><h1>Alumnos</h1><p>Gestión de estudiantes (alta, edición y baja lógica).</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<a href="alumno_form.php" class="boton"><?= icono('mas') ?>Nuevo alumno</a>

<!-- CUADRO DE BÚSQUEDA -->
<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>"
           placeholder="Buscar por nombre, legajo, DNI, teléfono o email...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="alumnos.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>

<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Legajo</th><th>Nombre</th><th>DNI</th><th>Teléfono</th><th>Email</th><th>Carrera</th><th>Acciones</th></tr></thead>
    <tbody>
        <?php if (empty($alumnos)): ?>
            <tr><td colspan="6" style="text-align:center;color:#9ca3af;">Sin resultados.</td></tr>
        <?php endif; ?>
        <?php foreach ($alumnos as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['legajo']) ?></td>
                <td><?= htmlspecialchars($a['nombre']) ?></td>
                <td><?= htmlspecialchars($a['dni']) ?></td>
                <td><?= htmlspecialchars($a['telefono']) ?></td>
                <td><?= htmlspecialchars($a['email']) ?></td>
                <td><?= htmlspecialchars($a['carrera'] ?? '—') ?></td>
                <td>
                    <a href="alumno_form.php?id=<?= (int)$a['id_alumno'] ?>">Editar</a> |
                    <a href="alumnos.php?baja=<?= (int)$a['id_alumno'] ?>"
                       onclick="return confirm('¿Dar de baja este alumno?')">Baja</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>
