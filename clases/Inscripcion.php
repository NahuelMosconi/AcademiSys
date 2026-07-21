<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Maneja las inscripciones usando el motor InscribirAlumno (las 3 reglas + ACID)
 * y la baja lógica AnularInscripcion.
 */
class Inscripcion
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    /**
     * READ (listado principal, para el admin) usando la VISTA SQL vista_inscripciones.
     * Los 4 JOINs (Inscripcion + Alumno + Comision + Materia) viven una sola vez en
     * la base (sql/01_estructura.sql); acá la vista se consulta como si fuera una
     * tabla, con un filtro opcional de búsqueda. Es la vista que se muestra en
     * inscripciones.php.
     */
    public function listar(string $filtro = ''): array
    {
        // ===== [USA VISTA SQL: vista_inscripciones] ===== (definida en sql/01_estructura.sql)
        // Solo las inscripciones del ciclo lectivo ABIERTO (el año en curso).
        $sql = "SELECT * FROM vista_inscripciones WHERE ciclo_estado='ABIERTO' ";
        if ($filtro !== '') {
            $sql .= "AND (alumno LIKE :f1 OR legajo LIKE :f2 OR materia LIKE :f3 OR estado LIKE :f4) ";
            $stmt = $this->db->prepare($sql . "ORDER BY fecha DESC LIMIT 200");
            $like = "%$filtro%";
            $stmt->execute(['f1'=>$like,'f2'=>$like,'f3'=>$like,'f4'=>$like]);
            return $stmt->fetchAll();
        }
        return $this->db->query($sql . "ORDER BY fecha DESC LIMIT 200")->fetchAll();
    }

    /**
     * Lista inscripciones SOLO de las comisiones de un docente (rol profesor).
     * No usa vista_inscripciones porque esa vista no expone id_docente para filtrar;
     * acá hace falta la consulta parametrizada por docente.
     */
    public function listarPorDocente(int $idDocente, string $filtro = ''): array
    {
        $sql = "SELECT i.id_inscripcion, i.fecha, i.estado,
                       al.nombre AS alumno, al.legajo,
                       m.nombre  AS materia,
                       c.dia, c.hora_inicio, c.hora_fin
                FROM Inscripcion i
                JOIN Alumno al  ON al.id_alumno = i.id_alumno
                JOIN Comision c ON c.id_comision = i.id_comision
                JOIN Materia m  ON m.id_materia = c.id_materia
                JOIN CicloLectivo cl ON cl.id_ciclo = c.id_ciclo
                WHERE c.id_docente = :doc AND cl.estado = 'ABIERTO' ";
        if ($filtro !== '') {
            $sql .= "AND (al.nombre LIKE :f1 OR al.legajo LIKE :f2 OR m.nombre LIKE :f3) ";
            $stmt = $this->db->prepare($sql . "ORDER BY i.fecha DESC LIMIT 200");
            $like = "%$filtro%";
            $stmt->execute(['doc'=>$idDocente,'f1'=>$like,'f2'=>$like,'f3'=>$like]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->prepare($sql . "ORDER BY i.fecha DESC LIMIT 200");
        $stmt->execute(['doc'=>$idDocente]);
        return $stmt->fetchAll();
    }

    /** Materias/horarios en los que está inscripto UN alumno (rol Alumno). */
    public function listarPorAlumno(int $idAlumno): array
    {
        $stmt = $this->db->prepare(
            "SELECT m.nombre AS materia, d.nombre AS docente, a.nombre AS aula,
                    c.dia, c.hora_inicio, c.hora_fin, i.estado, p.nombre AS periodo
             FROM Inscripcion i
             JOIN Comision c ON c.id_comision = i.id_comision
             JOIN Materia m  ON m.id_materia = c.id_materia
             JOIN Docente d  ON d.id_docente = c.id_docente
             JOIN Aula a     ON a.id_aula = c.id_aula
             JOIN PeriodoLectivo p ON p.id_periodo = c.id_periodo
             JOIN CicloLectivo cl  ON cl.id_ciclo  = c.id_ciclo
             WHERE i.id_alumno = :al AND i.estado = 'ACTIVA' AND cl.estado = 'ABIERTO'
             ORDER BY FIELD(c.dia,'Lunes','Martes','Miércoles','Jueves','Viernes'), c.hora_inicio");
        $stmt->execute(['al' => $idAlumno]);
        return $stmt->fetchAll();
    }

    /**
     * Comisiones a las que un alumno PUEDE inscribirse: de su carrera y SOLO del
     * ciclo lectivo ABIERTO (el año en curso).
     */
    public function comisionesParaAlumno(int $idAlumno): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.id_comision, m.nombre AS materia, c.dia, c.hora_inicio, c.vacantes_disponibles
             FROM Comision c
             JOIN Materia m ON m.id_materia = c.id_materia
             JOIN Alumno al ON al.id_carrera = m.id_carrera
             JOIN CicloLectivo cl ON cl.id_ciclo = c.id_ciclo
             WHERE al.id_alumno = :al AND c.activo = 1 AND cl.estado = 'ABIERTO'
             ORDER BY m.nombre");
        $stmt->execute(['al'=>$idAlumno]);
        return $stmt->fetchAll();
    }

    /**
     * Inscribe un alumno llamando al MOTOR (procedimiento InscribirAlumno).
     * El procedimiento valida cupo, correlativas y solapamiento, y hace la
     * transacción ACID. Devuelve [exito, mensaje].
     */
    public function inscribir(int $idAlumno, int $idComision, string $ip): array
    {
        try {
            // ===== [USA PROCEDIMIENTO ALMACENADO: InscribirAlumno] =====
            // El "motor": valida cupo/correlativas/solapamiento y hace la
            // transacción ACID. Definido en sql/01_estructura.sql.
            $stmt = $this->db->prepare("CALL InscribirAlumno(:a, :c, :ip)");
            $stmt->execute(['a' => $idAlumno, 'c' => $idComision, 'ip' => $ip]);
            return [true, 'Inscripción confirmada correctamente.'];
        } catch (PDOException $e) {
            // El procedimiento lanza el motivo del rechazo en el mensaje de error.
            $motivo = $e->getMessage();

            // Si el mensaje es el nombre de una materia, es por correlativas.
            if (strpos($motivo, 'cupo') !== false) {
                return [false, 'No hay cupo disponible: el aula está llena.'];
            }
            if (strpos($motivo, 'día y horario') !== false || strpos($motivo, 'horario') !== false) {
                return [false, 'Solapamiento: ya tenés otra materia ese día y horario.'];
            }
            if (strpos($motivo, 'ya estaba') !== false || strpos($motivo, 'Duplicate') !== false) {
                return [false, 'El alumno ya está inscripto en esa comisión.'];
            }
            // El SP devuelve el nombre de la materia que falta como mensaje
            // cuando son correlativas. Limpiamos el prefijo técnico de PDO.
            $limpio = preg_replace('/^SQLSTATE\[\w+\].*?: \d+ /', '', $motivo);
            return [false, 'Te falta aprobar una correlativa: ' . $limpio];
        }
    }

    /** Anula una inscripción (baja lógica vía procedimiento AnularInscripcion). */
    public function anular(int $idInscripcion): array
    {
        try {
            // ===== [USA PROCEDIMIENTO ALMACENADO: AnularInscripcion] =====
            // Baja lógica (marca 'ANULADA') y devuelve la vacante. sql/01_estructura.sql.
            $stmt = $this->db->prepare("CALL AnularInscripcion(:id)");
            $stmt->execute(['id' => $idInscripcion]);
            return [true, 'Inscripción anulada (baja lógica).'];
        } catch (PDOException $e) {
            return [false, 'No se pudo anular: ' . $e->getMessage()];
        }
    }
}
