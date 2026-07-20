<?php
// ============================================================
//  Mis Notas (solo Alumno). Muestra el historial de notas del alumno logueado
//  (parciales y finales). Solo ve las suyas.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Alumno']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../clases/Acta.php';

// Solo el alumno entra (requerirRol). Mostramos únicamente SUS notas.
$u = $_SESSION['usuario'];
$notas = (new Acta())->listarPorAlumno((int)$u['id_alumno']);
?>
<div class="encabezado-pagina"><h1>Mis notas</h1>
<p>Tu historial académico (parciales, finales y recuperatorios).</p></div>

<?php if (empty($notas)): ?>
    <p class="ayuda">Todavía no tenés notas cargadas.</p>
<?php else: ?>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Materia</th><th>Tipo</th><th>Nota</th><th>Fecha</th></tr></thead>
    <tbody>
        <?php foreach ($notas as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['materia']) ?></td>
                <td><?= htmlspecialchars($n['tipo']) ?></td>
                <td><strong><?= htmlspecialchars($n['nota_final']) ?></strong></td>
                <td><?= htmlspecialchars($n['fecha']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
