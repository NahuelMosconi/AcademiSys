<?php
// ============================================================
//  Pantalla de Auditoría (solo Administrador). Muestra el registro de
//  inscripciones (con IP de origen) y de notas, que llenan los triggers/motor.
// ============================================================
require_once __DIR__ . '/../config/sesion.php';
requerirRol(['Administrador']);
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../config/Database.php';

$db = Database::conectar();
$q = trim($_GET['q'] ?? '');
$sqlInsc = "SELECT au.id_auditoria, al.nombre AS alumno, m.nombre AS materia,
            au.ip_origen, au.fecha_registro
     FROM AuditoriaInscripcion au
     JOIN Alumno al ON al.id_alumno = au.id_alumno
     JOIN Comision c ON c.id_comision = au.id_comision
     JOIN Materia m ON m.id_materia = c.id_materia ";
if ($q !== '') {
    $st = $db->prepare($sqlInsc . "WHERE al.nombre LIKE :f1 OR m.nombre LIKE :f2 OR au.ip_origen LIKE :f3
                                   ORDER BY au.fecha_registro DESC LIMIT 50");
    $like = "%$q%";
    $st->execute(['f1'=>$like,'f2'=>$like,'f3'=>$like]);
    $insc = $st->fetchAll();
} else {
    $insc = $db->query($sqlInsc . "ORDER BY au.fecha_registro DESC LIMIT 50")->fetchAll();
}
$notas = $db->query(
    "SELECT au.id_auditoria, al.nombre AS alumno, m.nombre AS materia, au.nota, au.fecha_registro
     FROM AuditoriaNota au
     JOIN Alumno al ON al.id_alumno = au.id_alumno
     JOIN Materia m ON m.id_materia = au.id_materia
     ORDER BY au.fecha_registro DESC LIMIT 50")->fetchAll();
?>
<div class="encabezado-pagina"><h1>Auditoría</h1><p>Registros automáticos de inscripciones y notas.</p></div>

<h2>Auditoría de inscripciones (con IP de origen)</h2>
<form method="GET" class="barra-busqueda">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Buscar por alumno, materia o IP...">
    <button type="submit">Filtrar</button>
    <?php if ($q !== ''): ?><a href="auditoria.php" class="limpiar">Limpiar</a><?php endif; ?>
</form>
<p class="ayuda">El motor de inscripción registra acá cada inscripción confirmada, con la IP y la fecha. Esto se hace dentro de la transacción ACID.</p>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Alumno</th><th>Materia</th><th>IP origen</th><th>Fecha</th></tr></thead>
    <tbody>
        <?php foreach ($insc as $r): ?>
            <tr><td><?= (int)$r['id_auditoria'] ?></td><td><?= htmlspecialchars($r['alumno']) ?></td>
            <td><?= htmlspecialchars($r['materia']) ?></td><td><code><?= htmlspecialchars($r['ip_origen']) ?></code></td>
            <td><?= htmlspecialchars($r['fecha_registro']) ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

<h2>Auditoría de notas (vía trigger)</h2>
<p class="ayuda">Esta tabla la llena un trigger automáticamente cada vez que se carga una nota final.</p>
<div class="tabla-wrap">
<table class="tabla">
    <thead><tr><th>ID</th><th>Alumno</th><th>Materia</th><th>Nota</th><th>Registrado</th></tr></thead>
    <tbody>
        <?php foreach ($notas as $r): ?>
            <tr><td><?= (int)$r['id_auditoria'] ?></td><td><?= htmlspecialchars($r['alumno']) ?></td>
            <td><?= htmlspecialchars($r['materia']) ?></td><td><?= htmlspecialchars($r['nota']) ?></td>
            <td><?= htmlspecialchars($r['fecha_registro']) ?></td></tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require_once __DIR__ . '/_footer.php'; ?>
