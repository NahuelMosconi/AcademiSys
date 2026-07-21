-- ############################################################################
-- #                                                                          #
-- #   ACADEMISYS - ESTRUCTURA DE LA BASE DE DATOS (script principal)         #
-- #   Sistema de gestion academica con motor de inscripciones.               #
-- #                                                                          #
-- #   Contiene: base + tablas (DDL), indices, procedimientos almacenados,    #
-- #   triggers, vistas SQL y datos base minimos (roles + admin inicial).     #
-- #                                                                          #
-- ############################################################################
--
-- --------------------------------------------------------------------------
-- MAPA RAPIDO (buscar con Ctrl+F el texto en MAYUSCULAS entre corchetes):
--
--   [TABLAS]                             -> definicion de las 14 tablas
--   [INDICE: idx_acta_alumno]            -> Acta(id_alumno)
--   [INDICE: idx_insc_comision]          -> Inscripcion(id_comision)
--   [INDICE: idx_alumno_nombre]          -> Alumno(nombre)
--   [FUNCION: fn_estado_materia]         -> estado UCh (Aprobada/Promocionada/Regular/Libre)
--   [PROCEDIMIENTO: RegistrarNota]       -> carga una nota (Acta)
--   [PROCEDIMIENTO: InscribirAlumno]     -> MOTOR de inscripcion (3 reglas+ACID)
--   [PROCEDIMIENTO: AnularInscripcion]   -> baja logica de inscripcion
--   [TRIGGER: tr_auditar_nota]           -> audita cada nota cargada
--   [TRIGGER: tr_validar_comision]       -> evita choques de aula/docente
--   [VISTA: vista_inscripciones]         -> inscripciones legibles (JOINs)
--   [VISTA: vista_notas_alumnos]         -> notas legibles (JOINs)
--   [VISTA: vista_comisiones_completas]  -> comisiones legibles (JOINs)
--   [DATOS BASE]                         -> roles + administrador inicial
--
-- DONDE SE USA CADA OBJETO DESDE EL PHP (resumen; detalle en cada seccion):
--   RegistrarNota .............. clases/Acta.php  -> registrar()      (vistas/notas.php)
--   InscribirAlumno ............ clases/Inscripcion.php -> inscribir() (vistas/inscripciones.php)
--   AnularInscripcion .......... clases/Inscripcion.php -> anular()    (vistas/inscripciones.php)
--   tr_auditar_nota ............ se dispara solo al cargar nota; se ve en vistas/auditoria.php
--   tr_validar_comision ........ se dispara al crear comision (clases/Comision.php -> crear())
--   vista_inscripciones ........ clases/Inscripcion.php -> listar()   (vistas/inscripciones.php)
--   vista_notas_alumnos ........ clases/Acta.php -> listar()/listarPorAlumno() (vistas/notas.php, vistas/mis_notas.php)
--   vista_comisiones_completas . clases/Comision.php -> listar()      (vistas/comisiones.php)
-- --------------------------------------------------------------------------


DROP DATABASE IF EXISTS academisys;
CREATE DATABASE academisys
    CHARACTER SET utf8mb4          -- soporta acentos y emojis (1 a 4 bytes)
    COLLATE utf8mb4_unicode_ci;    -- comparacion sin distinguir mayus/acentos

USE academisys;

-- Asegura que el cliente interprete el archivo como UTF-8 al importar
-- (evita que los acentos se guarden mal, ej. "GÃ³mez" en vez de "Gómez").
SET NAMES utf8mb4;


-- ############################################################################
-- ##  [TABLAS]  -  DDL: definicion de las 14 tablas                          ##
-- ############################################################################
-- Se crean primero las tablas "maestras" (sin dependencias) y luego las que
-- tienen claves foraneas (FK) hacia ellas. Casi todas tienen una columna
-- "activo" para la BAJA LOGICA (se marca inactivo en vez de borrar).

-- ============================================================
--  TABLAS MAESTRAS (sin dependencias)
-- ============================================================

-- (1) Rol: tipos de usuario que pueden iniciar sesion (Administrador, Profesor, Alumno).
CREATE TABLE Rol (
    id_rol  INT AUTO_INCREMENT PRIMARY KEY,   -- clave primaria autoincremental
    nombre  VARCHAR(30) NOT NULL UNIQUE       -- no se repite el nombre de rol
) ENGINE=InnoDB;

-- (2) Carrera: las carreras que ofrece la institucion. Una carrera tiene muchas materias.
CREATE TABLE Carrera (
    id_carrera     INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(80) NOT NULL UNIQUE,
    duracion_anios INT NOT NULL,
    activo         TINYINT(1) NOT NULL DEFAULT 1     -- baja logica (1=activo, 0=baja)
) ENGINE=InnoDB;

-- (3) Docente: el cuerpo docente. Se vincula a un Usuario cuando tiene cuenta de acceso.
CREATE TABLE Docente (
    id_docente  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL,
    dni         VARCHAR(15) NOT NULL UNIQUE,          -- no se repite
    email       VARCHAR(100) NOT NULL UNIQUE,
    activo      TINYINT(1) NOT NULL DEFAULT 1         -- baja logica
) ENGINE=InnoDB;

-- (4) Alumno: los estudiantes. Cada uno pertenece a una Carrera (id_carrera).
CREATE TABLE Alumno (
    id_alumno   INT AUTO_INCREMENT PRIMARY KEY,
    legajo      VARCHAR(15) NOT NULL UNIQUE,          -- identificador academico, unico
    nombre      VARCHAR(80) NOT NULL,
    dni         VARCHAR(15) NOT NULL UNIQUE,          -- no se repite
    telefono    VARCHAR(25) NOT NULL UNIQUE,          -- no se repite
    email       VARCHAR(100) NOT NULL UNIQUE,         -- no se repite
    id_carrera  INT NOT NULL,                         -- carrera que cursa (FK)
    activo      TINYINT(1) NOT NULL DEFAULT 1,        -- baja logica
    CONSTRAINT fk_alumno_carrera
        FOREIGN KEY (id_carrera) REFERENCES Carrera(id_carrera)
) ENGINE=InnoDB;

-- (5) Aula: espacios fisicos. cupo_maximo define cuantos alumnos entran
--     (lo usa el motor de inscripcion como tope -> Regla 1).
CREATE TABLE Aula (
    id_aula      INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(30) NOT NULL UNIQUE,
    cupo_maximo  INT NOT NULL                        -- cuantos alumnos entran
) ENGINE=InnoDB;

-- (6) PeriodoLectivo: el cuatrimestre/anio en que se dicta una comision.
CREATE TABLE PeriodoLectivo (
    id_periodo  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(40) NOT NULL UNIQUE,
    anio        INT NOT NULL
) ENGINE=InnoDB;


-- ============================================================
--  TABLAS CON CLAVES FORANEAS
-- ============================================================

-- (7) Usuario: cuentas de acceso. Login por DNI. Se vincula a Docente o Alumno segun el rol.
--     id_docente / id_alumno son NULL segun corresponda (un admin no tiene ninguno).
CREATE TABLE Usuario (
    id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(80) NOT NULL,
    dni            VARCHAR(15) NOT NULL UNIQUE,       -- se usa para iniciar sesion
    email          VARCHAR(100) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,             -- hash bcrypt (nunca la clave en claro)
    id_rol         INT NOT NULL,                      -- FK a Rol
    id_docente     INT NULL,                          -- FK a Docente (NULL si no es profesor)
    id_alumno      INT NULL,                          -- FK a Alumno  (NULL si no es alumno)
    activo         TINYINT(1) NOT NULL DEFAULT 1,     -- baja logica (login exige activo=1)
    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (id_rol) REFERENCES Rol(id_rol),
    CONSTRAINT fk_usuario_docente
        FOREIGN KEY (id_docente) REFERENCES Docente(id_docente),
    CONSTRAINT fk_usuario_alumno
        FOREIGN KEY (id_alumno) REFERENCES Alumno(id_alumno)
) ENGINE=InnoDB;

-- (8) Materia: las asignaturas. Pertenece a una Carrera. Puede tener correlativas.
CREATE TABLE Materia (
    id_materia  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL,
    anio        INT NOT NULL,                         -- anio de la carrera en que va
    id_carrera  INT NOT NULL,                         -- FK a Carrera
    activo      TINYINT(1) NOT NULL DEFAULT 1,        -- baja logica
    CONSTRAINT fk_materia_carrera
        FOREIGN KEY (id_carrera) REFERENCES Carrera(id_carrera)
) ENGINE=InnoDB;

-- (9) Correlativa: relacion materia <- materia previa (la que hay que tener aprobada).
--     El motor de inscripcion (InscribirAlumno) la consulta para validar la Regla 2.
CREATE TABLE Correlativa (
    id_correlativa    INT AUTO_INCREMENT PRIMARY KEY,
    id_materia        INT NOT NULL,                   -- la materia que se quiere cursar
    id_materia_previa INT NOT NULL,                   -- la que necesita tener aprobada
    CONSTRAINT fk_corr_materia
        FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    CONSTRAINT fk_corr_previa
        FOREIGN KEY (id_materia_previa) REFERENCES Materia(id_materia),
    CONSTRAINT uk_correlativa UNIQUE (id_materia, id_materia_previa)  -- no duplicar la relacion
) ENGINE=InnoDB;

-- (10) Comision: el dictado concreto de una materia (junta materia, docente, aula,
--      periodo y horario). Tiene dia/horario (Regla 3: solapamiento) y
--      vacantes_disponibles (dato desnormalizado para rendimiento).
CREATE TABLE Comision (
    id_comision INT AUTO_INCREMENT PRIMARY KEY,
    id_materia  INT NOT NULL,
    id_docente  INT NOT NULL,
    id_aula     INT NOT NULL,
    id_periodo  INT NOT NULL,
    dia         VARCHAR(10) NOT NULL,                -- Lunes, Martes...
    hora_inicio TIME NOT NULL,
    hora_fin    TIME NOT NULL,
    vacantes_disponibles INT NOT NULL,               -- se descuenta al inscribir
    activo      TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_comision_materia FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    CONSTRAINT fk_comision_docente FOREIGN KEY (id_docente) REFERENCES Docente(id_docente),
    CONSTRAINT fk_comision_aula    FOREIGN KEY (id_aula)    REFERENCES Aula(id_aula),
    CONSTRAINT fk_comision_periodo FOREIGN KEY (id_periodo) REFERENCES PeriodoLectivo(id_periodo)
) ENGINE=InnoDB;

-- (11) Acta: historial de notas del alumno. Una nota por tipo (parcial/final) por materia.
--      Sirve para validar correlativas (Final con nota >= 4 = materia aprobada).
CREATE TABLE Acta (
    id_acta     INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno   INT NOT NULL,
    id_materia  INT NOT NULL,
    tipo        VARCHAR(25) NOT NULL,                 -- 1er Parcial, 2do Parcial, Final, Recup...
    nota_final  DECIMAL(4,2) NOT NULL,
    fecha       DATE NOT NULL,
    CONSTRAINT fk_acta_alumno  FOREIGN KEY (id_alumno)  REFERENCES Alumno(id_alumno) ON DELETE CASCADE,
    CONSTRAINT fk_acta_materia FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    CONSTRAINT uk_acta UNIQUE (id_alumno, id_materia, tipo)  -- una sola nota de cada tipo por materia
) ENGINE=InnoDB;

-- (12) Inscripcion: vincula alumno con comision (resuelve el muchos-a-muchos).
--      Estado ACTIVA/ANULADA para la baja logica.
CREATE TABLE Inscripcion (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno   INT NOT NULL,
    id_comision INT NOT NULL,
    fecha       DATETIME NOT NULL,
    estado      VARCHAR(10) NOT NULL DEFAULT 'ACTIVA',  -- ACTIVA / ANULADA (baja logica)
    CONSTRAINT fk_inscripcion_alumno   FOREIGN KEY (id_alumno)   REFERENCES Alumno(id_alumno),
    CONSTRAINT fk_inscripcion_comision FOREIGN KEY (id_comision) REFERENCES Comision(id_comision),
    CONSTRAINT uk_inscripcion UNIQUE (id_alumno, id_comision)  -- no inscribir 2 veces al mismo
) ENGINE=InnoDB;

-- (13) AuditoriaInscripcion: registro historico de inscripciones (con IP + fecha).
--      La llena el MOTOR (InscribirAlumno) dentro de la transaccion. Sin FK a
--      proposito: debe sobrevivir aunque despues se borren otros datos.
CREATE TABLE AuditoriaInscripcion (
    id_auditoria   INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno      INT NOT NULL,
    id_comision    INT NOT NULL,
    ip_origen      VARCHAR(45) NOT NULL,             -- IPv4 o IPv6
    fecha_registro DATETIME NOT NULL
) ENGINE=InnoDB;

-- (14) AuditoriaNota: se crea mas abajo, junto al trigger que la llena
--      (ver [TRIGGER: tr_auditar_nota]).


-- ############################################################################
-- ##  [INDICES]  (Tarea 4) - aceleran las consultas mas frecuentes          ##
-- ############################################################################
-- Un indice es como el indice de un libro: evita recorrer toda la tabla.
-- Se crea sobre columnas por las que se busca/ordena seguido.

-- ---- [INDICE: idx_acta_alumno] ----
-- Sobre Acta(id_alumno). Acelera "traer las actas de un alumno".
-- Lo aprovechan:  el MOTOR al validar correlativas (busca Finales del alumno)
--                 y  clases/Acta.php -> listarPorAlumno()  (vistas/mis_notas.php).
CREATE INDEX idx_acta_alumno ON Acta(id_alumno);

-- ---- [INDICE: idx_insc_comision] ----
-- Sobre Inscripcion(id_comision). Acelera "contar inscriptos de una comision".
-- Lo aprovecha: el MOTOR al chequear el cupo del aula (Regla 1, COUNT por comision).
CREATE INDEX idx_insc_comision ON Inscripcion(id_comision);

-- ---- [INDICE: idx_alumno_nombre] ----
-- Sobre Alumno(nombre). Acelera los listados ordenados/buscados por nombre.
-- Lo aprovecha: clases/Alumno.php -> listar()  (vistas/alumnos.php).
CREATE INDEX idx_alumno_nombre ON Alumno(nombre);


-- ############################################################################
-- ##  [FUNCION: fn_estado_materia]   (ESTADO DE MATERIA - regimen UCh)      ##
-- ############################################################################
-- Una funcion almacenada devuelve UN valor. Esta calcula, para un alumno y una
-- materia, el estado segun el regimen de la Universidad Champagnat:
--   'Aprobada'     -> tiene Final >= 4 (o promociono).
--   'Promocionada' -> ambos parciales (o sus recuperatorios) >= 4, Trabajos
--                     Practicos >= 4 y PROMEDIO de parciales >= 7 (sin final).
--   'Regular'      -> ambos parciales >= 4 y TP >= 4 (pero promedio < 7): puede
--                     rendir final. (La regularidad en UCh dura 3 anios.)
--   'Libre'        -> no cumple lo anterior.
-- La usan: el motor de inscripcion (correlativas) y clases/Acta.php (mostrar estado).
DELIMITER //
CREATE FUNCTION fn_estado_materia(p_id_alumno INT, p_id_materia INT)
    RETURNS VARCHAR(20)
    DETERMINISTIC
    READS SQL DATA
    COMMENT 'Estado UCh de una materia para un alumno: Aprobada/Promocionada/Regular/Libre'
BEGIN
    DECLARE v_p1    DECIMAL(4,2);   -- mejor nota del 1er parcial (o su recuperatorio)
    DECLARE v_p2    DECIMAL(4,2);   -- mejor nota del 2do parcial (o su recuperatorio)
    DECLARE v_tp    DECIMAL(4,2);   -- nota de Trabajos Practicos
    DECLARE v_final DECIMAL(4,2);   -- mejor nota de Final

    SELECT MAX(nota_final) INTO v_p1 FROM Acta
     WHERE id_alumno=p_id_alumno AND id_materia=p_id_materia
       AND tipo IN ('1er Parcial','Recup 1er Parcial');
    SELECT MAX(nota_final) INTO v_p2 FROM Acta
     WHERE id_alumno=p_id_alumno AND id_materia=p_id_materia
       AND tipo IN ('2do Parcial','Recup 2do Parcial');
    SELECT MAX(nota_final) INTO v_tp FROM Acta
     WHERE id_alumno=p_id_alumno AND id_materia=p_id_materia
       AND tipo='Trabajos Prácticos';
    SELECT MAX(nota_final) INTO v_final FROM Acta
     WHERE id_alumno=p_id_alumno AND id_materia=p_id_materia AND tipo='Final';

    -- Aprobada por examen final.
    IF v_final IS NOT NULL AND v_final >= 4 THEN
        RETURN 'Aprobada';
    END IF;

    -- Cursada aprobada (regular): dos parciales >= 4 y TP >= 4.
    IF v_p1 >= 4 AND v_p2 >= 4 AND v_tp >= 4 THEN
        IF (v_p1 + v_p2) / 2 >= 7 THEN
            RETURN 'Promocionada';   -- promedio alto: no rinde final
        ELSE
            RETURN 'Regular';        -- regular: debe rendir final
        END IF;
    END IF;

    RETURN 'Libre';
END //
DELIMITER ;


-- ############################################################################
-- ##  [PROCEDIMIENTOS ALMACENADOS]  (Tarea 4)                               ##
-- ############################################################################
-- Un procedimiento almacenado es codigo SQL guardado en la base con un nombre,
-- que se ejecuta con CALL. Centraliza la logica y la vuelve atomica/segura.


-- ==========================================================================
--  [PROCEDIMIENTO: RegistrarNota]   (REGISTRAR NOTA)
--  Que hace:  inserta una nota en Acta, validando que este entre 0 y 10.
--  Parametros: alumno, materia, tipo de nota y la nota.
--  Se llama desde:  clases/Acta.php -> registrar()   (pantalla vistas/notas.php)
--  Si el tipo ya existe para ese alumno/materia, el UNIQUE uk_acta lo rechaza
--  y PHP lo traduce a un mensaje amigable.
-- ==========================================================================
DELIMITER //
CREATE PROCEDURE RegistrarNota (
    IN p_id_alumno  INT,          -- alumno al que se le carga la nota
    IN p_id_materia INT,          -- materia de la nota
    IN p_tipo       VARCHAR(25),  -- '1er Parcial', 'Final', etc.
    IN p_nota       DECIMAL(4,2)  -- valor de la nota (0 a 10)
)
COMMENT 'Carga una nota en Acta validando 0-10. Se llama desde clases/Acta.php::registrar()'
BEGIN
    -- Validamos el rango de la nota; si esta fuera de 0-10 lanzamos error controlado.
    IF p_nota < 0 OR p_nota > 10 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La nota debe estar entre 0 y 10';
    END IF;

    -- Insertamos. Si ya existe ese tipo para ese alumno/materia, el UNIQUE
    -- (uk_acta) lanza error y PHP lo traduce a un mensaje amigable.
    -- Este INSERT ademas dispara el trigger tr_auditar_nota (ver mas abajo).
    INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha)
    VALUES (p_id_alumno, p_id_materia, p_tipo, p_nota, CURDATE());
END //
DELIMITER ;


-- ==========================================================================
--  [PROCEDIMIENTO: InscribirAlumno]   (INSCRIBIR ALUMNO) -- EL "MOTOR"
--  Que hace:  inscribe un alumno a una comision validando 3 reglas y, si pasan
--             todas, hace la operacion como TRANSACCION ACID (todo o nada).
--    Regla 1: CUPO del aula     (no superar cupo_maximo)
--    Regla 2: CORRELATIVAS      (tener Final >= 4 en todas las materias previas)
--    Regla 3: SOLAPAMIENTO      (no chocar con otra materia el mismo dia/horario)
--  Si pasa todo:  INSERT en Inscripcion + descuenta vacante + registra auditoria (IP).
--  Se llama desde:  clases/Inscripcion.php -> inscribir()  (pantalla vistas/inscripciones.php)
-- ==========================================================================
DELIMITER //
CREATE PROCEDURE InscribirAlumno (
    IN p_id_alumno   INT,          -- alumno a inscribir
    IN p_id_comision INT,          -- comision destino
    IN p_ip          VARCHAR(45)   -- IP de origen (para la auditoria)
)
COMMENT 'MOTOR de inscripcion: valida cupo, correlativas y solapamiento; transaccion ACID + auditoria. Se llama desde clases/Inscripcion.php::inscribir()'
BEGIN
    -- Variables de trabajo (datos de la comision y contadores).
    DECLARE v_id_materia    INT;
    DECLARE v_id_aula       INT;
    DECLARE v_cupo_maximo   INT;
    DECLARE v_inscriptos    INT;
    DECLARE v_dia           VARCHAR(10);
    DECLARE v_hora_inicio   TIME;
    DECLARE v_hora_fin      TIME;
    DECLARE v_faltan        INT;
    DECLARE v_solapadas     INT;
    DECLARE v_materia_falta VARCHAR(80);

    -- MANEJADOR DE ERRORES: si algo falla dentro de la transaccion, se revierte
    -- todo (ROLLBACK) y se relanza el error (RESIGNAL) para que PHP lo capture.
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    -- Traemos los datos de la comision destino (materia, aula, dia, horario y su cupo).
    SELECT c.id_materia, c.id_aula, c.dia, c.hora_inicio, c.hora_fin,
           a.cupo_maximo
      INTO v_id_materia, v_id_aula, v_dia, v_hora_inicio, v_hora_fin,
           v_cupo_maximo
    FROM Comision c
    JOIN Aula a ON a.id_aula = c.id_aula
    WHERE c.id_comision = p_id_comision;

    -- ===== REGLA 1: CUPO FISICO DEL AULA =====
    -- Contamos inscriptos ACTIVOS; si ya se lleno, cortamos.
    SELECT COUNT(*) INTO v_inscriptos
    FROM Inscripcion
    WHERE id_comision = p_id_comision AND estado = 'ACTIVA';

    IF v_inscriptos >= v_cupo_maximo THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No hay cupo: el aula está llena.';
    END IF;

    -- ===== REGLA 2: CORRELATIVAS (regimen UCh) =====
    -- Para CURSAR una materia hay que tener la correlativa REGULARIZADA, es decir
    -- en estado Regular, Promocionada o Aprobada (lo calcula fn_estado_materia).
    -- Contamos cuantas previas requeridas NO estan regularizadas.
    SELECT COUNT(*) INTO v_faltan
    FROM Correlativa co
    WHERE co.id_materia = v_id_materia
      AND fn_estado_materia(p_id_alumno, co.id_materia_previa) COLLATE utf8mb4_unicode_ci
          NOT IN ('Regular','Promocionada','Aprobada');

    IF v_faltan > 0 THEN
        -- Buscamos el nombre de UNA materia que le falta, para el mensaje de error.
        SELECT m.nombre INTO v_materia_falta
        FROM Correlativa co
        JOIN Materia m ON m.id_materia = co.id_materia_previa
        WHERE co.id_materia = v_id_materia
          AND fn_estado_materia(p_id_alumno, co.id_materia_previa) COLLATE utf8mb4_unicode_ci
              NOT IN ('Regular','Promocionada','Aprobada')
        LIMIT 1;

        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_materia_falta;   -- PHP arma el texto "Te falta: ..."
    END IF;

    -- ===== REGLA 3: SOLAPAMIENTO HORARIO =====
    -- Choque de horario = mismo dia y los intervalos se pisan.
    -- Condicion clasica de solape: inicioA < finB AND finA > inicioB.
    SELECT COUNT(*) INTO v_solapadas
    FROM Inscripcion i
    JOIN Comision c ON c.id_comision = i.id_comision
    WHERE i.id_alumno = p_id_alumno
      AND i.estado = 'ACTIVA'
      AND c.dia = v_dia
      AND c.hora_inicio < v_hora_fin
      AND c.hora_fin > v_hora_inicio;

    IF v_solapadas > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Tenés otra materia en ese día y horario.';
    END IF;

    -- ===== PASO TODOS LOS CONTROLES: TRANSACCION ACID =====
    -- Los 3 pasos siguientes ocurren como una unidad: o se hacen todos, o ninguno.
    START TRANSACTION;

        -- 1) Inscripcion oficial.
        INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado)
        VALUES (p_id_alumno, p_id_comision, NOW(), 'ACTIVA');

        -- 2) Descontar una vacante (dato desnormalizado de la comision).
        UPDATE Comision
        SET vacantes_disponibles = vacantes_disponibles - 1
        WHERE id_comision = p_id_comision;

        -- 3) Registrar la auditoria (con IP y fecha) -> tabla AuditoriaInscripcion.
        INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro)
        VALUES (p_id_alumno, p_id_comision, p_ip, NOW());

    COMMIT;
END //
DELIMITER ;


-- ==========================================================================
--  [PROCEDIMIENTO: AnularInscripcion]   (ANULAR INSCRIPCION)
--  Que hace:  BAJA LOGICA de una inscripcion (marca 'ANULADA', no borra) y
--             devuelve la vacante a la comision. Todo en una transaccion.
--  Se llama desde:  clases/Inscripcion.php -> anular()  (pantalla vistas/inscripciones.php)
-- ==========================================================================
DELIMITER //
CREATE PROCEDURE AnularInscripcion (
    IN p_id_inscripcion INT       -- inscripcion a anular
)
COMMENT 'Baja logica de inscripcion y devuelve la vacante. Se llama desde clases/Inscripcion.php::anular()'
BEGIN
    DECLARE v_id_comision INT;
    DECLARE v_estado VARCHAR(10);

    -- Traemos comision y estado actual de la inscripcion.
    SELECT id_comision, estado INTO v_id_comision, v_estado
    FROM Inscripcion WHERE id_inscripcion = p_id_inscripcion;

    -- Si ya estaba anulada, avisamos (evita devolver una vacante de mas).
    IF v_estado = 'ANULADA' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La inscripción ya estaba anulada.';
    END IF;

    START TRANSACTION;
        -- Baja logica: marcamos ANULADA en vez de borrar (conserva el historial).
        UPDATE Inscripcion SET estado = 'ANULADA' WHERE id_inscripcion = p_id_inscripcion;
        -- Devolvemos la vacante a la comision.
        UPDATE Comision SET vacantes_disponibles = vacantes_disponibles + 1
        WHERE id_comision = v_id_comision;
    COMMIT;
END //
DELIMITER ;


-- ############################################################################
-- ##  [TRIGGERS]  (Tarea 4)                                                 ##
-- ############################################################################
-- Un trigger (disparador) es codigo que la base ejecuta AUTOMATICAMENTE cuando
-- ocurre un evento (INSERT/UPDATE/DELETE) sobre una tabla. Nadie lo llama a mano.

-- Tabla que llena el trigger de auditoria de notas (ver el trigger debajo).
CREATE TABLE AuditoriaNota (
    id_auditoria   INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno      INT NOT NULL,
    id_materia     INT NOT NULL,
    nota           DECIMAL(4,2) NOT NULL,
    fecha_registro DATETIME NOT NULL
) ENGINE=InnoDB;

-- ==========================================================================
--  [TRIGGER: tr_auditar_nota]   (AUDITAR NOTA)
--  Cuando:  DESPUES de insertar una fila en Acta (AFTER INSERT).
--  Que hace: copia esa nota a AuditoriaNota con la fecha/hora actual.
--  Se dispara solo: al cargar una nota (RegistrarNota, desde clases/Acta.php).
--  Se ve el resultado en:  vistas/auditoria.php  (leido por clases/AuditoriaNota.php).
--  NEW.* = valores de la fila recien insertada en Acta.
-- ==========================================================================
DELIMITER //
CREATE TRIGGER tr_auditar_nota
AFTER INSERT ON Acta          -- se dispara DESPUES de insertar una nota
FOR EACH ROW                  -- una vez por cada fila insertada
BEGIN
    INSERT INTO AuditoriaNota (id_alumno, id_materia, nota, fecha_registro)
    VALUES (NEW.id_alumno, NEW.id_materia, NEW.nota_final, NOW());
END //
DELIMITER ;

-- ==========================================================================
--  [TRIGGER: tr_validar_comision]   (VALIDAR COMISION)
--  Cuando:  ANTES de insertar una fila en Comision (BEFORE INSERT).
--  Que hace: valida la comision antes de crearla. Corta (SIGNAL) si:
--     - la hora de INICIO no es anterior a la de FIN (ej. 19:30-19:00), o
--     - la MISMA AULA ya esta ocupada ese dia/horario, o
--     - el MISMO DOCENTE ya da otra clase ese dia/horario.
--  Se dispara solo:  al crear una comision (clases/Comision.php -> crear(),
--                     pantalla vistas/comisiones.php). PHP captura el error.
--  NEW.* = valores de la comision que se intenta insertar.
-- ==========================================================================
DELIMITER //
CREATE TRIGGER tr_validar_comision
BEFORE INSERT ON Comision
FOR EACH ROW
BEGIN
    DECLARE v_choque_aula    INT;
    DECLARE v_choque_docente INT;

    -- Coherencia horaria: la hora de inicio debe ser anterior a la de fin
    -- (evita comisiones tipo 19:30-19:00).
    IF NEW.hora_inicio >= NEW.hora_fin THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La hora de inicio debe ser anterior a la hora de fin.';
    END IF;

    -- Choque de AULA: misma aula, mismo dia, horarios que se pisan.
    SELECT COUNT(*) INTO v_choque_aula
    FROM Comision
    WHERE activo = 1
      AND id_aula = NEW.id_aula
      AND dia = NEW.dia
      AND hora_inicio < NEW.hora_fin
      AND hora_fin > NEW.hora_inicio;

    IF v_choque_aula > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El aula ya está ocupada ese día y horario.';
    END IF;

    -- Choque de DOCENTE: mismo docente, mismo dia, horarios que se pisan.
    SELECT COUNT(*) INTO v_choque_docente
    FROM Comision
    WHERE activo = 1
      AND id_docente = NEW.id_docente
      AND dia = NEW.dia
      AND hora_inicio < NEW.hora_fin
      AND hora_fin > NEW.hora_inicio;

    IF v_choque_docente > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'El docente ya tiene otra clase ese día y horario.';
    END IF;
END //
DELIMITER ;


-- ############################################################################
-- ##  [VISTAS SQL]  (CREATE VIEW)                                           ##
-- ############################################################################
-- Una vista es una consulta SELECT guardada con un nombre. Se usa como si
-- fuera una tabla, pero NO guarda datos propios: cada vez que se consulta,
-- ejecuta su SELECT y trae datos frescos de las tablas reales.
--
-- IMPORTANTE (para la defensa): al crear una vista, MySQL REESCRIBE el SELECT
-- en su forma canonica y ELIMINA estos comentarios. Por eso, si mirás la vista
-- en phpMyAdmin la vas a ver sin comentarios: la explicacion vive aca, en el
-- archivo. (En procedimientos, la explicacion breve va en la clausula COMMENT.)

-- ==========================================================================
--  [VISTA: vista_inscripciones]   (INSCRIPCIONES LEGIBLES)
--  Problema que resuelve: mostrar inscripciones exige unir 5 tablas
--     (Inscripcion + Alumno + Comision + Materia + Docente + Aula).
--  Esta vista guarda esos JOINs una sola vez; el PHP hace SELECT * FROM ella.
--  La usa:  clases/Inscripcion.php -> listar()   (pantalla vistas/inscripciones.php)
-- ==========================================================================
CREATE OR REPLACE VIEW vista_inscripciones AS
    SELECT
        i.id_inscripcion,
        i.estado,
        i.fecha,
        al.nombre      AS alumno,       -- nombre real del alumno
        al.legajo,
        m.nombre       AS materia,      -- nombre de la materia (via Comision)
        d.nombre       AS docente,      -- nombre del docente (via Comision)
        a.nombre       AS aula,         -- nombre del aula (via Comision)
        c.dia,
        c.hora_inicio,
        c.hora_fin
    FROM Inscripcion i
    JOIN Alumno   al ON al.id_alumno   = i.id_alumno
    JOIN Comision  c ON  c.id_comision = i.id_comision
    JOIN Materia   m ON  m.id_materia  = c.id_materia
    JOIN Docente   d ON  d.id_docente  = c.id_docente
    JOIN Aula      a ON  a.id_aula     = c.id_aula;

-- ==========================================================================
--  [VISTA: vista_notas_alumnos]   (NOTAS LEGIBLES)
--  Problema que resuelve: mostrar notas exige cruzar Acta + Alumno + Materia.
--  Incluye id_alumno para poder filtrar por alumno (WHERE id_alumno = X).
--  La usa:  clases/Acta.php -> listar()          (pantalla vistas/notas.php)
--           clases/Acta.php -> listarPorAlumno()  (pantalla vistas/mis_notas.php)
-- ==========================================================================
CREATE OR REPLACE VIEW vista_notas_alumnos AS
    SELECT
        ac.id_alumno,                   -- se guarda para poder filtrar por alumno
        al.nombre      AS alumno,
        al.legajo,
        m.nombre       AS materia,
        ac.tipo,                        -- '1er Parcial', 'Final', etc.
        ac.nota_final,
        ac.fecha
    FROM Acta ac
    JOIN Alumno  al ON al.id_alumno  = ac.id_alumno
    JOIN Materia  m ON  m.id_materia = ac.id_materia
    ORDER BY al.nombre, m.nombre, ac.fecha;

-- ==========================================================================
--  [VISTA: vista_comisiones_completas]   (COMISIONES LEGIBLES)
--  Problema que resuelve: la tabla Comision solo tiene ids. Esta vista los
--  traduce a nombres (materia, docente, aula, periodo) y ya filtra activo=1.
--  La usa:  clases/Comision.php -> listar()   (pantalla vistas/comisiones.php)
-- ==========================================================================
CREATE OR REPLACE VIEW vista_comisiones_completas AS
    SELECT
        c.id_comision,
        m.nombre       AS materia,
        d.nombre       AS docente,
        a.nombre       AS aula,
        a.cupo_maximo,
        c.vacantes_disponibles,
        c.dia,
        c.hora_inicio,
        c.hora_fin,
        p.nombre       AS periodo
    FROM Comision c
    JOIN Materia        m ON m.id_materia = c.id_materia
    JOIN Docente        d ON d.id_docente = c.id_docente
    JOIN Aula           a ON a.id_aula    = c.id_aula
    JOIN PeriodoLectivo p ON p.id_periodo = c.id_periodo
    WHERE c.activo = 1;               -- solo comisiones activas


-- ############################################################################
-- ##  [DATOS BASE]  -  minimos para que el sistema arranque                 ##
-- ############################################################################
-- No es "carga de datos" de prueba: es lo imprescindible para poder entrar.
-- Sin los roles nadie puede tener un rol, y sin un administrador inicial
-- nadie podria entrar a crear el resto. El alta de docentes y alumnos (desde
-- la app) crea su cuenta sola. Datos de ejemplo opcionales: sql/02_datos.sql.

-- Los 3 roles. El orden fija los ids: 1=Administrador, 2=Profesor, 3=Alumno.
INSERT INTO Rol (nombre) VALUES
    ('Administrador'),
    ('Profesor'),
    ('Alumno');

-- Administrador inicial. Login por DNI. Contraseña inicial = DNI (10000000).
-- El password_hash es el resultado de password_hash('10000000', PASSWORD_DEFAULT)
-- en PHP (bcrypt). Se puede cambiar despues desde "Mi perfil".
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol) VALUES
    ('Administrador', '10000000', 'admin@academisys.edu',
     '$2y$12$BEvH4QXhB8RkmRlk0ATBOemu0Ob/npbZ1Ko0x7i/cxiadK3AzFu9a', 1);
