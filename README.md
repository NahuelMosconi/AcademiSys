# AcademiSys — Sistema de Gestión Académica

Sistema con motor de inscripciones (cupo, correlativas, solapamiento horario) y transacciones ACID.
Hecho en PHP (POO + MVC) y MySQL, con acceso a datos vía PDO y consultas preparadas.

## Puesta en marcha (Ubuntu / XAMPP / cualquier Apache + MySQL)

1. Copiá el proyecto a la raíz web (ej. `/var/www/html/academisys` o `htdocs`).
2. En tu gestor de MySQL ejecutá:
   - `sql/01_estructura.sql` — crea la base, las 14 tablas, los procedimientos,
     los triggers, los índices y las vistas SQL, y siembra los datos base mínimos
     (los 3 roles y el usuario administrador inicial).
   - *(opcional)* `sql/02_datos.sql` — datos de ejemplo **funcionales**: carreras,
     docentes, alumnos, materias con correlativas, comisiones, inscripciones y
     notas. Cada docente y alumno viene con su cuenta de acceso lista (login por
     DNI, contraseña = DNI). Ejecutalo **después** de `01_estructura.sql`.
3. Revisá los datos de conexión en `config/Database.php` (host, base, usuario y
   contraseña de MySQL) y ajustalos a tu entorno.
4. Entrá a `http://localhost/academisys/` e ingresá con las credenciales de
   `CREDENCIALES.txt` (administrador: DNI `10000000`, contraseña `10000000`).

> `sql/02_datos.sql` no es una carga masiva "vacía": todos los docentes y alumnos
> quedan con su cuenta de acceso real. Se regenera/amplía con
> `php sql/generar_datos.php` (los `password_hash` son bcrypt reales del DNI).
> Si preferís empezar de cero, salteá ese paso y creá todo desde la app.

## Usuarios y acceso

- **Login por DNI.** La contraseña inicial de todos es su propio DNI; cada uno la
  puede cambiar desde "Mi perfil" (mínimo 8 caracteres).
- **Todos pueden ingresar.** Al dar de alta un **docente** o un **alumno**, el
  sistema le crea automáticamente su cuenta de acceso (rol Profesor o Alumno,
  login y contraseña = DNI). No hace falta ningún paso extra.
- **Usuarios (admin).** La pantalla "Usuarios" se usa solo para crear cuentas de
  **Administrador** y para dar de baja cuentas; los docentes y alumnos ya vienen
  con la suya.
- **Roles:**
  - *Administrador:* ABM completo (alumnos, docentes, materias, comisiones,
    usuarios), auditoría, inscripciones y carga de notas.
  - *Profesor:* ve solo sus comisiones/alumnos y carga notas solo de las materias
    que dicta.
  - *Alumno:* ve solo sus materias/horarios y sus notas (solo lectura).

## Qué cubre

- **DDL:** CREATE DATABASE, 14 tablas, PK/FK, tipos de datos.
- **Procedimientos y triggers:** `RegistrarNota`, `InscribirAlumno`,
  `AnularInscripcion`; trigger de auditoría de notas y trigger que valida el
  solapamiento de aula/docente al crear comisiones; índices.
- **Vistas SQL:** `vista_inscripciones`, `vista_notas_alumnos` y
  `vista_comisiones_completas`, que el PHP consume directamente en los listados.
- **Seguridad y arquitectura:** login, roles, `password_hash`, sesiones con
  timeout por inactividad, protección de session fixation, PDO con consultas
  preparadas, POO y MVC.
- **Motor de inscripciones:** valida cupo del aula, correlativas y solapamiento
  horario, con transacción ACID y auditoría de IP.
- **Bajas lógicas:** los registros se marcan inactivos, no se borran (y al dar de
  baja un docente/alumno se desactiva también su cuenta de acceso).
