<?php
// Punto de entrada: redirige según haya o no sesión.
require_once __DIR__ . '/config/sesion.php';
header('Location: ' . (estaLogueado() ? 'vistas/panel.php' : 'login.php'));
exit;
