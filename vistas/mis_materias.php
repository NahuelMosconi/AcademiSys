<?php
// ============================================================
//  Mis Materias y Horarios (solo Alumno). Muestra las materias en las que el
//  alumno está inscripto, con docente, aula, día y horario. Solo lectura.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Alumno']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Inscripcion.php';

// requerirRol(['Alumno']) ya bloqueó a cualquier otro rol antes de llegar acá.
$u = $_SESSION['usuario'];
// Traemos SOLO las inscripciones del alumno logueado (su id_alumno viene de la sesión).
$inscripciones = (new Inscripcion())->listarPorAlumno((int)$u['id_alumno']);
?>
<div class="encabezado-pagina"><h1>Mis materias y horarios</h1>
<p>Materias en las que estás inscripto este período.</p></div>

<?php if (empty($inscripciones)): ?>
    <p class="ayuda">Todavía no estás inscripto en ninguna materia. Hablá con administración.</p>
<?php else: ?>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Materia</th><th>Docente</th><th>Aula</th><th>Día</th><th>Horario</th><th>Período</th></tr></thead>
    <tbody>
        <?php foreach ($inscripciones as $i): ?>
            <tr>
                <td><?= htmlspecialchars($i['materia']) ?></td>
                <td><?= htmlspecialchars($i['docente']) ?></td>
                <td><?= htmlspecialchars($i['aula']) ?></td>
                <td><?= htmlspecialchars($i['dia']) ?></td>
                <td><?= substr($i['hora_inicio'],0,5) ?> - <?= substr($i['hora_fin'],0,5) ?></td>
                <td><?= htmlspecialchars($i['periodo']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
