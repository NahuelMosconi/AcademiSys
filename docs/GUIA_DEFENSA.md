# Guía de defensa — AcademiSys

Documento para preparar la defensa. Mapea **cada objeto de la base** (índices,
procedimientos, triggers, vistas) con **dónde está en el SQL** y **dónde se usa
en el PHP** (archivo → método → pantalla). Al final hay preguntas típicas con
su respuesta corta.

> **Cómo buscar rápido:** en `sql/01_estructura.sql` cada objeto tiene un banner
> del estilo `[PROCEDIMIENTO: InscribirAlumno]`. Buscá ese texto con Ctrl+F.
> En el PHP, buscá `[USA PROCEDIMIENTO`, `[USA VISTA`, `[DISPARA TRIGGER` o
> `[LEE la tabla`.

---

## 1. Qué es y cómo está armado

- **Qué es:** sistema de gestión académica (carreras, materias, docentes,
  alumnos, comisiones, inscripciones y notas) con un **motor de inscripciones**
  que valida cupo, correlativas y solapamiento horario.
- **Tecnología:** PHP (POO + patrón **MVC**) y **MySQL**, con acceso a datos por
  **PDO** y consultas preparadas.
- **Capas:**
  - `config/` → conexión (`Database.php`, PDO Singleton), sesión/roles
    (`sesion.php`) e íconos (`iconos.php`).
  - `clases/` → **Modelo**: una clase por entidad, con el acceso a datos.
  - `vistas/` → **Controlador + Vista**: procesan el formulario y muestran HTML.
  - `sql/` → estructura (`01_estructura.sql`) y datos de ejemplo (`02_datos.sql`).

---

## 2. Mapa de ÍNDICES

| Índice | Sobre | Buscar en SQL | Para qué sirve / dónde pega |
|---|---|---|---|
| `idx_acta_alumno` | `Acta(id_alumno)` | `[INDICE: idx_acta_alumno]` | Traer las actas de un alumno. Lo usa el motor al validar correlativas y `clases/Acta.php → listarPorAlumno()` (pantalla **Mis notas**). |
| `idx_insc_comision` | `Inscripcion(id_comision)` | `[INDICE: idx_insc_comision]` | Contar inscriptos de una comisión (cupo, Regla 1 del motor). |
| `idx_alumno_nombre` | `Alumno(nombre)` | `[INDICE: idx_alumno_nombre]` | Listados ordenados/buscados por nombre. `clases/Alumno.php → listar()` (pantalla **Alumnos**). |

**Qué es un índice:** una estructura que evita recorrer toda la tabla al buscar
por esa columna (como el índice de un libro). Acelera lecturas frecuentes.

---

## 3. Mapa de PROCEDIMIENTOS ALMACENADOS

Un procedimiento almacenado es código SQL guardado en la base con un nombre; se
ejecuta con `CALL`. En phpMyAdmin se ven en **Rutinas** (y su descripción está en
la cláusula `COMMENT`).

### 3.1 `RegistrarNota` — cargar una nota
- **SQL:** buscar `[PROCEDIMIENTO: RegistrarNota]`.
- **Qué hace:** valida que la nota esté entre 0 y 10 e inserta en `Acta`.
- **Se llama desde:** `clases/Acta.php → registrar()` (buscar `[USA PROCEDIMIENTO ALMACENADO: RegistrarNota]`).
- **Pantalla:** `vistas/notas.php` (**Notas**).
- **Efecto secundario:** el `INSERT` en `Acta` **dispara** el trigger `tr_auditar_nota`.

### 3.2 `InscribirAlumno` — EL MOTOR (lo más importante)
- **SQL:** buscar `[PROCEDIMIENTO: InscribirAlumno]`.
- **Qué hace:** valida **3 reglas** y, si pasan todas, hace una **transacción ACID**:
  1. **Regla 1 – Cupo:** no superar `cupo_maximo` del aula (cuenta inscriptos ACTIVOS).
  2. **Regla 2 – Correlativas:** tener `Final >= 4` en todas las materias previas.
  3. **Regla 3 – Solapamiento:** no chocar con otra materia el mismo día/horario.
  - Si pasa todo: `INSERT` en `Inscripcion` + descuenta vacante + inserta en
    `AuditoriaInscripcion` (con IP). Los tres, atómicos (todo o nada).
- **Se llama desde:** `clases/Inscripcion.php → inscribir()` (buscar `[USA PROCEDIMIENTO ALMACENADO: InscribirAlumno]`).
- **Pantalla:** `vistas/inscripciones.php` (**Inscripciones**).

### 3.3 `AnularInscripcion` — baja lógica
- **SQL:** buscar `[PROCEDIMIENTO: AnularInscripcion]`.
- **Qué hace:** marca la inscripción como `ANULADA` (no borra) y **devuelve la vacante**.
- **Se llama desde:** `clases/Inscripcion.php → anular()`.
- **Pantalla:** `vistas/inscripciones.php`.

---

## 4. Mapa de TRIGGERS

Un trigger (disparador) se ejecuta **solo**, automáticamente, cuando ocurre un
evento en una tabla. Nadie lo llama a mano.

### 4.1 `tr_auditar_nota` — auditoría de notas
- **SQL:** buscar `[TRIGGER: tr_auditar_nota]`.
- **Cuándo:** `AFTER INSERT ON Acta` (después de cargar una nota).
- **Qué hace:** copia la nota a `AuditoriaNota` con la fecha/hora.
- **Se ve en:** `vistas/auditoria.php`, leído por `clases/AuditoriaNota.php → listar()`
  (buscar `[LEE la tabla que llena el TRIGGER: tr_auditar_nota]`).

### 4.2 `tr_validar_comision` — evitar choques
- **SQL:** buscar `[TRIGGER: tr_validar_comision]`.
- **Cuándo:** `BEFORE INSERT ON Comision` (antes de crear una comisión).
- **Qué hace:** rechaza (con `SIGNAL`) si la misma **aula** o el mismo **docente**
  ya están ocupados ese día/horario.
- **Se dispara desde:** `clases/Comision.php → crear()` (buscar `[DISPARA TRIGGER: tr_validar_comision]`).
- **Pantalla:** `vistas/comisiones.php`.

---

## 5. Mapa de VISTAS SQL

Una vista es una consulta `SELECT` guardada con un nombre; se usa como una tabla
pero no guarda datos propios (trae datos frescos al consultarla). Resuelven los
`JOIN` una sola vez para no repetirlos en el PHP.

| Vista | Buscar en SQL | Cruza | Se usa en PHP | Pantalla |
|---|---|---|---|---|
| `vista_inscripciones` | `[VISTA: vista_inscripciones]` | Inscripcion+Alumno+Comision+Materia+Docente+Aula | `clases/Inscripcion.php → listar()` | Inscripciones |
| `vista_notas_alumnos` | `[VISTA: vista_notas_alumnos]` | Acta+Alumno+Materia | `clases/Acta.php → listar()` y `listarPorAlumno()` | Notas / Mis notas |
| `vista_comisiones_completas` | `[VISTA: vista_comisiones_completas]` | Comision+Materia+Docente+Aula+PeriodoLectivo | `clases/Comision.php → listar()` | Comisiones |

> En el PHP buscá `[USA VISTA SQL: ...]` para ver el `SELECT * FROM la_vista`.

---

## 6. El motor de inscripción, paso a paso (por si preguntan)

Cuando el admin confirma una inscripción:
1. `vistas/inscripciones.php` toma alumno + comisión + la IP del usuario.
2. Llama a `clases/Inscripcion.php → inscribir()`, que hace `CALL InscribirAlumno(...)`.
3. El procedimiento:
   - lee los datos de la comisión (materia, aula, día, horario, cupo);
   - **Regla 1** cupo, **Regla 2** correlativas, **Regla 3** solapamiento;
   - si alguna falla, `SIGNAL` corta y PHP muestra el motivo;
   - si pasan todas, `START TRANSACTION` → inserta inscripción, descuenta vacante,
     inserta auditoría → `COMMIT`. Si algo falla en el medio, el `EXIT HANDLER`
     hace `ROLLBACK` (no queda nada a medias).

**ACID en una frase:** o se hacen los 3 pasos (inscripción + vacante + auditoría)
o no se hace ninguno; nunca queda un estado intermedio.

---

## 7. Seguridad (suelen preguntarlo)

- **Contraseñas:** se guardan **hasheadas con `password_hash` (bcrypt)**, nunca en
  texto. El login usa `password_verify`. Ver `clases/Usuario.php → login()`.
- **Inyección SQL:** todo el acceso usa **PDO con consultas preparadas**
  (`prepare` + `execute` con parámetros `:x`). Ver `config/Database.php`
  (`PDO::ATTR_EMULATE_PREPARES => false`, prepared statements reales).
- **Sesiones:** `config/sesion.php` — timeout por inactividad (20 min) y
  `session_regenerate_id(true)` al loguear (previene *session fixation*).
- **Roles:** `requerirRol([...])` corta el acceso a pantallas según el rol
  (Administrador / Profesor / Alumno).
- **Bajas lógicas:** no se borra; se marca `activo = 0` o `estado = 'ANULADA'`.

---

## 8. Sobre los comentarios en phpMyAdmin (aclaración importante)

- **Procedimientos:** llevan una cláusula **`COMMENT '...'`** que **sí** se guarda
  y phpMyAdmin la muestra en **Rutinas** (columna comentario).
- **Cuerpo de rutinas y triggers:** MySQL/MariaDB puede **reescribir** el código y,
  según el motor, **quitar los comentarios internos** al guardarlos. MariaDB los
  quita; MySQL (el de XAMPP) normalmente los conserva.
- **Vistas:** MySQL **siempre reescribe** el `SELECT` y **elimina** los comentarios.
- **Conclusión:** la explicación completa y comentada vive en `sql/01_estructura.sql`
  (este es el archivo para leer/mostrar cómo funciona todo). Si en phpMyAdmin no ves
  los comentarios internos, es por eso — abrí el `.sql`.

---

## 9. Preguntas típicas y respuesta corta

- **¿Dónde está el procedimiento del motor?** En `sql/01_estructura.sql`
  (`[PROCEDIMIENTO: InscribirAlumno]`); se llama desde `clases/Inscripcion.php → inscribir()`.
- **¿Qué es una transacción ACID y dónde la usan?** En `InscribirAlumno`:
  inscripción + vacante + auditoría como una sola unidad (`START TRANSACTION`/`COMMIT`,
  con `ROLLBACK` ante error).
- **¿Para qué un trigger y no código PHP?** Porque debe ejecutarse **siempre** a nivel
  base, sin depender de que el programador se acuerde (auditoría y validación de choques).
- **¿Por qué vistas?** Para no repetir los `JOIN` en cada consulta y tener una única
  definición de "inscripción/nota/comisión legible".
- **¿Por qué índices?** Para acelerar las búsquedas frecuentes (correlativas, cupo,
  listados por nombre) sin recorrer toda la tabla.
- **¿Cómo evitan SQL injection?** PDO con consultas preparadas y parámetros.
- **¿Cómo guardan las contraseñas?** Con `password_hash` (bcrypt); nunca en claro.
- **¿Qué es la baja lógica?** Marcar inactivo/anulado en vez de borrar, para conservar
  el historial.
