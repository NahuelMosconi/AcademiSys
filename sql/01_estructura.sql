-- ============================================================
--  ACADEMISYS - Estructura de la base de datos
--  Sistema de gestión académica con motor de inscripciones.
--  Tarea 3 (DDL) + Tarea 4 (Procedimientos, Trigger, Índice)
-- ============================================================

DROP DATABASE IF EXISTS academisys;
CREATE DATABASE academisys
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE academisys;

-- Asegura que el cliente interprete el archivo como UTF-8 al importar
-- (evita que los acentos se guarden mal, ej. "GÃ³mez" en vez de "Gómez").
SET NAMES utf8mb4;


-- ============================================================
--  TABLAS MAESTRAS (sin dependencias)
-- ============================================================

-- (1) Rol: tipos de usuario que pueden iniciar sesión
-- Rol: tipos de usuario del sistema (Administrador, Profesor, Alumno).
CREATE TABLE Rol (
    id_rol  INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- (2) Carrera
-- Carrera: las carreras que ofrece la institución. Una carrera tiene muchas materias.
CREATE TABLE Carrera (
    id_carrera     INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(80) NOT NULL UNIQUE,
    duracion_anios INT NOT NULL,
    activo         TINYINT(1) NOT NULL DEFAULT 1     -- baja lógica
) ENGINE=InnoDB;

-- (3) Docente
-- Docente: el cuerpo docente. Se vincula a un Usuario cuando tiene cuenta de acceso.
CREATE TABLE Docente (
    id_docente  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL,
    dni         VARCHAR(15) NOT NULL UNIQUE,          -- no se repite
    email       VARCHAR(100) NOT NULL UNIQUE,
    activo      TINYINT(1) NOT NULL DEFAULT 1         -- baja lógica
) ENGINE=InnoDB;

-- (4) Alumno
-- Alumno: los estudiantes. Cada uno pertenece a una Carrera (id_carrera).
CREATE TABLE Alumno (
    id_alumno   INT AUTO_INCREMENT PRIMARY KEY,
    legajo      VARCHAR(15) NOT NULL UNIQUE,         -- identificador académico, único
    nombre      VARCHAR(80) NOT NULL,
    dni         VARCHAR(15) NOT NULL UNIQUE,          -- no se repite
    telefono    VARCHAR(25) NOT NULL UNIQUE,          -- no se repite
    email       VARCHAR(100) NOT NULL UNIQUE,         -- no se repite
    id_carrera  INT NOT NULL,                         -- carrera que cursa
    activo      TINYINT(1) NOT NULL DEFAULT 1,        -- baja lógica
    CONSTRAINT fk_alumno_carrera
        FOREIGN KEY (id_carrera) REFERENCES Carrera(id_carrera)
) ENGINE=InnoDB;

-- (5) Aula: tiene cupo_maximo (capacidad física) -> Regla 1 de inscripción
-- Aula: espacios físicos. cupo_maximo define cuántos alumnos entran (lo usa el motor).
CREATE TABLE Aula (
    id_aula      INT AUTO_INCREMENT PRIMARY KEY,
    nombre       VARCHAR(30) NOT NULL UNIQUE,
    cupo_maximo  INT NOT NULL                        -- cuántos alumnos entran
) ENGINE=InnoDB;

-- (6) PeriodoLectivo
-- PeriodoLectivo: el cuatrimestre/año en que se dicta una comisión.
CREATE TABLE PeriodoLectivo (
    id_periodo  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(40) NOT NULL UNIQUE,
    anio        INT NOT NULL
) ENGINE=InnoDB;


-- ============================================================
--  TABLAS CON CLAVES FORANEAS
-- ============================================================

-- (7) Usuario: cuenta de acceso. Tiene un Rol.
-- Usuario: cuentas de acceso. Login por DNI. Se vincula a Docente o Alumno según el rol.
CREATE TABLE Usuario (
    id_usuario     INT AUTO_INCREMENT PRIMARY KEY,
    nombre         VARCHAR(80) NOT NULL,
    dni            VARCHAR(15) NOT NULL UNIQUE,       -- se usa para iniciar sesión
    email          VARCHAR(100) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    id_rol         INT NOT NULL,
    id_docente     INT NULL,                          -- vínculo con Docente (NULL si no es profesor)
    id_alumno      INT NULL,                          -- vínculo con Alumno (NULL si no es alumno)
    activo         TINYINT(1) NOT NULL DEFAULT 1,     -- baja lógica
    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (id_rol) REFERENCES Rol(id_rol),
    CONSTRAINT fk_usuario_docente
        FOREIGN KEY (id_docente) REFERENCES Docente(id_docente),
    CONSTRAINT fk_usuario_alumno
        FOREIGN KEY (id_alumno) REFERENCES Alumno(id_alumno)
) ENGINE=InnoDB;

-- (8) Materia: pertenece a una Carrera
-- Materia: las asignaturas. Pertenece a una Carrera. Puede tener correlativas.
CREATE TABLE Materia (
    id_materia  INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(80) NOT NULL,
    anio        INT NOT NULL,
    id_carrera  INT NOT NULL,
    activo      TINYINT(1) NOT NULL DEFAULT 1,       -- baja lógica
    CONSTRAINT fk_materia_carrera
        FOREIGN KEY (id_carrera) REFERENCES Carrera(id_carrera)
) ENGINE=InnoDB;

-- (9) Correlativa: qué materia exige tener aprobada otra materia (previa)
--     Regla 2: el muro de las correlativas.
-- Correlativa: relación materia <- materia previa. El motor la usa para validar.
CREATE TABLE Correlativa (
    id_correlativa  INT AUTO_INCREMENT PRIMARY KEY,
    id_materia      INT NOT NULL,                    -- la materia que se quiere cursar
    id_materia_previa INT NOT NULL,                  -- la que necesita tener aprobada
    CONSTRAINT fk_corr_materia
        FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    CONSTRAINT fk_corr_previa
        FOREIGN KEY (id_materia_previa) REFERENCES Materia(id_materia),
    CONSTRAINT uk_correlativa UNIQUE (id_materia, id_materia_previa)
) ENGINE=InnoDB;

-- (10) Comision: dictado de una materia. Tiene día y horario (Regla 3: solapamiento)
--      y vacantes_disponibles (dato desnormalizado para rendimiento).
-- Comision: el dictado concreto de una materia (junta materia, docente, aula, período y horario).
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

-- (11) Acta: historial académico. Nota final del alumno en una materia.
--      Sirve para validar correlativas (nota >= 4 = aprobada).
-- Acta: historial de notas del alumno. Una nota por tipo (parcial/final) por materia.
CREATE TABLE Acta (
    id_acta     INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno   INT NOT NULL,
    id_materia  INT NOT NULL,
    tipo        VARCHAR(25) NOT NULL,                 -- 1er Parcial, 2do Parcial, Final, Recup 1er Parcial, Recup 2do Parcial
    nota_final  DECIMAL(4,2) NOT NULL,
    fecha       DATE NOT NULL,
    CONSTRAINT fk_acta_alumno  FOREIGN KEY (id_alumno)  REFERENCES Alumno(id_alumno) ON DELETE CASCADE,
    CONSTRAINT fk_acta_materia FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    CONSTRAINT uk_acta UNIQUE (id_alumno, id_materia, tipo)  -- una sola nota de cada tipo por materia
) ENGINE=InnoDB;

-- (12) Inscripcion: alumno inscripto a una comisión.
--      Tiene estado para la baja lógica (ACTIVA / ANULADA).
-- Inscripcion: vincula alumno con comisión (resuelve el muchos-a-muchos). Estado ACTIVA/ANULADA.
CREATE TABLE Inscripcion (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno   INT NOT NULL,
    id_comision INT NOT NULL,
    fecha       DATETIME NOT NULL,
    estado      VARCHAR(10) NOT NULL DEFAULT 'ACTIVA',  -- ACTIVA / ANULADA (baja lógica)
    CONSTRAINT fk_inscripcion_alumno   FOREIGN KEY (id_alumno)   REFERENCES Alumno(id_alumno),
    CONSTRAINT fk_inscripcion_comision FOREIGN KEY (id_comision) REFERENCES Comision(id_comision),
    CONSTRAINT uk_inscripcion UNIQUE (id_alumno, id_comision)
) ENGINE=InnoDB;

-- (13) AuditoriaInscripcion: registra cada inscripción confirmada (IP + fecha).
--      La llena el motor de inscripción dentro de la transacción.
-- AuditoriaInscripcion: registro histórico de inscripciones (sin FK, debe sobrevivir borrados).
CREATE TABLE AuditoriaInscripcion (
    id_auditoria   INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno      INT NOT NULL,
    id_comision    INT NOT NULL,
    ip_origen      VARCHAR(45) NOT NULL,             -- IPv4 o IPv6
    fecha_registro DATETIME NOT NULL
) ENGINE=InnoDB;


-- ============================================================
--  ÍNDICES (Tarea 4) - para acelerar consultas frecuentes
-- ============================================================
-- Búsqueda de actas por alumno (se usa al validar correlativas)
CREATE INDEX idx_acta_alumno ON Acta(id_alumno);
-- Búsqueda de inscripciones por comisión (se usa al contar cupo)
CREATE INDEX idx_insc_comision ON Inscripcion(id_comision);
-- Búsqueda de alumnos por nombre (listados)
CREATE INDEX idx_alumno_nombre ON Alumno(nombre);


-- ============================================================
--  TAREA 4 - PROCEDIMIENTOS ALMACENADOS
-- ============================================================

-- ------------------------------------------------------------
-- SP simple: RegistrarNota (carga una nota final en Acta, valida 0-10)
-- ------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE RegistrarNota (
    IN p_id_alumno  INT,
    IN p_id_materia INT,
    IN p_tipo       VARCHAR(25),
    IN p_nota       DECIMAL(4,2)
)
BEGIN
    -- Validamos el rango de la nota
    IF p_nota < 0 OR p_nota > 10 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La nota debe estar entre 0 y 10';
    END IF;

    -- Insertamos. Si ya existe ese tipo para ese alumno/materia, el UNIQUE
    -- (uk_acta) lanza error y PHP lo traduce a un mensaje amigable.
    INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha)
    VALUES (p_id_alumno, p_id_materia, p_tipo, p_nota, CURDATE());
END //
DELIMITER ;


-- ------------------------------------------------------------
-- SP PRINCIPAL: InscribirAlumno (el "motor de inscripciones")
-- Valida las 3 reglas y hace la transacción ACID.
--   Regla 1: cupo del aula (no superar cupo_maximo)
--   Regla 2: correlativas (nota >= 4 en todas las previas)
--   Regla 3: solapamiento horario (mismo día y hora)
-- Si pasa todo: INSERT en Inscripcion + descuenta vacante +
--               registra auditoría con IP. Todo atómico.
-- ------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE InscribirAlumno (
    IN p_id_alumno   INT,
    IN p_id_comision INT,
    IN p_ip          VARCHAR(45)
)
BEGIN
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

    -- Si algo falla en la transacción, se revierte todo (ACID)
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;   -- vuelve a lanzar el error para que PHP lo capture
    END;

    -- Datos de la comisión a la que se quiere inscribir
    SELECT c.id_materia, c.id_aula, c.dia, c.hora_inicio, c.hora_fin,
           a.cupo_maximo
      INTO v_id_materia, v_id_aula, v_dia, v_hora_inicio, v_hora_fin,
           v_cupo_maximo
    FROM Comision c
    JOIN Aula a ON a.id_aula = c.id_aula
    WHERE c.id_comision = p_id_comision;

    -- ===== REGLA 1: CUPO FÍSICO DEL AULA =====
    SELECT COUNT(*) INTO v_inscriptos
    FROM Inscripcion
    WHERE id_comision = p_id_comision AND estado = 'ACTIVA';

    IF v_inscriptos >= v_cupo_maximo THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No hay cupo: el aula está llena.';
    END IF;

    -- ===== REGLA 2: CORRELATIVAS =====
    -- Contamos cuántas materias previas requeridas NO tiene aprobadas (nota >= 4).
    SELECT COUNT(*) INTO v_faltan
    FROM Correlativa co
    WHERE co.id_materia = v_id_materia
      AND co.id_materia_previa NOT IN (
          SELECT ac.id_materia FROM Acta ac
          WHERE ac.id_alumno = p_id_alumno
            AND ac.tipo = 'Final' AND ac.nota_final >= 4
      );

    IF v_faltan > 0 THEN
        -- Buscamos el nombre de una materia que le falta, para el mensaje
        SELECT m.nombre INTO v_materia_falta
        FROM Correlativa co
        JOIN Materia m ON m.id_materia = co.id_materia_previa
        WHERE co.id_materia = v_id_materia
          AND co.id_materia_previa NOT IN (
              SELECT ac.id_materia FROM Acta ac
              WHERE ac.id_alumno = p_id_alumno
                AND ac.tipo = 'Final' AND ac.nota_final >= 4
          )
        LIMIT 1;

        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = v_materia_falta;   -- PHP arma el mensaje "Te falta: ..."
    END IF;

    -- ===== REGLA 3: SOLAPAMIENTO HORARIO =====
    -- ¿Ya está inscripto (ACTIVA) en otra comisión el mismo día con choque de horario?
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

    -- ===== PASÓ TODOS LOS CONTROLES: TRANSACCIÓN ACID =====
    START TRANSACTION;

        -- 1) Inserción oficial de la inscripción
        INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado)
        VALUES (p_id_alumno, p_id_comision, NOW(), 'ACTIVA');

        -- 2) Descontar una vacante de la comisión (dato desnormalizado)
        UPDATE Comision
        SET vacantes_disponibles = vacantes_disponibles - 1
        WHERE id_comision = p_id_comision;

        -- 3) Registrar la auditoría (con IP y fecha)
        INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro)
        VALUES (p_id_alumno, p_id_comision, p_ip, NOW());

    COMMIT;
END //
DELIMITER ;


-- ------------------------------------------------------------
-- SP: AnularInscripcion (baja lógica: cambia estado, no borra)
-- Devuelve la vacante a la comisión.
-- ------------------------------------------------------------
DELIMITER //
CREATE PROCEDURE AnularInscripcion (
    IN p_id_inscripcion INT
)
BEGIN
    DECLARE v_id_comision INT;
    DECLARE v_estado VARCHAR(10);

    SELECT id_comision, estado INTO v_id_comision, v_estado
    FROM Inscripcion WHERE id_inscripcion = p_id_inscripcion;

    IF v_estado = 'ANULADA' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'La inscripción ya estaba anulada.';
    END IF;

    START TRANSACTION;
        -- Baja lógica: marcamos ANULADA en vez de borrar (conserva el log)
        UPDATE Inscripcion SET estado = 'ANULADA' WHERE id_inscripcion = p_id_inscripcion;
        -- Devolvemos la vacante
        UPDATE Comision SET vacantes_disponibles = vacantes_disponibles + 1
        WHERE id_comision = v_id_comision;
    COMMIT;
END //
DELIMITER ;


-- ============================================================
--  TAREA 4 - TRIGGER
--  Cada nota final cargada en Acta queda auditada.
-- ============================================================
CREATE TABLE AuditoriaNota (
    id_auditoria   INT AUTO_INCREMENT PRIMARY KEY,
    id_alumno      INT NOT NULL,
    id_materia     INT NOT NULL,
    nota           DECIMAL(4,2) NOT NULL,
    fecha_registro DATETIME NOT NULL
) ENGINE=InnoDB;

DELIMITER //
CREATE TRIGGER tr_auditar_nota
AFTER INSERT ON Acta          -- se dispara DESPUÉS de insertar una nota
FOR EACH ROW                  -- una vez por cada fila insertada
BEGIN
    -- NEW.* son los valores de la fila recién insertada en Acta.
    -- Copiamos esos datos a la tabla de auditoría con la fecha actual.
    INSERT INTO AuditoriaNota (id_alumno, id_materia, nota, fecha_registro)
    VALUES (NEW.id_alumno, NEW.id_materia, NEW.nota_final, NOW());
END //
DELIMITER ;


-- ============================================================
--  TRIGGER: validar solapamiento al crear una Comisión
--  Evita dos comisiones en la misma aula al mismo tiempo,
--  o el mismo docente dando dos clases a la vez.
-- ============================================================
DELIMITER //
CREATE TRIGGER tr_validar_comision
BEFORE INSERT ON Comision
FOR EACH ROW
BEGIN
    DECLARE v_choque_aula    INT;
    DECLARE v_choque_docente INT;

    -- ¿Hay otra comisión activa en la misma aula, mismo día, con horario que se pisa?
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

    -- ¿El mismo docente ya da otra clase en ese día y horario?
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


-- ============================================================
--  VISTAS SQL (CREATE VIEW)
--  Una vista es una consulta SELECT guardada con un nombre.
--  Se usa como si fuera una tabla, pero no guarda datos propios:
--  cada vez que la consultás, ejecuta la consulta de adentro
--  y trae los datos frescos de las tablas reales.
-- ============================================================

-- Vista 1: vista_inscripciones
-- Problema que resuelve: para mostrar las inscripciones necesitás
-- unir 4 tablas con JOINs (Inscripcion + Alumno + Comision + Materia).
-- En vez de escribir esos JOINs cada vez, los guardamos acá una sola vez.
-- Después alcanza con: SELECT * FROM vista_inscripciones
CREATE OR REPLACE VIEW vista_inscripciones AS
    SELECT
        i.id_inscripcion,
        i.estado,
        i.fecha,
        al.nombre      AS alumno,       -- nombre real del alumno (de la tabla Alumno)
        al.legajo,
        m.nombre       AS materia,      -- nombre de la materia (de Materia, via Comision)
        d.nombre       AS docente,      -- nombre del docente (de Docente, via Comision)
        a.nombre       AS aula,         -- nombre del aula (de Aula, via Comision)
        c.dia,
        c.hora_inicio,
        c.hora_fin
    FROM Inscripcion i
    JOIN Alumno   al ON al.id_alumno   = i.id_alumno    -- une con Alumno por su id
    JOIN Comision  c ON  c.id_comision = i.id_comision  -- une con Comision por su id
    JOIN Materia   m ON  m.id_materia  = c.id_materia   -- de Comision salta a Materia
    JOIN Docente   d ON  d.id_docente  = c.id_docente   -- de Comision salta a Docente
    JOIN Aula      a ON  a.id_aula     = c.id_aula;     -- de Comision salta a Aula


-- Vista 2: vista_notas_alumnos
-- Problema que resuelve: para mostrar las notas necesitás cruzar
-- Acta + Alumno + Materia. Esta vista lo deja listo para consultarlo
-- directamente filtrando por alumno: WHERE id_alumno = 1
CREATE OR REPLACE VIEW vista_notas_alumnos AS
    SELECT
        ac.id_alumno,                   -- guardamos el id para poder filtrar por alumno
        al.nombre      AS alumno,
        al.legajo,
        m.nombre       AS materia,
        ac.tipo,                        -- '1er Parcial', 'Final', etc.
        ac.nota_final,
        ac.fecha
    FROM Acta ac
    JOIN Alumno  al ON al.id_alumno  = ac.id_alumno   -- trae el nombre del alumno
    JOIN Materia  m ON  m.id_materia = ac.id_materia  -- trae el nombre de la materia
    ORDER BY al.nombre, m.nombre, ac.fecha;


-- Vista 3: vista_comisiones_completas
-- Problema que resuelve: la tabla Comision solo tiene ids (id_materia,
-- id_docente, id_aula). Esta vista los traduce a nombres, mostrando
-- una comisión completa y legible de un vistazo.
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


-- ============================================================
--  DATOS BASE MÍNIMOS
--  No es "carga de datos" de prueba: es lo imprescindible para que
--  el sistema arranque. Sin los roles nadie puede tener un rol, y sin
--  un administrador inicial nadie podría entrar a crear el resto.
--  El alta de docentes y alumnos (desde la app) crea su cuenta sola.
-- ============================================================

-- Los 3 roles del sistema. El orden fija los ids: 1=Administrador, 2=Profesor, 3=Alumno.
INSERT INTO Rol (nombre) VALUES
    ('Administrador'),
    ('Profesor'),
    ('Alumno');

-- Administrador inicial. Login por DNI. Contraseña inicial = DNI (10000000).
-- El password_hash es el resultado de password_hash('10000000', PASSWORD_DEFAULT).
-- Se puede cambiar después desde "Mi perfil".
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol) VALUES
    ('Administrador', '10000000', 'admin@academisys.edu',
     '$2y$12$BEvH4QXhB8RkmRlk0ATBOemu0Ob/npbZ1Ko0x7i/cxiadK3AzFu9a', 1);
