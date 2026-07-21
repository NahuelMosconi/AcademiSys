<?php
require_once __DIR__ . '/../config/sesion.php';
require_once __DIR__ . '/../config/iconos.php';
requerirLogin();
$u = $_SESSION['usuario'];
// Detecta la página actual para marcar el item activo en el menú
$actual = basename($_SERVER['PHP_SELF']);
function activo($pag, $actual) { return $pag === $actual ? 'activo' : ''; }
// Iniciales para el avatar
$iniciales = strtoupper(substr($u['nombre'], 0, 1));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AcademiSys</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
<div class="layout">

    <!-- ===== SIDEBAR (menú lateral) ===== -->
    <aside class="sidebar">
        <div class="sidebar-marca">
            <img class="marca-logo" src="../assets/logo-mark.svg" alt="">
            <span>AcademiSys</span>
        </div>

        <div class="sidebar-seccion">General</div>
        <nav>
            <a href="panel.php" class="<?= activo('panel.php',$actual) ?>"><?= icono('inicio') ?><span class="txt">Inicio</span></a>
            <?php if ($u['rol'] === 'Alumno'): ?>
                <a href="mis_materias.php" class="<?= activo('mis_materias.php',$actual) ?>"><?= icono('calendario') ?><span class="txt">Mis materias</span></a>
                <a href="mis_notas.php" class="<?= activo('mis_notas.php',$actual) ?>"><?= icono('documento') ?><span class="txt">Mis notas</span></a>
            <?php else: ?>
                <a href="inscripciones.php" class="<?= activo('inscripciones.php',$actual) ?>"><?= icono('inscripcion') ?><span class="txt">Inscripciones</span></a>
                <a href="notas.php" class="<?= activo('notas.php',$actual) ?>"><?= icono('documento') ?><span class="txt">Notas</span></a>
            <?php endif; ?>
        </nav>

        <?php if ($u['rol'] === 'Administrador'): ?>
        <div class="sidebar-seccion">Gestión</div>
        <nav>
            <a href="alumnos.php" class="<?= activo('alumnos.php',$actual).' '.activo('alumno_form.php',$actual) ?>"><?= icono('alumno') ?><span class="txt">Alumnos</span></a>
            <a href="docentes.php" class="<?= activo('docentes.php',$actual) ?>"><?= icono('docente') ?><span class="txt">Docentes</span></a>
            <a href="carreras.php" class="<?= activo('carreras.php',$actual) ?>"><?= icono('carrera') ?><span class="txt">Carreras</span></a>
            <a href="materias.php" class="<?= activo('materias.php',$actual) ?>"><?= icono('materia') ?><span class="txt">Materias</span></a>
            <a href="comisiones.php" class="<?= activo('comisiones.php',$actual) ?>"><?= icono('comision') ?><span class="txt">Comisiones</span></a>
            <a href="ciclos.php" class="<?= activo('ciclos.php',$actual) ?>"><?= icono('ciclo') ?><span class="txt">Ciclos lectivos</span></a>
            <a href="auditoria.php" class="<?= activo('auditoria.php',$actual) ?>"><?= icono('auditoria') ?><span class="txt">Auditoría</span></a>
            <a href="usuarios.php" class="<?= activo('usuarios.php',$actual) ?>"><?= icono('usuarios') ?><span class="txt">Usuarios</span></a>
        </nav>
        <?php endif; ?>

        <div class="sidebar-seccion">Cuenta</div>
        <nav>
            <a href="perfil.php" class="<?= activo('perfil.php',$actual) ?>"><?= icono('perfil') ?><span class="txt">Mi perfil</span></a>
        </nav>

        <div class="sidebar-pie">
            <div class="sidebar-user">
                <div class="sidebar-avatar"><?= htmlspecialchars($iniciales) ?></div>
                <div class="info">
                    <?= htmlspecialchars($u['nombre']) ?><br>
                    <span class="rol"><?= htmlspecialchars($u['rol']) ?></span>
                </div>
            </div>
            <a href="../logout.php" class="btn-salir"><?= icono('salir') ?><span>Cerrar sesión</span></a>
        </div>
    </aside>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <main class="principal">
