-- ============================================================
--  ACADEMISYS - Datos de ejemplo FUNCIONALES
--  Ejecutar DESPUÉS de sql/01_estructura.sql (que crea el esquema,
--  los roles y el administrador inicial).
--
--  Todos los docentes y alumnos vienen CON su cuenta de acceso:
--  login por DNI y contraseña inicial = DNI. No hay usuarios vacíos.
--  (Este archivo lo genera sql/generar_datos.php; los password_hash
--   son bcrypt reales del propio DNI de cada uno.)
-- ============================================================

USE academisys;

-- Ids de rol resueltos por nombre (no dependemos de ids fijos).
SET @rol_prof = (SELECT id_rol FROM Rol WHERE nombre = 'Profesor');
SET @rol_alu  = (SELECT id_rol FROM Rol WHERE nombre = 'Alumno');

-- ---------- Carreras ----------
INSERT INTO Carrera (id_carrera, nombre, duracion_anios) VALUES (1, 'Tecnicatura en Programación', 3);
INSERT INTO Carrera (id_carrera, nombre, duracion_anios) VALUES (2, 'Tecnicatura en Análisis de Sistemas', 3);
INSERT INTO Carrera (id_carrera, nombre, duracion_anios) VALUES (3, 'Tecnicatura en Redes', 2);

-- ---------- Aulas ----------
INSERT INTO Aula (id_aula, nombre, cupo_maximo) VALUES (1, 'Aula 101', 30);
INSERT INTO Aula (id_aula, nombre, cupo_maximo) VALUES (2, 'Aula 102', 25);
INSERT INTO Aula (id_aula, nombre, cupo_maximo) VALUES (3, 'Laboratorio A', 20);
INSERT INTO Aula (id_aula, nombre, cupo_maximo) VALUES (4, 'Laboratorio B', 20);

-- ---------- Períodos lectivos ----------
INSERT INTO PeriodoLectivo (id_periodo, nombre, anio) VALUES (1, '1er Cuatrimestre 2025', 2025);
INSERT INTO PeriodoLectivo (id_periodo, nombre, anio) VALUES (2, '2do Cuatrimestre 2025', 2025);

-- ---------- Docentes + su cuenta de acceso (rol Profesor) ----------
INSERT INTO Docente (id_docente, nombre, dni, email) VALUES (1, 'Laura Gómez', '20000001', 'laura.gomez@academisys.edu');
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ('Laura Gómez', '20000001', 'laura.gomez@academisys.edu', '$2y$12$9V.UAuIKg3haN0K8bpOBzuB1zhLa/4ybBouJ0pL1ODJ1/8/.uERvW', @rol_prof, 1);
INSERT INTO Docente (id_docente, nombre, dni, email) VALUES (2, 'Martín Pérez', '20000002', 'martin.perez@academisys.edu');
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ('Martín Pérez', '20000002', 'martin.perez@academisys.edu', '$2y$12$nsnIpts5cydqQJhQb/zbp.GOasj.qrPpIIo58rdMgQ6XRv4fijkbS', @rol_prof, 2);
INSERT INTO Docente (id_docente, nombre, dni, email) VALUES (3, 'Ana Torres', '20000003', 'ana.torres@academisys.edu');
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ('Ana Torres', '20000003', 'ana.torres@academisys.edu', '$2y$12$EMgpofP7d/wvRN7EYgoQ..w8NDy5bu.7JUOcZM96tjmyLgXuj/peu', @rol_prof, 3);
INSERT INTO Docente (id_docente, nombre, dni, email) VALUES (4, 'Diego Fernández', '20000004', 'diego.fernandez@academisys.edu');
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ('Diego Fernández', '20000004', 'diego.fernandez@academisys.edu', '$2y$12$Y0JhTaL12cuAWRGdPmbtxOkrOsHCy1d44d9eSzIkVBn1nbnUgDJh.', @rol_prof, 4);
INSERT INTO Docente (id_docente, nombre, dni, email) VALUES (5, 'Sofía Ramírez', '20000005', 'sofia.ramirez@academisys.edu');
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ('Sofía Ramírez', '20000005', 'sofia.ramirez@academisys.edu', '$2y$12$WvwECqsG6rDrnh7x4kN9weBakksZ856edfzCbpIFmDa5Znza4olxC', @rol_prof, 5);
INSERT INTO Docente (id_docente, nombre, dni, email) VALUES (6, 'Javier López', '20000006', 'javier.lopez@academisys.edu');
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente) VALUES ('Javier López', '20000006', 'javier.lopez@academisys.edu', '$2y$12$ZR6MvYhB0rbULBHRnvMM7.7BLaml6xq1/q8DhxdgTi46uAHPggIPW', @rol_prof, 6);

-- ---------- Alumnos + su cuenta de acceso (rol Alumno) ----------
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (1, 'A2025001', 'Rodrigo Ramírez', '38000001', '11-4000-0001', 'rodrigo.ramirez@alumnos.academisys.edu', 1);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Rodrigo Ramírez', '38000001', 'rodrigo.ramirez@alumnos.academisys.edu', '$2y$12$zycNi3BcL7Y5IrMGC34OoO0SJ5ZNQTFrBJsc1WkVGXe/7.rGAuIRa', @rol_alu, 1);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (2, 'A2025002', 'Camila Suárez', '38000002', '11-4000-0002', 'camila.suarez@alumnos.academisys.edu', 1);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Camila Suárez', '38000002', 'camila.suarez@alumnos.academisys.edu', '$2y$12$BpFTexxh6DwSg9Zdx8F/QOI069xcf7o0TF23vt8t1c3r30IZxlE.G', @rol_alu, 2);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (3, 'A2025003', 'Lucas Molina', '38000003', '11-4000-0003', 'lucas.molina@alumnos.academisys.edu', 1);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Lucas Molina', '38000003', 'lucas.molina@alumnos.academisys.edu', '$2y$12$HHWaFWIEj2HRN4zv3dMG1OR5LVJ.E/za2plVIcKEoR9FbF61W8ASe', @rol_alu, 3);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (4, 'A2025004', 'Valentina Ríos', '38000004', '11-4000-0004', 'valentina.rios@alumnos.academisys.edu', 1);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Valentina Ríos', '38000004', 'valentina.rios@alumnos.academisys.edu', '$2y$12$UjfD.7W1l6K/Ciab.2TrKeuLJxTGhNLewG8./QPKh/LEFGGlMZFZu', @rol_alu, 4);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (5, 'A2025005', 'Mateo Castro', '38000005', '11-4000-0005', 'mateo.castro@alumnos.academisys.edu', 1);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Mateo Castro', '38000005', 'mateo.castro@alumnos.academisys.edu', '$2y$12$.EKN6tuzPMuquJJ4FAzpgO.9CTbSukIaJUQfvpU2Y9xWog9CRjzfW', @rol_alu, 5);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (6, 'A2025006', 'Julieta Herrera', '38000006', '11-4000-0006', 'julieta.herrera@alumnos.academisys.edu', 1);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Julieta Herrera', '38000006', 'julieta.herrera@alumnos.academisys.edu', '$2y$12$VXf/ZgwmjjrjuSeFLBtdj.JFUVF2KH9JjeGw4rvWejC/RlXnWDltO', @rol_alu, 6);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (7, 'A2025007', 'Tomás Aguirre', '38000007', '11-4000-0007', 'tomas.aguirre@alumnos.academisys.edu', 1);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Tomás Aguirre', '38000007', 'tomas.aguirre@alumnos.academisys.edu', '$2y$12$uCsd9skmutIA6A6FQUQz5.Z2C6Wq3fuB4vbFlfpMONx/o3SJX77VS', @rol_alu, 7);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (8, 'A2025008', 'Florencia Vega', '38000008', '11-4000-0008', 'florencia.vega@alumnos.academisys.edu', 2);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Florencia Vega', '38000008', 'florencia.vega@alumnos.academisys.edu', '$2y$12$e4bDXL7CVyMKOYRNCZyixezj6gHR3dAc3YXyTcudaxqWNoa.VWpce', @rol_alu, 8);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (9, 'A2025009', 'Nicolás Medina', '38000009', '11-4000-0009', 'nicolas.medina@alumnos.academisys.edu', 2);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Nicolás Medina', '38000009', 'nicolas.medina@alumnos.academisys.edu', '$2y$12$2XY/gQ/1CbkbyalUt/ZTWu/1Hm27pben61wA0TZ.i.7wjosq78ZFq', @rol_alu, 9);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (10, 'A2025010', 'Agustina Rojas', '38000010', '11-4000-0010', 'agustina.rojas@alumnos.academisys.edu', 2);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Agustina Rojas', '38000010', 'agustina.rojas@alumnos.academisys.edu', '$2y$12$amcG.RuJzhEXoAeVAm4LnOqTOAQ7hs/I.g5UInF0HHtoJ/gxKlzRq', @rol_alu, 10);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (11, 'A2025011', 'Franco Domínguez', '38000011', '11-4000-0011', 'franco.dominguez@alumnos.academisys.edu', 2);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Franco Domínguez', '38000011', 'franco.dominguez@alumnos.academisys.edu', '$2y$12$DEhvLJL39cEiiZF8CMzsTOgr1K1rLNOvaFXYs2Ns5siEw7MFcBxjO', @rol_alu, 11);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (12, 'A2025012', 'Martina Silva', '38000012', '11-4000-0012', 'martina.silva@alumnos.academisys.edu', 3);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Martina Silva', '38000012', 'martina.silva@alumnos.academisys.edu', '$2y$12$mWzcoMDozDZN0b8m4yTy9.e6pw7vzQqNk35YMfhk0IxhT1dLxnfxe', @rol_alu, 12);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (13, 'A2025013', 'Bruno Sosa', '38000013', '11-4000-0013', 'bruno.sosa@alumnos.academisys.edu', 3);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Bruno Sosa', '38000013', 'bruno.sosa@alumnos.academisys.edu', '$2y$12$qoS9cufjyLpRef973YgFl.d7kdTLiAZGJWYcpa0otP6Y6O3viNDNq', @rol_alu, 13);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (14, 'A2025014', 'Carla Núñez', '38000014', '11-4000-0014', 'carla.nunez@alumnos.academisys.edu', 3);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Carla Núñez', '38000014', 'carla.nunez@alumnos.academisys.edu', '$2y$12$hzpJf3sIxzGHw1TSyoj1ReBYv798AiDE95Y4MuyRpzIuERTd8lTqS', @rol_alu, 14);
INSERT INTO Alumno (id_alumno, legajo, nombre, dni, telefono, email, id_carrera) VALUES (15, 'A2025015', 'Iván Paredes', '38000015', '11-4000-0015', 'ivan.paredes@alumnos.academisys.edu', 3);
INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_alumno) VALUES ('Iván Paredes', '38000015', 'ivan.paredes@alumnos.academisys.edu', '$2y$12$XcZHRnCQTbWBX5JJDfO4mO7gZ4jzxcyvNl5hxPuosuvOVula8O8qC', @rol_alu, 15);

-- ---------- Materias ----------
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (1, 'Introducción a la Programación', 1, 1);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (2, 'Programación Orientada a Objetos', 2, 1);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (3, 'Estructuras de Datos', 2, 1);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (4, 'Bases de Datos', 2, 1);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (5, 'Desarrollo Web', 3, 1);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (6, 'Análisis de Sistemas I', 1, 2);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (7, 'Análisis de Sistemas II', 2, 2);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (8, 'Ingeniería de Software', 3, 2);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (9, 'Fundamentos de Redes', 1, 3);
INSERT INTO Materia (id_materia, nombre, anio, id_carrera) VALUES (10, 'Administración de Redes', 2, 3);

-- ---------- Correlativas ----------
INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES (2, 1);
INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES (3, 1);
INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES (5, 2);
INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES (5, 4);
INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES (7, 6);
INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES (8, 7);
INSERT INTO Correlativa (id_materia, id_materia_previa) VALUES (10, 9);

-- ---------- Comisiones (vacantes iniciales = cupo del aula) ----------
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (1, 1, 1, 1, 1, 'Lunes', '08:00', '10:00', 30);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (2, 2, 2, 2, 1, 'Lunes', '10:00', '12:00', 25);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (3, 3, 3, 1, 1, 'Martes', '08:00', '10:00', 30);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (4, 4, 4, 3, 1, 'Martes', '10:00', '12:00', 20);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (5, 5, 1, 1, 1, 'Miércoles', '08:00', '10:00', 30);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (6, 6, 5, 2, 1, 'Lunes', '08:00', '10:00', 25);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (7, 7, 5, 2, 1, 'Martes', '08:00', '10:00', 25);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (8, 9, 6, 4, 1, 'Miércoles', '10:00', '12:00', 20);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (9, 8, 4, 3, 1, 'Miércoles', '08:00', '10:00', 20);
INSERT INTO Comision (id_comision, id_materia, id_docente, id_aula, id_periodo, dia, hora_inicio, hora_fin, vacantes_disponibles) VALUES (10, 10, 6, 4, 1, 'Jueves', '08:00', '10:00', 20);

-- ---------- Inscripciones (alumnos en comisiones de su carrera) ----------
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (1, 1, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (1, 2, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (1, 3, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (2, 1, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (2, 4, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (3, 1, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (3, 2, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (4, 3, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (4, 4, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (5, 1, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (6, 2, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (6, 5, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (7, 1, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (7, 3, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (8, 6, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (8, 7, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (9, 6, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (10, 7, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (10, 9, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (11, 6, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (11, 9, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (12, 8, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (13, 8, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (13, 10, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (14, 10, '2025-03-10 09:00:00', 'ACTIVA');
INSERT INTO Inscripcion (id_alumno, id_comision, fecha, estado) VALUES (15, 8, '2025-03-10 09:00:00', 'ACTIVA');

-- Recalcular vacantes según inscriptos activos (coherencia del dato desnormalizado).
UPDATE Comision c SET c.vacantes_disponibles =
    (SELECT a.cupo_maximo FROM Aula a WHERE a.id_aula = c.id_aula)
  - (SELECT COUNT(*) FROM Inscripcion i WHERE i.id_comision = c.id_comision AND i.estado = 'ACTIVA');

-- ---------- Auditoría de inscripciones (espeja las inscripciones) ----------
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (1, 1, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (1, 2, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (1, 3, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (2, 1, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (2, 4, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (3, 1, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (3, 2, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (4, 3, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (4, 4, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (5, 1, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (6, 2, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (6, 5, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (7, 1, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (7, 3, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (8, 6, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (8, 7, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (9, 6, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (10, 7, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (10, 9, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (11, 6, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (11, 9, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (12, 8, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (13, 8, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (13, 10, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (14, 10, '127.0.0.1', '2025-03-10 09:00:00');
INSERT INTO AuditoriaInscripcion (id_alumno, id_comision, ip_origen, fecha_registro) VALUES (15, 8, '127.0.0.1', '2025-03-10 09:00:00');

-- ---------- Notas (Acta). El trigger tr_auditar_nota llena AuditoriaNota. ----------
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (1, 1, '1er Parcial', 7.50, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (1, 1, '2do Parcial', 8.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (1, 1, 'Final', 8.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (2, 1, '1er Parcial', 6.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (2, 1, 'Final', 5.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (3, 1, '1er Parcial', 4.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (6, 2, '1er Parcial', 9.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (8, 6, '1er Parcial', 8.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (8, 6, 'Final', 9.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (10, 6, 'Final', 7.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (10, 7, '1er Parcial', 8.00, '2025-06-20');
INSERT INTO Acta (id_alumno, id_materia, tipo, nota_final, fecha) VALUES (13, 9, '1er Parcial', 6.50, '2025-06-20');

