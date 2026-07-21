<?php
require_once __DIR__ . '/../config/Database.php';

/** ABM de comisiones (con día, horario y vacantes). */
class Comision
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    /**
     * Lista las comisiones activas usando la VISTA SQL vista_comisiones_completas
     * (traduce los ids a nombres y ya filtra activo = 1). Filtro de búsqueda opcional.
     * Es la vista que se muestra en comisiones.php.
     */
    public function listar(string $filtro = ''): array
    {
        // ===== [USA VISTA SQL: vista_comisiones_completas] ===== (definida en sql/01_estructura.sql)
        // Solo las comisiones del ciclo lectivo ABIERTO (el año en curso).
        $sql = "SELECT * FROM vista_comisiones_completas WHERE ciclo_estado='ABIERTO' ";
        if ($filtro !== '') {
            $sql .= "AND (materia LIKE :f1 OR docente LIKE :f2 OR aula LIKE :f3 OR dia LIKE :f4) ";
            $stmt = $this->db->prepare($sql . "ORDER BY id_comision");
            $like = "%$filtro%";
            $stmt->execute(['f1'=>$like,'f2'=>$like,'f3'=>$like,'f4'=>$like]);
            return $stmt->fetchAll();
        }
        return $this->db->query($sql . "ORDER BY id_comision")->fetchAll();
    }

    /**
     * Lista las comisiones de UN docente (para el rol profesor).
     * No usa la vista porque necesita filtrar por id_docente, que la vista no expone.
     */
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
                JOIN CicloLectivo cl  ON cl.id_ciclo  = c.id_ciclo
                WHERE c.activo = 1 AND c.id_docente = :doc AND cl.estado = 'ABIERTO' ";
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
        // Chequeo previo de coherencia horaria (mensaje amable, sin tocar la base).
        // Las horas vienen como "HH:MM" (input type=time), comparables como texto.
        if ($horaInicio >= $horaFin) {
            return [false, 'La hora de inicio debe ser anterior a la hora de fin.'];
        }
        // La comisión se dicta en el CICLO LECTIVO ABIERTO (el año en curso).
        $idCiclo = (int)$this->db->query(
            "SELECT id_ciclo FROM CicloLectivo WHERE estado='ABIERTO' ORDER BY anio DESC LIMIT 1")->fetchColumn();
        if ($idCiclo <= 0) {
            return [false, 'No hay un ciclo lectivo abierto. Abrí/reabrí un ciclo antes de crear comisiones.'];
        }
        try {
            // Las vacantes arrancan en el cupo del aula elegida.
            $cupo = $this->db->prepare("SELECT cupo_maximo FROM Aula WHERE id_aula = :a");
            $cupo->execute(['a' => $idAula]);
            $vacantes = (int) $cupo->fetchColumn();

            // ===== [DISPARA TRIGGER: tr_validar_comision] =====
            // Este INSERT activa el trigger BEFORE INSERT que valida que no se
            // pisen aula ni docente en el mismo ciclo (sql/01_estructura.sql).
            $stmt = $this->db->prepare(
                "INSERT INTO Comision (id_materia, id_docente, id_aula, id_periodo, id_ciclo,
                                       dia, hora_inicio, hora_fin, vacantes_disponibles)
                 VALUES (:m, :d, :a, :p, :cic, :dia, :hi, :hf, :vac)");
            $stmt->execute([
                'm' => $idMateria, 'd' => $idDocente, 'a' => $idAula, 'p' => $idPeriodo, 'cic' => $idCiclo,
                'dia' => $dia, 'hi' => $horaInicio, 'hf' => $horaFin, 'vac' => $vacantes]);
            return [true, 'Comisión creada correctamente.'];
        } catch (PDOException $e) {
            $msg = $e->getMessage();
            // El trigger tr_validar_comision lanza estos mensajes
            if (strpos($msg, 'inicio debe ser anterior') !== false) {
                return [false, 'La hora de inicio debe ser anterior a la hora de fin.'];
            }
            if (strpos($msg, 'aula ya') !== false) {
                return [false, 'El aula ya está ocupada ese día y horario.'];
            }
            if (strpos($msg, 'docente ya') !== false) {
                return [false, 'El docente ya tiene otra clase ese día y horario.'];
            }
            return [false, 'No se pudo crear la comisión: ' . $msg];
        }
    }

    /** READ — Busca una comisión por id (para editar). */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Comision WHERE id_comision = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * UPDATE — Modifica una comisión (siempre permitido, aunque tenga inscriptos).
     * El trigger tr_validar_comision solo actúa en el INSERT, así que acá validamos
     * a mano: horario coherente y que no se pisen aula ni docente (excluyendo la
     * propia comisión). Recalcula las vacantes según el cupo del aula y los
     * inscriptos activos. Devuelve [exito, mensaje].
     */
    public function actualizar(int $id, int $idMateria, int $idDocente, int $idAula, int $idPeriodo,
                               string $dia, string $horaInicio, string $horaFin): array
    {
        if ($horaInicio >= $horaFin) {
            return [false, 'La hora de inicio debe ser anterior a la hora de fin.'];
        }
        // Choque de AULA en el MISMO CICLO de esta comisión (excluye esta misma).
        $qa = $this->db->prepare(
            "SELECT COUNT(*) FROM Comision WHERE activo=1 AND id_comision<>:id
             AND id_ciclo = (SELECT id_ciclo FROM Comision WHERE id_comision=:id2)
             AND id_aula=:a AND dia=:dia AND hora_inicio < :hf AND hora_fin > :hi");
        $qa->execute(['id'=>$id,'id2'=>$id,'a'=>$idAula,'dia'=>$dia,'hf'=>$horaFin,'hi'=>$horaInicio]);
        if ((int)$qa->fetchColumn() > 0) {
            return [false, 'El aula ya está ocupada ese día y horario.'];
        }
        // Choque de DOCENTE en el MISMO CICLO (excluye esta misma comisión).
        $qd = $this->db->prepare(
            "SELECT COUNT(*) FROM Comision WHERE activo=1 AND id_comision<>:id
             AND id_ciclo = (SELECT id_ciclo FROM Comision WHERE id_comision=:id2)
             AND id_docente=:d AND dia=:dia AND hora_inicio < :hf AND hora_fin > :hi");
        $qd->execute(['id'=>$id,'id2'=>$id,'d'=>$idDocente,'dia'=>$dia,'hf'=>$horaFin,'hi'=>$horaInicio]);
        if ((int)$qd->fetchColumn() > 0) {
            return [false, 'El docente ya tiene otra clase ese día y horario.'];
        }
        // Recalcular vacantes = cupo del aula - inscriptos activos (por si cambió el aula).
        $cupo = $this->db->prepare("SELECT cupo_maximo FROM Aula WHERE id_aula=:a");
        $cupo->execute(['a'=>$idAula]);
        $cupoMax = (int)$cupo->fetchColumn();
        $ins = $this->db->prepare("SELECT COUNT(*) FROM Inscripcion WHERE id_comision=:id AND estado='ACTIVA'");
        $ins->execute(['id'=>$id]);
        $vac = max(0, $cupoMax - (int)$ins->fetchColumn());

        $stmt = $this->db->prepare(
            "UPDATE Comision SET id_materia=:m, id_docente=:d, id_aula=:a, id_periodo=:p,
                    dia=:dia, hora_inicio=:hi, hora_fin=:hf, vacantes_disponibles=:vac
             WHERE id_comision=:id");
        $stmt->execute(['m'=>$idMateria,'d'=>$idDocente,'a'=>$idAula,'p'=>$idPeriodo,
            'dia'=>$dia,'hi'=>$horaInicio,'hf'=>$horaFin,'vac'=>$vac,'id'=>$id]);
        return [true, 'Comisión modificada correctamente.'];
    }

    /**
     * DELETE lógico de una comisión. NO permite eliminar si tiene alumnos
     * inscriptos activos (integridad). Devuelve [exito, mensaje].
     */
    public function darDeBaja(int $id): array
    {
        $chk = $this->db->prepare(
            "SELECT COUNT(*) FROM Inscripcion WHERE id_comision = :id AND estado='ACTIVA'");
        $chk->execute(['id' => $id]);
        if ((int)$chk->fetchColumn() > 0) {
            return [false, 'No se puede eliminar: la comisión tiene alumnos inscriptos activos.'];
        }
        $stmt = $this->db->prepare("UPDATE Comision SET activo = 0 WHERE id_comision = :id");
        $stmt->execute(['id' => $id]);
        return [true, 'Comisión eliminada.'];
    }
}
