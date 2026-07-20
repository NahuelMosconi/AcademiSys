# AcademiSys — Sistema de Gestión Académica

Sistema con motor de inscripciones (cupo, correlativas, solapamiento horario) y transacciones ACID.

## Puesta en marcha (Ubuntu / XAMPP / cualquier Apache+MySQL)

1. Copiá la carpeta `php/` a la raíz web (ej. `/var/www/html/academisys` o `htdocs`).
2. En tu gestor de MySQL ejecutá, en orden:
   - `sql/01_estructura.sql`  (crea la base, tablas, procedimientos, trigger e índices)
   - `sql/03_datos_masivos.sql`  (carga ~12.700 registros)
   - *(alternativa: `sql/02_datos.sql` para datos chicos de prueba)*
3. Entrá a `http://localhost/academisys/` e ingresá con `CREDENCIALES.txt`.

## Qué cubre

- **Tarea 3 (DDL):** CREATE DATABASE, 14 tablas, PK/FK, tipos de datos.
- **Tarea 4:** procedimientos (RegistrarNota, InscribirAlumno, AnularInscripcion), trigger e índices.
- **Tarea 5:** login, roles, password_hash, sesiones con timeout, PDO, POO, MVC.
- **Motor de inscripciones:** valida cupo del aula, correlativas y solapamiento horario, con transacción ACID y auditoría de IP.
- **Bajas lógicas:** los registros se marcan inactivos, no se borran.
