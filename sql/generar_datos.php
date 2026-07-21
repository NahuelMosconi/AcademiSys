<?php
/**
 * Generador de sql/02_datos.sql — datos de ejemplo FUNCIONALES para AcademiSys.
 * Cada docente y cada alumno se crea CON su cuenta de acceso (login = DNI,
 * contraseña inicial = DNI hasheada con password_hash/bcrypt), para que todos
 * puedan ingresar. No hay usuarios vacíos.
 *
 * Se ejecuta una vez y vuelca el archivo SQL. No forma parte de la app:
 * es una utilidad para (re)generar o ampliar los datos de ejemplo.
 * Uso:  php sql/generar_datos.php   (deja sql/02_datos.sql actualizado)
 */

// ---- helpers ----------------------------------------------------------------
function q(string $s): string { return "'" . str_replace("'", "''", $s) . "'"; }
function hash_dni(string $dni): string { return password_hash($dni, PASSWORD_DEFAULT); }
function slug(string $s): string {
    $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n',
                    'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n']);
    $s = strtolower($s);
    $s = preg_replace('/[^a-z ]/', '', $s);
    $s = str_replace(' ', '.', trim($s));
    return $s;
}

// ---- datos base -------------------------------------------------------------
$carreras = [
    // id => [nombre, duracion_anios]
    1 => ['Tecnicatura en Programación', 3],
    2 => ['Tecnicatura en Análisis de Sistemas', 3],
    3 => ['Tecnicatura en Redes', 2],
];

$aulas = [
    1 => ['Aula 101', 30],
    2 => ['Aula 102', 25],
    3 => ['Laboratorio A', 20],
    4 => ['Laboratorio B', 20],
];

$periodos = [
    1 => ['1er Cuatrimestre 2025', 2025],
    2 => ['2do Cuatrimestre 2025', 2025],
];

// docentes: id => [nombre, dni]
$docentes = [
    1 => ['Laura Gómez',       '20000001'],
    2 => ['Martín Pérez',      '20000002'],
    3 => ['Ana Torres',        '20000003'],
    4 => ['Diego Fernández',   '20000004'],
    5 => ['Sofía Ramírez',     '20000005'],
    6 => ['Javier López',      '20000006'],
];

// alumnos: id => [nombre, dni, id_carrera]
$alumnos = [
    1  => ['Rodrigo Ramírez',   '38000001', 1],
    2  => ['Camila Suárez',     '38000002', 1],
    3  => ['Lucas Molina',      '38000003', 1],
    4  => ['Valentina Ríos',    '38000004', 1],
    5  => ['Mateo Castro',      '38000005', 1],
    6  => ['Julieta Herrera',   '38000006', 1],
    7  => ['Tomás Aguirre',     '38000007', 1],
    8  => ['Florencia Vega',    '38000008', 2],
    9  => ['Nicolás Medina',    '38000009', 2],
    10 => ['Agustina Rojas',    '38000010', 2],
    11 => ['Franco Domínguez',  '38000011', 2],
    12 => ['Martina Silva',     '38000012', 3],
    13 => ['Bruno Sosa',        '38000013', 3],
    14 => ['Carla Núñez',       '38000014', 3],
    15 => ['Iván Paredes',      '38000015', 3],
];

// materias: id => [nombre, anio, id_carrera]
$materias = [
    1  => ['Introducción a la Programación', 1, 1],
    2  => ['Programación Orientada a Objetos', 2, 1],
    3  => ['Estructuras de Datos', 2, 1],
    4  => ['Bases de Datos', 2, 1],
    5  => ['Desarrollo Web', 3, 1],
    6  => ['Análisis de Sistemas I', 1, 2],
    7  => ['Análisis de Sistemas II', 2, 2],
    8  => ['Ingeniería de Software', 3, 2],
    9  => ['Fundamentos de Redes', 1, 3],
    10 => ['Administración de Redes', 2, 3],
];

// correlativas: [id_materia, id_materia_previa]
$correlativas = [
    [2, 1], [3, 1],
    [5, 2], [5, 4],
    [7, 6],
    [8, 7],
    [10, 9],
];

// comisiones: id => [id_materia, id_docente, id_aula, id_periodo, dia, hi, hf]
// Cuidado: sin choques de aula/docente el mismo día/horario (lo valida un trigger).
$comisiones = [
    1  => [1, 1, 1, 1, 'Lunes',     '08:00', '10:00'],
    2  => [2, 2, 2, 1, 'Lunes',     '10:00', '12:00'],
    3  => [3, 3, 1, 1, 'Martes',    '08:00', '10:00'],
    4  => [4, 4, 3, 1, 'Martes',    '10:00', '12:00'],
    5  => [5, 1, 1, 1, 'Miércoles', '08:00', '10:00'],
    6  => [6, 5, 2, 1, 'Lunes',     '08:00', '10:00'],
    7  => [7, 5, 2, 1, 'Martes',    '08:00', '10:00'],
    8  => [9, 6, 4, 1, 'Miércoles', '10:00', '12:00'],
    9  => [8, 4, 3, 1, 'Miércoles', '08:00', '10:00'],
    10 => [10, 6, 4, 1, 'Jueves',   '08:00', '10:00'],
];

// inscripciones: id_alumno => [id_comision, ...]  (comisiones de su carrera)
$inscripciones = [
    1  => [1, 2, 3], 2 => [1, 4], 3 => [1, 2], 4 => [3, 4], 5 => [1],
    6  => [2, 5], 7 => [1, 3],
    8  => [6, 7], 9 => [6], 10 => [7, 9], 11 => [6, 9],
    12 => [8], 13 => [8, 10], 14 => [10], 15 => [8],
];

// notas: [id_alumno, id_materia, tipo, nota]
$notas = [
    [1, 1, '1er Parcial', 7.50],
    [1, 1, '2do Parcial', 8.00],
    [1, 1, 'Final',       8.00],
    [2, 1, '1er Parcial', 6.00],
    [2, 1, 'Final',       5.00],
    [3, 1, '1er Parcial', 4.00],
    [6, 2, '1er Parcial', 9.00],
    [8, 6, '1er Parcial', 8.00],
    [8, 6, 'Final',       9.00],
    [10, 6, 'Final',      7.00],
    [10, 7, '1er Parcial', 8.00],
    [13, 9, '1er Parcial', 6.50],
];

// ---- emisión del SQL --------------------------------------------------------
$out = [];
$out[] = "-- ============================================================";
$out[] = "--  ACADEMISYS - Datos de ejemplo FUNCIONALES";
$out[] = "--  Ejecutar DESPUÉS de sql/01_estructura.sql (que crea el esquema,";
$out[] = "--  los roles y el administrador inicial).";
$out[] = "--";
$out[] = "--  Todos los docentes y alumnos vienen CON su cuenta de acceso:";
$out[] = "--  login por DNI y contraseña inicial = DNI. No hay usuarios vacíos.";
$out[] = "--  (Este archivo lo genera sql/generar_datos.php; los password_hash";
$out[] = "--   son bcrypt reales del propio DNI de cada uno.)";
$out[] = "-- ============================================================";
$out[] = "";
$out[] = "USE academisys;";
$out[] = "";
$out[] = "-- Interpretar este archivo como UTF-8 al importar (acentos correctos).";
$out[] = "SET NAMES utf8mb4;";
$out[] = "";
$out[] = "-- Ids de rol resueltos por nombre (no dependemos de ids fijos).";
$out[] = "SET @rol_prof = (SELECT id_rol FROM Rol WHERE nombre = 'Profesor');";
$out[] = "SET @rol_alu  = (SELECT id_rol FROM Rol WHERE nombre = 'Alumno');";
$out[] = "";

$out[] = "-- ---------- Carreras ----------";
foreach ($carreras as $id => $c) {
    $out[] = "INSERT INTO Carrera (id_carrera, nombre, duracion_anios) VALUES ($id, ".q($c[0]).", {$c[1]});";
}
$out[] = "";

$out[] = "-- ---------- Aulas ----------";
foreach ($aulas as $id => $a) {
    $out[] = "INSERT INTO Aula (id_aula, nombre, cupo_maximo) VALUES ($id, ".q($a[0]).", {$a[1]});";
}
$out[] = "";

$out[] = "-- ---------- Períodos lectivos ----------";
foreach ($periodos as $id => $p) {
    $out[] = "INSERT INTO PeriodoLectivo (id_periodo, nombre, anio) VALUES ($id, ".q($p[0]).", {$p[1]});";
}
$out[] = "";

$out[] = "-- ---------- Docentes + su cuenta de acceso (rol Profesor) ----------";
foreach ($docentes as $id => $d) {
    [$nombre, $dni] = $d;
    $email = slug($nombre) . '@academisys.edu';
    $out[] = "INSERT INTO Docente (id_docente, nombre, dni, email) VALUES ($id, ".q($nombre).", ".q($dni).", ".q($email).");";
    $out[] = "INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ("
           . q($nombre).", ".q($dni).", ".q($email).", ".q(hash_dni($dni)).", @rol_prof, $id);";
}
$out[] = "";

$out[] = "-- ---------- Alumnos + su cuenta de acceso (rol Alumno) ----------";
$n = 0;
foreach ($alumnos as $id => $a) {
    $n++;
    [$nombre, $dni, $idCarrera] = $a;
    $legajo = sprintf('A2025%03d', $id);
    $email  = slug($nombre) . '@alumnos.academisys.edu';
    $tel    = sprintf('11-4000-%04d', $id);
    $out[] = "INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES ("
           . "$id, ".q($legajo).", ".q($nombre).", ".q($dni).", ".q($tel).", ".q($email).", $idCarrera);";
    $out[] = "INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ("
           . q($nombre).", ".q($dni).", ".q($email).", ".q(hash_dni($dni)).", @rol_alu, $id);";
}
$out[] = "";

$out[] = "-- ---------- Materias ----------";
foreach ($materias as $id => $m) {
    $out[] = "INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES ($id, ".q($m[0]).", {$m[1]}, {$m[2]});";
}
$out[] = "";

$out[] = "-- ---------- Correlativas ----------";
foreach ($correlativas as $c) {
    $out[] = "INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES ({$c[0]}, {$c[1]});";
}
$out[] = "";

$out[] = "-- ---------- Comisiones (vacantes iniciales = cupo del aula) ----------";
foreach ($comisiones as $id => $c) {
    [$mat, $doc, $aula, $per, $dia, $hi, $hf] = $c;
    $cupo = $aulas[$aula][1];
    $out[] = "INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES ("
           . "$id, $mat, $doc, $aula, $per, ".q($dia).", ".q($hi).", ".q($hf).", $cupo);";
}
$out[] = "";

$out[] = "-- ---------- Inscripciones (alumnos en comisiones de su carrera) ----------";
$fecha = '2025-03-10 09:00:00';
foreach ($inscripciones as $idAlumno => $coms) {
    foreach ($coms as $idCom) {
        $out[] = "INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES ($idAlumno, $idCom, ".q($fecha).", 'ACTIVA');";
    }
}
$out[] = "";

$out[] = "-- Recalcular vacantes según inscriptos activos (coherencia del dato desnormalizado).";
$out[] = "UPDATE Comision c SET c.vacantes_disponibles =";
$out[] = "    (SELECT a.cupo_maximo FROM Aula a WHERE a.id_aula = c.id_aula)";
$out[] = "  - (SELECT COUNT(*) FROM Inscripcion i WHERE i.id_comision = c.id_comision AND i.estado = 'ACTIVA');";
$out[] = "";

$out[] = "-- ---------- Auditoría de inscripciones (espeja las inscripciones) ----------";
foreach ($inscripciones as $idAlumno => $coms) {
    foreach ($coms as $idCom) {
        $out[] = "INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES ($idAlumno, $idCom, '127.0.0.1', ".q($fecha).");";
    }
}
$out[] = "";

$out[] = "-- ---------- Notas (Acta). El trigger tr_auditar_nota llena AuditoriaNota. ----------";
$fnota = '2025-06-20';
foreach ($notas as $x) {
    [$al, $mat, $tipo, $nota] = $x;
    $out[] = "INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES ($al, $mat, ".q($tipo).", ".number_format($nota,2,'.','').", ".q($fnota).");";
}
$out[] = "";

file_put_contents(__DIR__ . '/02_datos.sql', implode("\n", $out) . "\n");
echo "OK: " . (count($out)) . " líneas generadas\n";
