<?php
/**
 * Generador de sql/02_datos.sql — datos de la Universidad Champagnat (UCh).
 *
 * Carga 7 carreras reales con su PLAN DE ESTUDIOS (materias y correlativas)
 * (materias y correlativas) y una demo coherente
 * (docentes, alumnos, comisiones, inscripciones y notas) para poder probar el
 * sistema. Cada docente y alumno viene con su cuenta de acceso (login = DNI).
 *
 * Uso:  php sql/generar_datos.php   (deja sql/02_datos.sql actualizado)
 *
 * Correlativas: lista de codigos de la MISMA carrera; 'TODOS' = todas las
 * materias de codigo anterior (equivale a "todos los espacios curriculares").
 */

function q(string $s): string { return "'" . str_replace("'", "''", $s) . "'"; }
function hash_dni(string $dni): string { return password_hash($dni, PASSWORD_DEFAULT); }
function slug(string $s): string {
    $s = strtr($s, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u',
                    'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n']);
    $s = strtolower(preg_replace('/[^a-zA-Z ]/', '', $s));
    return str_replace(' ', '.', trim(preg_replace('/\s+/', ' ', $s)));
}

// ============================================================================
//  PLANES DE ESTUDIO (carreras con sus materias y correlativas)
//  materia = [codigo, nombre, anio, [codigos_correlativas]]
// ============================================================================
$planes = [
 'Analista Programador Universitario de Sistemas' => ['dur'=>3, 'mats'=>[
   [1,'Algoritmos y Estructuras de Datos I',1,[]],[2,'Programación I',1,[]],
   [3,'Introducción a los Sistemas',1,[]],[4,'Matemática Básica',1,[]],
   [5,'Arquitectura de Computadoras I',1,[]],[6,'Bases de Datos I',1,[]],
   [7,'Programación II',1,[]],[8,'Álgebra I',1,[4]],[9,'Ingeniería de Requisitos',1,[]],
   [10,'Sistemas Operativos I',1,[5]],
   [11,'Programación III',2,[1,2]],[12,'Álgebra II',2,[8]],[13,'Bases de Datos II',2,[6]],
   [14,'Ingeniería de Software I',2,[3,9]],[15,'Programación IV',2,[6,7]],[16,'Cálculo I',2,[4]],
   [17,'Redes I',2,[5]],[18,'Ingeniería de Software II',2,[3,9]],[19,'Sistemas Operativos II',2,[10]],
   [20,'Programación V',3,[11]],[21,'Cálculo II',3,[16]],[22,'Redes II',3,[17]],
   [23,'Algoritmos y Estructuras de Datos II',3,[1,16]],[24,'Ingeniería de Software III',3,[18]],
   [25,'Programación VI',3,[15]],[26,'Teoría de la Computación I',3,[12]],
   [27,'Arquitectura de Computadoras II',3,[17,19]],[28,'Bases de Datos III',3,[13]],
   [29,'Aspectos Profesionales I',3,[]],
 ]],

 'Licenciatura en Sistemas de Información' => ['dur'=>4, 'mats'=>[
   [1,'Algoritmos, Programas y Estructuras de Datos I',1,[]],[2,'Lógica y Estructuras Discretas',1,[]],
   [3,'Álgebra y Geometría Analítica',1,[]],[4,'Introducción a los Sistemas',1,[]],
   [5,'Arquitectura de las Computadoras I',1,[]],[6,'Programación Web',1,[]],
   [7,'Teoría de la Computación I',1,[2]],[8,'Bases de Datos I',1,[]],
   [9,'Ingeniería de Requisitos',1,[]],[10,'Sistemas Operativos I',1,[5]],
   [11,'Algoritmos, Programas y Estructuras de Datos II',2,[1]],[12,'Cálculo I',2,[]],
   [13,'Teoría de la Computación II',2,[7]],[14,'Bases de Datos II',2,[8,10]],
   [15,'Ingeniería de Software I',2,[4,9]],[16,'Programación Orientada a Objetos',2,[8,11]],
   [17,'Cálculo II',2,[12]],[18,'Ingeniería de Software II',2,[15]],[19,'Sistemas Operativos II',2,[10]],
   [20,'Redes I',2,[15]],
   [21,'Laboratorio de Desarrollo de Software',3,[6,16]],[22,'Programación Concurrente',3,[14,16]],
   [23,'Probabilidades y Estadísticas',3,[17]],[24,'Ingeniería de Software III',3,[16,18]],
   [25,'Redes II',3,[20]],[26,'Programación Distribuida',3,[19,22,25]],
   [27,'Fundamentos de Inteligencia Artificial',3,[2]],[28,'Bases de Datos III',3,[14]],
   [29,'Arquitectura de Sistemas',3,[21,24]],[30,'Arquitectura de las Computadoras II',3,[19,20]],
   [31,'Optativa I (Área Algoritmos y Lenguajes)',4,[21]],[32,'Gestión de Proyectos',4,[29]],
   [33,'Calidad de Software',4,[21,24]],[34,'Aspectos Sociales, Empresariales y Profesionales',4,[]],
   [35,'Metodología de la Investigación',4,[23]],[36,'Proyecto de Software (Anual)',4,[26,28,29]],
   [37,'Inteligencia Artificial',4,[23,27]],[38,'Seguridad Aplicada a Sistemas de Información',4,[26,29]],
   [39,'Optativa II (Área Ing. de Software / BD / SI)',4,[29,14]],
   [40,'Aspectos Laborales y Legales del Ejercicio de la Profesión',4,[34]],
   [41,'Práctica Profesional Supervisada',4,[32,33,34]],
 ]],

 'Licenciatura en Diseño Gráfico y Digital' => ['dur'=>4, 'mats'=>[
   [1,'Lenguaje Visual',1,[]],[2,'Introducción a la Tipografía',1,[]],
   [3,'Psicología y Comportamiento del Consumidor',1,[]],[4,'Comunicación, Arte y Tecnología',1,[]],
   [5,'Programas Vectoriales',1,[]],[6,'Dibujo I',1,[]],[7,'Fundamentos Proyectuales',1,[]],
   [8,'Tipografía',1,[2]],[9,'Sistemas de Representación',1,[]],[10,'Programas Pixelares',1,[5]],
   [11,'Dibujo II',1,[6]],[12,'Arte y Diseño Moderno',1,[]],[13,'Proyecto de Identidad',1,[1,2,3,4,5,6,7,9]],
   [14,'Sistemas de Signos',2,[7,11]],[15,'Semiología',2,[]],[16,'Programas de Maquetación',2,[10]],
   [17,'Fotografía',2,[1,10]],[18,'Introducción a la Animación',2,[11]],[19,'Proyecto Editorial',2,[8,13]],
   [20,'Diseño Audiovisual',2,[17]],[21,'Tecnología Gráfica',2,[16]],[22,'Animación',2,[11,18]],
   [23,'Ética y Legislación Profesional',2,[]],[24,'Proyecto Multimedia',2,[14,19]],
   [25,'Práctica Profesionalizante',2,[20,21,22,23,24]],
   [26,'Diseño Regional y Sustentable',3,[25]],[27,'Marketing y Publicidad',3,[]],
   [28,'Creatividad e Innovación',3,[]],[29,'Guión y Realización Audiovisual',3,[20]],
   [30,'Diseño Contemporáneo',3,[12]],[31,'Diseño Web',3,[24]],[32,'Modelos de Negocios',3,[]],
   [33,'Comunicación Profesional',3,[25]],[34,'Narrativas Digitales',3,[29]],
   [35,'Experiencia de Usuario',3,[24]],[36,'Diseño de Información',3,[19]],
   [37,'Proyecto Integrador',3,[25,31,34,35,36]],
   [38,'Diseño y Gestión',4,[26,28]],[39,'Diseño Web y Aplicaciones Móviles',4,[31]],
   [40,'Producción Editorial Digital',4,[33,36]],[41,'Desarrollo Web y Aplicaciones Móviles',4,[37]],
   [42,'Diseño de Libros',4,[37]],[43,'Marketing Digital',4,[27,32]],[44,'Cultura de la Imagen',4,[15,30]],
   [45,'Diseño en Movimiento',4,[37]],[46,'Electiva (Packaging / Multiplataforma / Editorial)',4,[26,38]],
   [47,'Proyecto de Diseño',4,'TODOS'],
 ]],

 'Licenciatura en Comunicación Digital' => ['dur'=>4, 'mats'=>[
   [1,'Plataformas y Ecosistemas Digitales',1,[]],[2,'Publicidad y Marketing Digital',1,[]],
   [3,'Guión',1,[]],[4,'Sociedad, Tecnología y Comunicación',1,[]],
   [5,'Diseño de Experiencia e Interfaz de Usuario',1,[]],[6,'Fotografía y Edición Digital',1,[1]],
   [7,'Redacción Digital',1,[1]],[8,'Análisis de Públicos y Audiencias',1,[2]],
   [9,'Animación Digital',1,[3,5,6]],[10,'Fundamentos de la Comunicación',1,[4]],
   [11,'Medios de Comunicación y Culturas Digitales',2,[10]],[12,'Periodismo de Datos',2,[4]],
   [13,'Modelos de Negocios y Economía de Plataformas',2,[8]],[14,'Edición de Audio y Video',2,[6,9]],
   [15,'Metodología de la Investigación Periodística',2,[8,12]],
   [16,'Planificación de Comunicación Digital',2,[2,7]],[17,'Realización Audiovisual',2,[14]],
   [18,'Periodismo Digital',2,[7,15]],[19,'Teorías Críticas de la Comunicación',2,[10]],
   [20,'Diseño y Desarrollo Web',2,[5,9]],[21,'Práctica Profesionalizante',2,[17,18,19,20]],
   [22,'Comunicación Estratégica y Gestión de Crisis',3,[16,21]],[23,'Storytelling',3,[21]],
   [24,'Gamificación',3,[23]],[25,'Regulación y Legislación de la Comunicación Digital',3,[13,16]],
   [26,'Narrativa Transmedia',3,[23,24]],[27,'Estética Audiovisual',3,[11,26]],
   [28,'Economía y Políticas Públicas',3,[13,21]],[29,'Televisión Digital',3,[21,22]],
   [30,'Procesos Creativos',3,[21,25]],
   [31,'Economía del Conocimiento',4,[25,28]],[32,'Administración de Proyectos',4,[19,26,30]],
   [33,'Gestión de Recursos Humanos',4,[30,32]],[34,'Comunicación Política',4,[22]],
   [35,'Producción Transmedia',4,[21,26]],[36,'Análisis y Estadística',4,[21,32]],
   [37,'Práctica Profesional Situada',4,'TODOS'],
 ]],

 'Licenciatura en Administración de Empresas' => ['dur'=>4, 'mats'=>[
   [1,'Introducción a la Contabilidad',1,[]],[2,'Matemática General',1,[]],
   [3,'Procesamiento de Datos',1,[]],[5,'Inglés I',1,[]],[6,'Derecho Civil',1,[]],
   [7,'Microeconomía',1,[]],[8,'Administración General',1,[]],
   [9,'Normas Contables de Valuación',2,[1]],[10,'Estadística Metodológica e Inferencial',2,[2]],
   [11,'Tecnologías de Gestión',2,[8]],[12,'Inglés II',2,[5]],[13,'Macroeconomía',2,[7]],
   [14,'Sistemas de Información y Control',2,[1,3,8]],[15,'Derecho Público',2,[6]],
   [16,'Matemática Financiera',2,[2,10]],
   [17,'Creatividad e Iniciativa',3,[]],[18,'Planificación Estratégica',3,[11]],
   [19,'Costos',3,[9,14]],[20,'Derecho Comercial I',3,[15]],
   [21,'Comportamiento Organizacional',3,[11,18]],[22,'Calidad',3,[17]],
   [23,'Exposición y Análisis de los Estados Contables',3,[9]],[24,'Derecho Laboral',3,[15]],
   [25,'Práctica Profesional I',3,[]],
   [26,'Comercialización General',4,[17,18,21,22]],[27,'Administración de Operaciones',4,[17,18,21,22]],
   [28,'Administración Financiera',4,[17,18,21,22,23]],[29,'Administración de los Recursos Humanos',4,[17,18,21,22]],
   [30,'Formulación y Evaluación de Proyectos',4,'TODOS'],[31,'Módulo Electivo I',4,[]],
   [32,'Módulo Electivo II',4,[]],[33,'Práctica Profesional II',4,'TODOS'],
 ]],

 'Contador Público' => ['dur'=>4, 'mats'=>[
   [1,'Introducción a la Contabilidad',1,[]],[2,'Taller de Comprensión Lectora y Producción de Textos',1,[]],
   [3,'Introducción a la Economía',1,[]],[4,'Matemática I',1,[]],[5,'Procesamiento de Datos',1,[]],
   [6,'Microeconomía',1,[3]],[7,'Administración General',1,[]],[8,'Matemática II',1,[4]],
   [9,'Contabilidad Básica',1,[1]],[10,'Práctica Contable I',1,[1]],
   [11,'Normas Contables I',2,[9]],[12,'Estadística I',2,[8]],[13,'Tecnologías de Gestión',2,[7]],
   [14,'Derecho Civil I',2,[2]],[15,'Macroeconomía',2,[6]],[16,'Normas Contables II',2,[11]],
   [17,'Estadística II',2,[12]],[18,'Matemática Financiera',2,[8]],[19,'Sistemas de Información y Control',2,[5,7,9]],
   [20,'Derecho Público',2,[14]],[21,'Práctica Contable II',2,[5,9,10]],
   [22,'Contabilidad Superior',3,[16,17]],[23,'Técnica Impositiva I',3,[2,6,20]],[24,'Finanzas Públicas',3,[15,20]],
   [25,'Derecho Comercial I',3,[2]],[26,'Historia Económica y Social',3,[2,15]],[27,'Contabilidad Pública',3,[24]],
   [28,'Técnica Impositiva II',3,[23]],[29,'Administración Financiera',3,[7,18]],[30,'Derecho Comercial II',3,[25]],
   [31,'Práctica Contable III',3,[16,21,23]],
   [32,'Auditoría',4,[19,22,28]],[33,'Costos',4,[13,16,22]],[34,'Derecho Civil II',4,[14]],
   [35,'Derecho Laboral',4,[25]],[36,'Práctica Jurídica',4,[14,20,30]],[37,'Ética Profesional',4,[27,32]],
   [38,'Auditoría y Procesamiento Electrónico de Datos',4,[32]],[39,'Metodología de la Investigación',4,[17]],
   [40,'Derecho Comercial III',4,[30]],[41,'Prácticas Profesionales Supervisadas',4,[26,28,29,31,33,34,35,36]],
 ]],

 'Abogacía y Procuración' => ['dur'=>4, 'mats'=>[
   [1,'Derecho Privado Parte General',1,[]],[2,'Principios Generales del Derecho',1,[]],
   [3,'Filosofía y Ética',1,[]],[4,'Historia de las Instituciones Argentinas',1,[]],
   [5,'Derecho Constitucional',1,[]],[6,'Teoría del Estado',1,[]],[7,'Derecho Penal Parte General',1,[]],
   [8,'Obligaciones Civiles y Comerciales I',1,[1]],[9,'Práctica Profesional I',1,[]],
   [10,'Derecho Penal Parte Especial',2,[7]],[11,'Contratos Parte General',2,[8]],
   [12,'Derecho Procesal Parte General',2,[]],[13,'Obligaciones Civiles y Comerciales II',2,[8]],
   [14,'Lógica y Argumentación',2,[]],[15,'Derechos Humanos',2,[5]],[16,'Derecho Procesal Penal',2,[7,12]],
   [17,'Contratos Parte Especial',2,[11,13]],[18,'Derecho Procesal Civil y Comercial',2,[12]],
   [19,'Práctica Profesional II',2,[9]],
   [20,'Resolución Alternativa de Conflictos',3,[]],[21,'Derechos Reales I',3,[17]],
   [22,'Derecho de las Familias',3,[17]],[23,'Sociedades Civiles y Comerciales',3,[17]],
   [24,'Metodología de la Investigación',3,[]],[25,'Derechos Reales II',3,[21]],
   [26,'Derecho Sucesorio',3,[22]],[27,'Papeles de Comercio',3,[17]],[28,'Concursos y Quiebras',3,[18]],
   [29,'Derecho Laboral',3,[17]],[30,'Práctica Profesional III',3,[19]],
   [31,'Economía',4,[]],[32,'Derecho Administrativo',4,[17]],
   [33,'Derecho Internacional Público y de la Integración',4,[15]],[34,'Derecho Privado Profundizado',4,[26]],
   [35,'Derecho Financiero y Tributario',4,[17,32]],[36,'Derecho Procesal Constitucional',4,[5,18]],
   [37,'Derecho Ambiental y Aguas',4,[25]],[38,'Derecho Internacional Privado',4,[25,26]],
   [39,'Práctica Profesional IV',4,[30]],[40,'Optativa I (Notarial / Minero y Agrario)',4,[25]],
   [41,'Optativa II (Registral / Transporte y Seguros)',4,[25]],
 ]],
];

// ============================================================================
//  DEMO (docentes, alumnos, comisiones, inscripciones, notas)
// ============================================================================
$aulas = [[1,'Aula 101',40],[2,'Aula 102',40],[3,'Aula 103',35],
          [4,'Laboratorio A',30],[5,'Laboratorio B',30],[6,'Aula Magna',60]];
$periodos = [[1,'1er Cuatrimestre 2025',2025],[2,'2do Cuatrimestre 2025',2025]];
// Pools de nombres/apellidos para generar muchas personas variadas y funcionales.
$nombresPool = ['Rodrigo','Camila','Lucas','Valentina','Mateo','Julieta','Tomás','Florencia','Nicolás',
  'Agustina','Franco','Martina','Bruno','Carla','Lautaro','Sofía','Benjamín','Delfina','Thiago','Catalina',
  'Joaquín','Emma','Bautista','Renata','Santiago','Isabella','Facundo','Guadalupe','Ignacio','Victoria',
  'Gonzalo','Abril','Ramiro','Josefina','Enzo','Malena','Dylan','Lucía','Álvaro','Paz','Máximo','Olivia',
  'Simón','Amparo','Gael','Zoe','Ciro','Pilar','Elías','Mora'];
$apellidosPool = ['Gómez','Pérez','Torres','Fernández','Ramírez','López','Díaz','Martínez','Ruiz','Sosa',
  'Ortiz','Castro','Romero','Silva','Núñez','Molina','Ríos','Herrera','Aguirre','Vega','Medina','Rojas',
  'Domínguez','Suárez','Flores','Acosta','Benítez','Cabrera','Ledesma','Ferreyra','Godoy','Ponce','Vera',
  'Bustos','Correa','Peralta','Quiroga','Miranda','Cardozo','Luna','Ibáñez','Navarro','Campos','Ávila',
  'Cáceres','Maldonado','Figueroa','Villalba','Ojeda','Pereyra'];
// Genera un nombre completo ÚNICO por cada índice global $k (0,1,2,...).
// El apellido depende de $k y también de la "vuelta" (cuántas veces se dio la
// vuelta al pool de nombres), así el par (nombre, apellido) nunca se repite
// mientras $k < count(nombres) * count(apellidos). Esto evita que un docente y
// un alumno terminen con el mismo nombre.
function nombreDe(int $k, array $n, array $a): string {
    $first = $n[$k % count($n)];
    $last  = $a[($k * 3 + intdiv($k, count($n))) % count($a)];
    return "$first $last";
}
$NUM_DOCENTES = 20;          // profesores
$ALUMNOS_POR_CARRERA = 7;    // 7 x 7 carreras = 49 alumnos
$dias = ['Lunes','Martes','Miércoles','Jueves','Viernes'];
$slots = [['08:00','10:00'],['10:00','12:00'],['14:00','16:00'],['16:00','18:00'],['18:00','20:00'],['20:00','22:00']];

// ---- construir ids globales de materias: idg[$carreraIdx][$codigo] = id ----
$out = [];
$out[] = "-- ============================================================";
$out[] = "--  ACADEMISYS - Datos de ejemplo";
$out[] = "--  Ejecutar DESPUÉS de sql/01_estructura.sql.";
$out[] = "--  7 carreras con su PLAN DE ESTUDIOS (materias + correlativas)";
$out[] = "--  y una demo (docentes, alumnos, comisiones, notas) para probar.";
$out[] = "--  Generado por sql/generar_datos.php (hashes bcrypt reales del DNI).";
$out[] = "-- ============================================================";
$out[] = "";
$out[] = "USE academisys;";
$out[] = "";
$out[] = "SET NAMES utf8mb4;";
$out[] = "";
$out[] = "SET @rol_prof = (SELECT id_rol FROM Rol WHERE nombre = 'Profesor');";
$out[] = "SET @rol_alu  = (SELECT id_rol FROM Rol WHERE nombre = 'Alumno');";
$out[] = "";

// Carreras
$out[] = "-- ---------- Carreras ----------";
$carreraIdx = 0; $carreraIds = [];
foreach ($planes as $nombre => $p) {
    $carreraIdx++;
    $carreraIds[$nombre] = $carreraIdx;
    $out[] = "INSERT INTO Carrera (id_carrera, nombre, duracion_anios) VALUES ($carreraIdx, ".q($nombre).", {$p['dur']});";
}
$out[] = "";

// Aulas / periodos
$out[] = "-- ---------- Aulas ----------";
foreach ($aulas as $a) $out[] = "INSERT INTO Aula (id_aula, nombre, cupo_maximo) VALUES ({$a[0]}, ".q($a[1]).", {$a[2]});";
$out[] = "";
// Ciclos lectivos: el 2025 (ABIERTO) y los cuatrimestres ya vienen de
// sql/01_estructura.sql. Acá agregamos dos ciclos ANTERIORES (cerrados) y
// resolvemos los ids por año/nombre en variables de sesión.
$out[] = "-- ---------- Ciclos lectivos anteriores (cerrados) ----------";
$out[] = "INSERT INTO CicloLectivo (anio, estado, fecha_apertura, fecha_cierre) VALUES";
$out[] = "    (2023, 'CERRADO', '2023-03-13', '2023-12-15'),";
$out[] = "    (2024, 'CERRADO', '2024-03-11', '2024-12-13');";
$out[] = "SET @per1  = (SELECT id_periodo FROM PeriodoLectivo WHERE nombre = '1er Cuatrimestre');";
$out[] = "SET @c2023 = (SELECT id_ciclo FROM CicloLectivo WHERE anio = 2023);";
$out[] = "SET @c2024 = (SELECT id_ciclo FROM CicloLectivo WHERE anio = 2024);";
$out[] = "SET @c2025 = (SELECT id_ciclo FROM CicloLectivo WHERE anio = 2025);";
$out[] = "";

// Materias (ids globales) + mapa codigo->id por carrera
$out[] = "-- ---------- Materias (plan de estudios de cada carrera) ----------";
$idg = [];         // idg[carreraId][codigo] = idGlobal
$codesByCarrera = []; // para 'TODOS'
$mid = 0;
$ci = 0;
foreach ($planes as $nombre => $p) {
    $ci++; $cid = $carreraIds[$nombre];
    $out[] = "-- $nombre";
    foreach ($p['mats'] as $m) {
        $mid++;
        $idg[$cid][$m[0]] = $mid;
        $codesByCarrera[$cid][] = $m[0];
        $out[] = "INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES ($mid, ".q($m[1]).", {$m[2]}, $cid);";
    }
}
$out[] = "";

// Correlativas
$out[] = "-- ---------- Correlativas (por carrera) ----------";
$ci = 0;
foreach ($planes as $nombre => $p) {
    $ci++; $cid = $carreraIds[$nombre];
    foreach ($p['mats'] as $m) {
        $corr = $m[3];
        if ($corr === 'TODOS') {
            $corr = array_filter($codesByCarrera[$cid], fn($c) => $c < $m[0]);
        }
        foreach ($corr as $prevCod) {
            if (!isset($idg[$cid][$prevCod])) continue;   // ignora codigos inexistentes
            $idMat = $idg[$cid][$m[0]]; $idPrev = $idg[$cid][$prevCod];
            $out[] = "INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES ($idMat, $idPrev);";
        }
    }
}
$out[] = "";

// Docentes + usuarios
$out[] = "-- ---------- Docentes + cuenta de acceso (rol Profesor) ----------";
$docIds = [];
for ($i = 0; $i < $NUM_DOCENTES; $i++) {
    $id = $i + 1; $docIds[] = $id;
    $nombre = nombreDe($i, $nombresPool, $apellidosPool);
    $dni = (string)(20000000 + $id);
    $email = slug($nombre).$id.'@academisys.edu';
    $out[] = "INSERT INTO Docente (id_docente, nombre, dni, email) VALUES ($id, ".q($nombre).", ".q($dni).", ".q($email).");";
    $out[] = "INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ("
           . q($nombre).", ".q($dni).", ".q($email).", ".q(hash_dni($dni)).", @rol_prof, $id);";
}
$out[] = "";

// Alumnos (varios por carrera) + usuarios
$out[] = "-- ---------- Alumnos + cuenta de acceso (rol Alumno) ----------";
$aluId = 0; $alumnosPorCarrera = [];
$an = 0;
foreach ($planes as $nombre => $p) {
    $cid = $carreraIds[$nombre];
    for ($k = 0; $k < $ALUMNOS_POR_CARRERA; $k++) {
        $nombreAl = nombreDe($an + $NUM_DOCENTES, $nombresPool, $apellidosPool); $an++;
        $aluId++;
        $dni = (string)(38000000 + $aluId);
        $legajo = sprintf('A2025%03d', $aluId);
        $email = slug($nombreAl).$aluId.'@alumnos.academisys.edu';
        $tel = sprintf('261-400-%04d', $aluId);
        $out[] = "INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES ("
               . "$aluId, ".q($legajo).", ".q($nombreAl).", ".q($dni).", ".q($tel).", ".q($email).", $cid);";
        $out[] = "INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ("
               . q($nombreAl).", ".q($dni).", ".q($email).", ".q(hash_dni($dni)).", @rol_alu, $aluId);";
        $alumnosPorCarrera[$cid][] = $aluId;
    }
}
$out[] = "";

// ----------------------------------------------------------------------------
//  Comisiones por CICLO + cohortes de alumnos en distintas etapas.
//  Ciclos: 2025 (abierto), 2024 y 2023 (cerrados). Por carrera se abren
//  comisiones para ciertos años en cada ciclo:
//     2025 -> 1°, 2° y 3° año   |   2024 -> 1° y 2°   |   2023 -> 1°
//  Cohortes (de 7 por carrera): 3 ingresantes (cursan 1° en 2025),
//  2 intermedios (hicieron 1° en 2024, cursan 2° en 2025) y 2 avanzados
//  (1° en 2023, 2° en 2024, cursan 3° en 2025).
//  Los choques se validan POR CICLO, así que cada ciclo se agenda por separado.
// ----------------------------------------------------------------------------
$pares = [];
foreach ($dias as $d) foreach ($slots as $s) $pares[] = [$d, $s];   // 30 pares (día,horario)
$NMAT = [1=>3, 2=>2, 3=>2];                                          // comisiones por año
$aniosPorCiclo = ['@c2025'=>[1,2,3], '@c2024'=>[1,2], '@c2023'=>[1]];
function matsAnio(array $mats, int $anio, int $n): array {
    return array_slice(array_values(array_filter($mats, fn($m)=>$m[2]===$anio)), 0, $n);
}

$out[] = "-- ---------- Comisiones (por ciclo lectivo y año) ----------";
$comIdx = 0; $comMateria = []; $com = [];   // com[cid][cicloVar][anio] = [idComision,...]
foreach ($aniosPorCiclo as $cicloVar => $anios) {
    $sched = 0;   // contador de agenda POR CICLO (los choques se validan por ciclo)
    foreach ($planes as $nombre => $p) {
        $cid = $carreraIds[$nombre];
        foreach ($anios as $anio) {
            foreach (matsAnio($p['mats'], $anio, $NMAT[$anio]) as $m) {
                $pairIdx = $sched % count($pares);
                $occ     = intdiv($sched, count($pares));
                [$slotDia, $slotHora] = $pares[$pairIdx];
                $aula = $aulas[$occ % count($aulas)];
                $doc  = $docIds[($pairIdx * 3 + $occ) % count($docIds)];
                $sched++; $comIdx++;
                $idMat = $idg[$cid][$m[0]];
                $out[] = "INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, id_ciclo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES ("
                       . "$comIdx, $idMat, $doc, {$aula[0]}, @per1, $cicloVar, ".q($slotDia).", ".q($slotHora[0]).", ".q($slotHora[1]).", {$aula[2]});";
                $com[$cid][$cicloVar][$anio][] = $comIdx;
                $comMateria[$comIdx] = $idMat;
            }
        }
    }
}
$out[] = "";

// Inscripciones por cohorte (en el año/ciclo que corresponde a su etapa).
$out[] = "-- ---------- Inscripciones (cada cohorte en su etapa) ----------";
$inscList = [];
function inscribir(&$out,&$inscList,$al,$coms,$fecha){
    foreach ($coms as $c){ $out[]="INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES ($al, $c, '".$fecha."', 'ACTIVA');"; $inscList[]=[$al,$c,$fecha]; }
}
$cohortes = [];
foreach ($alumnosPorCarrera as $cid => $alus) {
    $ing = array_slice($alus,0,3); $int = array_slice($alus,3,2); $avz = array_slice($alus,5,2);
    $cohortes[$cid] = ['ing'=>$ing,'int'=>$int,'avz'=>$avz];
    $c25 = $com[$cid]['@c2025'] ?? []; $c24 = $com[$cid]['@c2024'] ?? []; $c23 = $com[$cid]['@c2023'] ?? [];
    foreach ($ing as $al) inscribir($out,$inscList,$al, $c25[1] ?? [], '2025-03-10 09:00:00');
    foreach ($int as $al){ inscribir($out,$inscList,$al, $c24[1] ?? [], '2024-03-11 09:00:00');
                           inscribir($out,$inscList,$al, $c25[2] ?? [], '2025-03-10 09:00:00'); }
    foreach ($avz as $al){ inscribir($out,$inscList,$al, $c23[1] ?? [], '2023-03-13 09:00:00');
                           inscribir($out,$inscList,$al, $c24[2] ?? [], '2024-03-11 09:00:00');
                           inscribir($out,$inscList,$al, $c25[3] ?? [], '2025-03-10 09:00:00'); }
}
$out[] = "";
$out[] = "-- Recalcular vacantes segun inscriptos activos.";
$out[] = "UPDATE Comision c SET c.vacantes_disponibles =";
$out[] = "    (SELECT a.cupo_maximo FROM Aula a WHERE a.id_aula = c.id_aula)";
$out[] = "  - (SELECT COUNT(*) FROM Inscripcion i WHERE i.id_comision = c.id_comision AND i.estado='ACTIVA');";
$out[] = "";
$out[] = "-- ---------- Auditoría de inscripciones (espeja las inscripciones) ----------";
foreach ($inscList as $x)
    $out[] = "INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES ({$x[0]}, {$x[1]}, '127.0.0.1', '".$x[2]."');";
$out[] = "";

// Notas: la historia que ubica a cada alumno en su etapa.
$out[] = "-- ---------- Notas (Acta): historial que marca la etapa de cada alumno ----------";
function acta(&$out,$al,$mat,$tipo,$nota,$f){ $out[]="INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES ($al, $mat, ".q($tipo).", ".number_format($nota,2,'.','').", ".q($f).");"; }
function matsDe($coms,$comMateria){ return array_map(fn($c)=>$comMateria[$c], $coms); }
foreach ($cohortes as $cid => $co) {
    $c25 = $com[$cid]['@c2025'] ?? []; $c24 = $com[$cid]['@c2024'] ?? []; $c23 = $com[$cid]['@c2023'] ?? [];
    // Intermedios: aprobaron (Final) las materias de 1° cursadas en 2024.
    foreach ($co['int'] as $al)
        foreach (matsDe($c24[1] ?? [], $comMateria) as $mat) acta($out,$al,$mat,'Final',7.00,'2024-11-20');
    // Avanzados: aprobaron 1° (2023) y 2° (2024).
    foreach ($co['avz'] as $al){
        foreach (matsDe($c23[1] ?? [], $comMateria) as $mat) acta($out,$al,$mat,'Final',8.00,'2023-11-22');
        foreach (matsDe($c24[2] ?? [], $comMateria) as $mat) acta($out,$al,$mat,'Final',7.00,'2024-11-20');
    }
    // Ingresantes: notas del ciclo actual (2025) en su 1ra materia de 1°, variadas.
    $m1 = isset($c25[1][0]) ? $comMateria[$c25[1][0]] : null;
    if ($m1 !== null) {
        if (isset($co['ing'][0])) { acta($out,$co['ing'][0],$m1,'1er Parcial',8.00,'2025-06-20'); acta($out,$co['ing'][0],$m1,'2do Parcial',9.00,'2025-06-25'); }
        if (isset($co['ing'][1])) { acta($out,$co['ing'][1],$m1,'1er Parcial',5.00,'2025-06-20'); acta($out,$co['ing'][1],$m1,'2do Parcial',6.00,'2025-06-25'); }
        if (isset($co['ing'][2])) { acta($out,$co['ing'][2],$m1,'1er Parcial',3.00,'2025-06-20'); }
    }
}
$out[] = "";

file_put_contents(__DIR__ . '/02_datos.sql', implode("\n", $out) . "\n");
echo "OK: ".count($out)." lineas. Carreras: ".count($planes).", materias: $mid, comisiones: $comIdx\n";
