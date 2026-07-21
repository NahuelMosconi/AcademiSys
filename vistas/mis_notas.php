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
$actaModel = new Acta();
$notas   = $actaModel->listarPorAlumno((int)$u['id_alumno']);
$estados = $actaModel->estadoPorAlumno((int)$u['id_alumno']);   // régimen UCh

// Clase de color (badge) según el estado UCh de la materia.
function badgeEstado(string $e): string {
    switch ($e) {
        case 'Aprobada':     return 'badge-activa';
        case 'Promocionada': return 'badge-promocion';
        case 'Regular':      return 'badge-regular';
        default:             return 'badge-anulada';   // Libre
    }
}
?>
<div class="encabezado-pagina"><h1>Mis notas</h1>
<p>Tu historial académico (parciales, finales, recuperatorios) y el estado de cada materia.</p></div>

<?php if (!empty($estados)): ?>
<h2>Estado de mis materias</h2>
<p class="ayuda">Régimen UCh: <strong>Promocionada</strong> (promedio de parciales ≥ 7 + TP, sin final),
   <strong>Regular</strong> (parciales y TP aprobados, debe rendir final),
   <strong>Aprobada</strong> (final rendido) o <strong>Libre</strong>.</p>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Materia</th><th>Estado</th></tr></thead>
    <tbody>
        <?php foreach ($estados as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['materia']) ?></td>
                <td><span class="<?= badgeEstado($e['estado']) ?>"><?= htmlspecialchars($e['estado']) ?></span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<h2>Detalle de notas</h2>
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
