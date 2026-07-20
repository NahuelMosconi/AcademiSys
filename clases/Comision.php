<?php
require_once __DIR__ . '/../config/Database.php';

/** ABM de comisiones (con día, horario y vacantes). */
class Comision
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    /** Lista las comisiones activas con sus datos relacionados. Filtro opcional. */
    public function listar(string $filtro = ''): array
    {
        $sql = "SELECT c.id_comision, c.dia, c.hora_inicio, c.hora_fin,
                       c.vacantes_disponibles, c.activo,
                       m.nombre AS materia, d.nombre AS docente,
                       a.nombre AS aula, a.cupo_maximo, p.nombre AS periodo
                FROM Comision c
                JOIN Materia m        ON m.id_materia = c.id_materia
                JOIN Docente d        ON d.id_docente = c.id_docente
                JOIN Aula a           ON a.id_aula    = c.id_aula
                JOIN PeriodoLectivo p ON p.id_periodo = c.id_periodo
                WHERE c.activo = 1 ";
        if ($filtro !== '') {
            $sql .= "AND (m.nombre LIKE :f1 OR d.nombre LIKE :f2 OR a.nombre LIKE :f3 OR c.dia LIKE :f4) ";
            $sql .= "ORDER BY c.id_comision";
            $stmt = $this->db->prepare($sql);
            $like = "%$filtro%";
            $stmt->execute(['f1'=>$like,'f2'=>$like,'f3'=>$like,'f4'=>$like]);
            return $stmt->fetchAll();
        }
        return $this->db->query($sql . "ORDER BY c.id_comision")->fetchAll();
    }

    /** Lista las comisiones de UN docente (para el rol profesor). */
    public function listarPorDocente(int $idDocente, string $filtro = ''): array
    {
        $sql = "SELECT c.id_comision, c.dia, c.hora_inicio, c.hora_fin,
                       c.vacantes_disponibles, c.activo,
                       m.nombre AS materia, d.nombre AS docente,
                       a.nombre AS aula, a.cupo_maximo, p.nombre AS periodo
                FROM Comision c
                JOIN Materia m        ON m.id_materia = c.id_materia
                JOIN Docente d        ON d.id_docente = c.id_docente
                JOIN Aula a           ON a.id_aula    = c.id_aula
                JOIN PeriodoLectivo p ON p.id_periodo = c.id_periodo
                WHERE c.activo = 1 AND c.id_docente = :doc ";
        if ($filtro !== '') {
            $sql .= "AND (m.nombre LIKE :f1 OR a.nombre LIKE :f2 OR c.dia LIKE :f3) ";
            $stmt = $this->db->prepare($sql . "ORDER BY c.id_comision");
            $like = "%$filtro%";
            $stmt->execute(['doc'=>$idDocente,'f1'=>$like,'f2'=>$like,'f3'=>$like]);
            return $stmt->fetchAll();
        }
        $stmt = $this->db->prepare($sql . "ORDER BY c.id_comision");
        $stmt->execute(['doc'=>$idDocente]);
        return $stmt->fetchAll();
    }

    /** Para los combos: comisiones activas con un texto descriptivo. */
    public function listarParaCombo(): array
    {
        $sql = "SELECT c.id_comision, c.dia, c.hora_inicio, c.vacantes_disponibles,
                       m.nombre AS materia
                FROM Comision c
                JOIN Materia m ON m.id_materia = c.id_materia
                WHERE c.activo = 1
                ORDER BY m.nombre";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Crea una comisión. Las vacantes iniciales = cupo del aula.
     * Un trigger en la base valida que no se solapen aula ni docente.
     * Devuelve [exito, mensaje].
     */
    public function crear(int $idMateria, int $idDocente, int $idAula, int $idPeriodo,
                          string $dia, string $horaInicio, string $horaFin): array
    {
        try {
            // Las vacantes arrancan en el cupo del aula elegida.
            $cupo = $this->db->prepare("SELECT cupo_maximo FROM Aula WHERE id_aula = :a");
            $cupo->execute(['a' => $idAula]);
            $vacantes = (int) $cupo->fetchColumn();

            $stmt = $this->db->prepare(
                "INSERT INTO Comision (id_materia, id_docente, id_aula, id_periodo,
                                       dia, hora_inicio, hora_fin, vacantes_disponibles)
                 VALUES (:m, :d, :a, :p, :dia, :hi, :hf, :vac)");
            $stmt->execute([
                'm' => $idMateria, 'd' => $idDocente, 'a' => $idAula, 'p' => $idPeriodo,
                'dia' => $dia, 'hi' => $horaInicio, 'hf' => $horaFin, 'vac' => $vacantes]);
            return [true, 'Comisión creada correctamente.'];
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            // El trigger tr_validar_comision lanza estos mensajes
            if (strpos($msg, 'aula ya') !== false) {
                return [false, 'El aula ya está ocupada ese día y horario.'];
            }
            if (strpos($msg, 'docente ya') !== false) {
                return [false, 'El docente ya tiene otra clase ese día y horario.'];
            }
            return [false, 'No se pudo crear la comisión: ' . $msg];
        }
    }

    /**
     * Baja lógica de una comisión. NO permite si tiene inscriptos activos (integridad).
     * Devuelve [exito, mensaje].
     */
    public function darDeBaja(int $id): array
    {
        $chk = $this->db->prepare(
            "SELECT COUNT(*) FROM Inscripcion WHERE id_comision = :id AND estado='ACTIVA'");
        $chk->execute(['id' => $id]);
        if ((int)$chk->fetchColumn() > 0) {
            return [false, 'No se puede dar de baja: la comisión tiene alumnos inscriptos activos.'];
        }
        $stmt = $this->db->prepare("UPDATE Comision SET activo = 0 WHERE id_comision = :id");
        $stmt->execute(['id' => $id]);
        return [true, 'Comisión dada de baja.'];
    }
}
