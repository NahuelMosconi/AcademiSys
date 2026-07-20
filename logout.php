<?php
// Cierra la sesión y vuelve al login.
require_once __DIR__ . '/config/sesion.php';
session_unset();
session_destroy();
header('Location: login.php');
exit;
