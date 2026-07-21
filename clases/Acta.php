<?php
require_once __DIR__ . '/../config/Database.php';

/** Historial académico: notas por tipo (parcial/final). Usa el SP RegistrarNota. */
class Acta
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    // Tipos de nota. Los parciales (con sus recuperatorios) definen la
    // regularidad/promoción; el Final aprueba la materia si no se promocionó.
    // El estado resultante lo calcula, en la base, la función fn_estado_materia
    // (ver sql/01_estructura.sql).
    public const TIPOS = ['1er Parcial', 'Recup 1er Parcial',
                          '2do Parcial', 'Recup 2do Parcial', 'Final'];

    /**
     * Estado de cada materia de un alumno
     * (Aprobada / Promocionada / Regular / Libre), calculado por la función
     * SQL fn_estado_materia. Se usa en la pantalla "Mis notas" del alumno.
     * Devuelve filas: materia, estado.
     */
    public function estadoPorAlumno(int $idAlumno): array
    {
        // ===== [USA FUNCIÓN SQL: fn_estado_materia] ===== (sql/01_estructura.sql)
        $stmt = $this->db->prepare(
            "SELECT m.nombre AS materia,
                    fn_estado_materia(:al, m.id_materia) AS estado
             FROM (SELECT DISTINCT id_materia FROM Acta WHERE id_alumno = :al2) t
             JOIN Materia m ON m.id_materia = t.id_materia
             ORDER BY m.nombre");
        $stmt->execute(['al' => $idAlumno, 'al2' => $idAlumno]);
        return $stmt->fetchAll();
    }

    /**
     * Lista actas (para el admin) usando la VISTA SQL vista_notas_alumnos, que ya
     * cruza Acta + Alumno + Materia. Filtro de búsqueda opcional. Es la vista que
     * se muestra en notas.php.
     */
    public function listar(string $filtro = ''): array
    {
        // ===== [USA VISTA SQL: vista_notas_alumnos] ===== (definida en sql/01_estructura.sql)
        $sql = "SELECT * FROM vista_notas_alumnos ";
        if ($filtro !== '') {
            $stmt = $this->db->prepare($sql .
                "WHERE alumno LIKE :f1 OR legajo LIKE :f2 OR materia LIKE :f3 OR tipo LIKE :f4
                 ORDER BY fecha DESC LIMIT 200");
            $like = "%$filtro%";
            $stmt->execute(['f1'=>$like,'f2'=>$like,'f3'=>$like,'f4'=>$like]);
            return $stmt->fetchAll();
        }
        return $this->db->query($sql . "ORDER BY fecha DESC LIMIT 200")->fetchAll();
    }

    public function contar(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM Acta")->fetchColumn();
    }

    /**
     * Lista actas SOLO de las materias que dicta un docente (rol profesor).
     */
    public function listarPorDocente(int $idDocente, string $filtro = ''): array
    {
        $base = "SELECT ac.id_acta, al.nombre AS alumno, al.legajo,
                        m.nombre AS materia, ac.tipo, ac.nota_final, ac.fecha
                 FROM Acta ac
                 JOIN Alumno al ON al.id_alumno = ac.id_alumno
                 JOIN Materia m ON m.id_materia = ac.id_materia
                 WHERE ac.id_materia IN (
                     SELECT DISTINCT c.id_materia FROM Comision c WHERE c.id_docente = :doc
                 ) ";
        if ($filtro !== '') {
            $stmt = $this->db->prepare($base .
                "AND (al.nombre LIKE :f1 OR al.legajo LIKE :f2 OR m.nombre LIKE :f3 OR ac.tipo LIKE :f4)
                 ORDER BY ac.fecha DESC LIMIT 200");
            $like = "%$filtro%";
            $stmt->execute(['doc'=>$idDocente,'f1'=>$like,'f2'=>$like,'f3'=>$like,'f4'=>$like]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->prepare($base . "ORDER BY ac.fecha DESC LIMIT 200");
        $stmt->execute(['doc'=>$idDocente]);
        return $stmt->fetchAll();
    }

    /**
     * Notas de UN alumno específico (para el rol Alumno: ve solo las suyas).
     * Usa la VISTA SQL vista_notas_alumnos, que sí expone id_alumno para filtrar.
     */
    public function listarPorAlumno(int $idAlumno): array
    {
        // ===== [USA VISTA SQL: vista_notas_alumnos] ===== (filtrada por id_alumno)
        $stmt = $this->db->prepare(
            "SELECT materia, tipo, nota_final, fecha
             FROM vista_notas_alumnos
             WHERE id_alumno = :al
             ORDER BY materia, fecha");
        $stmt->execute(['al' => $idAlumno]);
        return $stmt->fetchAll();
    }

    /** Materias que dicta un docente (para el combo de carga de notas del profesor). */
    public function materiasDelDocente(int $idDocente): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT m.id_materia, m.nombre
             FROM Comision c JOIN Materia m ON m.id_materia = c.id_materia
             WHERE c.id_docente = :doc AND c.activo = 1
             ORDER BY m.nombre");
        $stmt->execute(['doc'=>$idDocente]);
        return $stmt->fetchAll();
    }

    /** Alumnos inscriptos (activos) en las comisiones de una materia del docente. */
    public function alumnosDeMateriaDelDocente(int $idDocente, int $idMateria): array
    {
        $stmt = $this->db->prepare(
            "SELECT DISTINCT al.id_alumno, al.nombre, al.legajo
             FROM Inscripcion i
             JOIN Comision c ON c.id_comision = i.id_comision
             JOIN Alumno al  ON al.id_alumno = i.id_alumno
             WHERE c.id_docente = :doc AND c.id_materia = :mat AND i.estado='ACTIVA'
             ORDER BY al.nombre");
        $stmt->execute(['doc'=>$idDocente, 'mat'=>$idMateria]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica que un docente realmente dicte una materia (seguridad antes de cargar nota).
     */
    public function docenteDictaMateria(int $idDocente, int $idMateria): bool
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM Comision WHERE id_docente=:doc AND id_materia=:mat");
        $stmt->execute(['doc'=>$idDocente, 'mat'=>$idMateria]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Registra una nota de un tipo dado, vía el procedimiento RegistrarNota.
     * Si ya existe ese tipo para ese alumno/materia, el UNIQUE lo rechaza.
     * Devuelve [exito, mensaje].
     */
    public function registrar(int $idAlumno, int $idMateria, string $tipo, float $nota): array
    {
        try {
            // ===== [USA PROCEDIMIENTO ALMACENADO: RegistrarNota] =====
            // Valida 0-10 e inserta en Acta (eso dispara el trigger tr_auditar_nota).
            $stmt = $this->db->prepare("CALL RegistrarNota(:a, :m, :t, :n)");
            $stmt->execute(['a'=>$idAlumno, 'm'=>$idMateria, 't'=>$tipo, 'n'=>$nota]);
            return [true, 'Nota registrada correctamente.'];
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            // Error de UNIQUE = ya existe ese tipo de nota para esa materia
            if (strpos($msg, 'Duplicate') !== false || strpos($msg, '1062') !== false) {
                return [false, 'Ese alumno ya tiene cargada la nota de "' . $tipo . '" en esa materia.'];
            }
            if (strpos($msg, 'entre 0 y 10') !== false) {
                return [false, 'La nota debe estar entre 0 y 10.'];
            }
            return [false, 'No se pudo registrar: ' . $msg];
        }
    }
}
