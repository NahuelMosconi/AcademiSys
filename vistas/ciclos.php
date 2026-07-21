<?php
// ============================================================
//  Ciclos lectivos (solo Administrador). El ciclo lectivo es el AÑO académico:
//  uno está ABIERTO (el actual) y los demás CERRADO (anteriores). El botón
//  "Cerrar ciclo actual" cierra el abierto y abre automáticamente el siguiente
//  (vía el procedimiento CerrarCicloLectivo).
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/../clases/CicloLectivo.php';

$model = new CicloLectivo();
$mensaje = ''; $tipo = '';

// Cerrar el ciclo actual (y abrir el siguiente).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar'])) {
    [$ok, $mensaje] = $model->cerrarCiclo();
    $tipo = $ok ? 'exito' : 'error';
}

require_once __DIR__ . '/_header.php';
$ciclos = $model->listar();
$actual = $model->actual();

function badgeCiclo(string $estado): string {
    return $estado === 'ABIERTO' ? 'badge-activa' : 'badge-anulada';
}
?>
<div class="encabezado-pagina"><h1>Ciclos lectivos</h1>
<p>El año académico. Solo uno está abierto; al cerrarlo se abre el siguiente.</p></div>
<?php if ($mensaje !== ''): ?><p class="aviso <?= $tipo ?>"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?>

<div class="tarjeta-form">
<h2>Ciclo actual</h2>
<?php if ($actual): ?>
    <p class="ayuda">El ciclo lectivo abierto es <strong><?= (int)$actual['anio'] ?></strong>
       (abierto el <?= htmlspecialchars($actual['fecha_apertura']) ?>).
       Las comisiones nuevas y las inscripciones se cargan en este ciclo.</p>
    <form method="POST" onsubmit="return confirm('¿Cerrar el ciclo <?= (int)$actual['anio'] ?> y abrir el <?= (int)$actual['anio']+1 ?>? Esta acción archiva el año actual.');">
        <input type="hidden" name="cerrar" value="1">
        <button type="submit">Cerrar ciclo <?= (int)$actual['anio'] ?> y abrir <?= (int)$actual['anio']+1 ?></button>
    </form>
<?php else: ?>
    <p class="ayuda">No hay ningún ciclo lectivo abierto.</p>
<?php endif; ?>
</div>

<h2>Historial de ciclos</h2>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>Año</th><th>Estado</th><th>Apertura</th><th>Cierre</th><th>Comisiones</th></tr></thead>
    <tbody>
        <?php foreach ($ciclos as $c): ?>
            <tr>
                <td><strong><?= (int)$c['anio'] ?></strong></td>
                <td><span class="<?= badgeCiclo($c['estado']) ?>"><?= htmlspecialchars($c['estado']) ?></span></td>
                <td><?= htmlspecialchars($c['fecha_apertura']) ?></td>
                <td><?= htmlspecialchars($c['fecha_cierre'] ?? '—') ?></td>
                <td><?= (int)$c['comisiones'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
