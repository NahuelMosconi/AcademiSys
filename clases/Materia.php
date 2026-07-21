<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Materia — ABM de materias con baja lógica.
 * Cada materia pertenece a una carrera (id_carrera) y puede tener correlativas.
 */
class Materia
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    /**
     * READ — Lista materias activas con el nombre de su carrera (JOIN con Carrera).
     * Con filtro opcional, busca por nombre de materia o de carrera.
     */
    public function listar(string $filtro = ''): array
    {
        // JOIN para traer el nombre de la carrera en vez de solo su id.
        $sql = "SELECT m.id_materia, m.nombre, m.anio, c.nombre AS carrera
                FROM Materia m
                JOIN Carrera c ON c.id_carrera = m.id_carrera
                WHERE m.activo = 1 ";
        if ($filtro !== '') {
            $sql .= "AND (m.nombre LIKE :f1 OR c.nombre LIKE :f2) ";
            $stmt = $this->db->prepare($sql . "ORDER BY c.nombre, m.anio, m.nombre");
            $like = "%$filtro%";
            $stmt->execute(['f1'=>$like,'f2'=>$like]);
            return $stmt->fetchAll();
        }
        return $this->db->query($sql . "ORDER BY c.nombre, m.anio, m.nombre")->fetchAll();
    }

    /** READ — Versión reducida (solo id y nombre) para llenar los combos <select>. */
    public function listarParaCombo(): array
    {
        return $this->db->query(
            "SELECT id_materia, nombre FROM Materia WHERE activo = 1 ORDER BY nombre")->fetchAll();
    }

    /** READ — Busca una materia por id (para editar). */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Materia WHERE id_materia = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** CREATE — Da de alta una materia vinculada a una carrera. */
    public function crear(string $nombre, int $anio, int $idCarrera): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Materia (nombre, anio, id_carrera) VALUES (:n, :a, :c)");
        return $stmt->execute(['n' => $nombre, 'a' => $anio, 'c' => $idCarrera]);
    }

    /** UPDATE — Modifica una materia (siempre permitido, aunque esté en uso). */
    public function actualizar(int $id, string $nombre, int $anio, int $idCarrera): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Materia SET nombre = :n, anio = :a, id_carrera = :c WHERE id_materia = :id");
        return $stmt->execute(['n' => $nombre, 'a' => $anio, 'c' => $idCarrera, 'id' => $id]);
    }

    /**
     * DELETE lógico con control de integridad. Devuelve [exito, mensaje].
     * NO permite eliminar si la materia tiene COMISIONES activas (profesor asignado)
     * o ALUMNOS inscriptos activos.
     */
    public function darDeBaja(int $id): array
    {
        // Comisiones activas de la materia = tiene profesor (docente) asignado.
        $chkCom = $this->db->prepare(
            "SELECT COUNT(*) FROM Comision WHERE id_materia = :id AND activo = 1");
        $chkCom->execute(['id' => $id]);
        if ((int)$chkCom->fetchColumn() > 0) {
            return [false, 'No se puede eliminar: la materia tiene comisiones (con profesor/alumnos).'];
        }
        // Por las dudas, tampoco si hubiera inscriptos activos.
        $chk = $this->db->prepare(
            "SELECT COUNT(*) FROM Inscripcion i
             JOIN Comision c ON c.id_comision = i.id_comision
             WHERE c.id_materia = :id AND i.estado='ACTIVA'");
        $chk->execute(['id' => $id]);
        if ((int)$chk->fetchColumn() > 0) {
            return [false, 'No se puede eliminar: la materia tiene alumnos inscriptos activos.'];
        }
        $stmt = $this->db->prepare("UPDATE Materia SET activo = 0 WHERE id_materia = :id");
        $stmt->execute(['id' => $id]);
        return [true, 'Materia eliminada.'];
    }
}
