<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Carrera — ABM de carreras.
 * Sigue el patrón común: el constructor toma la conexión PDO y cada método
 * hace una operación contra la base usando consultas preparadas.
 */
class Carrera
{
    private PDO $db;   // conexión compartida a la base

    // Constructor: pide la conexión al Singleton de Database.
    public function __construct() { $this->db = Database::conectar(); }

    /**
     * READ — Lista las carreras activas (baja lógica: solo activo = 1).
     * Sirve para los combos donde se elige una carrera.
     */
    public function listar(): array
    {
        return $this->db->query(
            "SELECT * FROM Carrera WHERE activo = 1 ORDER BY nombre")->fetchAll();
    }

    /** READ — Busca una carrera por id (para editar). */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Carrera WHERE id_carrera = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * CREATE — Da de alta una carrera nueva.
     * Usa placeholders (:n, :d) que se rellenan en execute, para evitar inyección SQL.
     */
    public function crear(string $nombre, int $duracion): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Carrera (nombre, duracion_anios) VALUES (:n, :d)");
        return $stmt->execute(['n' => $nombre, 'd' => $duracion]);
    }

    /** UPDATE — Modifica una carrera (siempre permitido, aunque tenga alumnos/materias). */
    public function actualizar(int $id, string $nombre, int $duracion): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Carrera SET nombre = :n, duracion_anios = :d WHERE id_carrera = :id");
        return $stmt->execute(['n' => $nombre, 'd' => $duracion, 'id' => $id]);
    }

    /**
     * DELETE lógico con control de integridad. Devuelve [exito, mensaje].
     * NO permite eliminar si la carrera tiene ALUMNOS activos o MATERIAS activas
     * (las materias implican profesores/comisiones asignados).
     */
    public function darDeBaja(int $id): array
    {
        $chkAlu = $this->db->prepare(
            "SELECT COUNT(*) FROM Alumno WHERE id_carrera = :id AND activo = 1");
        $chkAlu->execute(['id' => $id]);
        if ((int)$chkAlu->fetchColumn() > 0) {
            return [false, 'No se puede eliminar: la carrera tiene alumnos.'];
        }
        $chkMat = $this->db->prepare(
            "SELECT COUNT(*) FROM Materia WHERE id_carrera = :id AND activo = 1");
        $chkMat->execute(['id' => $id]);
        if ((int)$chkMat->fetchColumn() > 0) {
            return [false, 'No se puede eliminar: la carrera tiene materias (con profesores/comisiones).'];
        }
        $stmt = $this->db->prepare("UPDATE Carrera SET activo = 0 WHERE id_carrera = :id");
        $stmt->execute(['id' => $id]);
        return [true, 'Carrera eliminada.'];
    }
}
