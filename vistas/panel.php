<?php
// ============================================================
//  Panel / Dashboard. Es la pantalla de inicio. Muestra indicadores distintos
//  según el rol: el alumno ve sus métricas, el profesor las de sus comisiones,
//  y el administrador los totales del sistema.
// ============================================================
require_once __DIR__ . '/_header.php';
require_once __DIR__ . '/../config/Database.php';
$db = Database::conectar();
$u = $_SESSION['usuario'];          // datos del usuario logueado (guardados en la sesión)
$esProfesor = ($u['rol'] === 'Profesor');
$esAlumno = ($u['rol'] === 'Alumno');
?>
<div class="encabezado-pagina">
    <h1>Hola, <?= htmlspecialchars($u['nombre']) ?></h1>
    <p>Sesión iniciada como <?= htmlspecialchars($u['rol']) ?>.</p>
</div>

<?php // ----- Dashboard del ALUMNO: cuenta solo SUS datos -----
if ($esAlumno && $u['id_alumno']):
    $al = (int)$u['id_alumno'];
    $misMaterias = (int)$db->query("SELECT COUNT(*) FROM Inscripcion WHERE id_alumno=$al AND estado='ACTIVA'")->fetchColumn();
    $misNotas = (int)$db->query("SELECT COUNT(*) FROM Acta WHERE id_alumno=$al")->fetchColumn();
    // Materias aprobadas según el régimen UCh: por Final o por Promoción (usa fn_estado_materia).
    $finales = (int)$db->query("SELECT COUNT(*) FROM (SELECT DISTINCT id_materia FROM Acta WHERE id_alumno=$al) t
        WHERE fn_estado_materia($al, t.id_materia) COLLATE utf8mb4_unicode_ci IN ('Aprobada','Promocionada')")->fetchColumn();
    $carrera = $db->query("SELECT c.nombre FROM Alumno a JOIN Carrera c ON c.id_carrera=a.id_carrera WHERE a.id_alumno=$al")->fetchColumn();
?>
<div class="ayuda">Carrera: <strong><?= htmlspecialchars($carrera ?: 'sin asignar') ?></strong></div>
<div class="tarjetas">
    <div class="tarjeta"><div class="ico-card ico-azul"><?= icono('calendario') ?></div><h3>Materias cursando</h3><p class="numero"><?= $misMaterias ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-rojo"><?= icono('documento') ?></div><h3>Notas registradas</h3><p class="numero"><?= $misNotas ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-verde"><?= icono('check') ?></div><h3>Materias aprobadas</h3><p class="numero"><?= $finales ?></p></div>
</div>
<?php elseif ($esProfesor && $u['id_docente']):
    $doc = (int)$u['id_docente'];
    $misComisiones = (int)$db->query("SELECT COUNT(*) FROM Comision WHERE id_docente=$doc AND activo=1")->fetchColumn();
    $misMaterias = (int)$db->query("SELECT COUNT(DISTINCT id_materia) FROM Comision WHERE id_docente=$doc AND activo=1")->fetchColumn();
    $misAlumnos = (int)$db->query("SELECT COUNT(DISTINCT i.id_alumno) FROM Inscripcion i JOIN Comision c ON c.id_comision=i.id_comision WHERE c.id_docente=$doc AND i.estado='ACTIVA'")->fetchColumn();
    $notasCargadas = (int)$db->query("SELECT COUNT(*) FROM Acta WHERE id_materia IN (SELECT DISTINCT id_materia FROM Comision WHERE id_docente=$doc)")->fetchColumn();
?>
<div class="tarjetas">
    <div class="tarjeta"><div class="ico-card ico-naranja"><?= icono('comision') ?></div><h3>Mis comisiones</h3><p class="numero"><?= $misComisiones ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-azul"><?= icono('materia') ?></div><h3>Mis materias</h3><p class="numero"><?= $misMaterias ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-verde"><?= icono('alumno') ?></div><h3>Mis alumnos</h3><p class="numero"><?= $misAlumnos ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-rojo"><?= icono('documento') ?></div><h3>Notas (mis materias)</h3><p class="numero"><?= $notasCargadas ?></p></div>
</div>
<?php else:
    $totalAlumnos = (int)$db->query("SELECT COUNT(*) FROM Alumno WHERE activo=1")->fetchColumn();
    $totalInsc    = (int)$db->query("SELECT COUNT(*) FROM Inscripcion WHERE estado='ACTIVA'")->fetchColumn();
    $totalCom     = (int)$db->query("SELECT COUNT(*) FROM Comision WHERE activo=1")->fetchColumn();
    $totalActas   = (int)$db->query("SELECT COUNT(*) FROM Acta")->fetchColumn();
?>
<div class="tarjetas">
    <div class="tarjeta"><div class="ico-card ico-azul"><?= icono('alumno') ?></div><h3>Alumnos activos</h3><p class="numero"><?= number_format($totalAlumnos,0,',','.') ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-verde"><?= icono('inscripcion') ?></div><h3>Inscripciones activas</h3><p class="numero"><?= number_format($totalInsc,0,',','.') ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-naranja"><?= icono('comision') ?></div><h3>Comisiones</h3><p class="numero"><?= number_format($totalCom,0,',','.') ?></p></div>
    <div class="tarjeta"><div class="ico-card ico-rojo"><?= icono('documento') ?></div><h3>Notas registradas</h3><p class="numero"><?= number_format($totalActas,0,',','.') ?></p></div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/_footer.php'; ?>
