<?php
/**
 * Funciones de sesión: login, control de rol y cierre por inactividad.
 */

// Iniciar la sesión una sola vez
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tiempo máximo sin actividad: 20 minutos (en segundos)
const TIMEOUT = 1200;

/** ¿Hay un usuario logueado? */
function estaLogueado(): bool
{
    return isset($_SESSION['usuario']);
}

/** Cierra la sesión si pasó demasiado tiempo sin actividad. */
function controlarInactividad(): void
{
    if (!estaLogueado()) return;

    if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: login.php?expirado=1');
        exit;
    }
    $_SESSION['ultimo_acceso'] = time();  // renueva el contador en cada página
}

/** Obliga a estar logueado; si no, manda al login. */
function requerirLogin(): void
{
    if (!estaLogueado()) {
        header('Location: login.php');
        exit;
    }
    controlarInactividad();
}

/** Obliga a tener uno de los roles indicados; si no, muestra 403. */
function requerirRol(array $rolesPermitidos): void
{
    requerirLogin();
    if (!in_array($_SESSION['usuario']['rol'], $rolesPermitidos, true)) {
        http_response_code(403);
        die('Acceso denegado: no tenés permiso para ver esta página.');
    }
}
