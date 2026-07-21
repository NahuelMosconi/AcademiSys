<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase PeriodoLectivo — los cuatrimestres/semestres (genéricos).
 * El AÑO no vive acá: lo pone el ciclo lectivo (CicloLectivo). El período solo
 * indica el tramo del año: "1er Cuatrimestre", "2do Cuatrimestre".
 */
class PeriodoLectivo
{
    private PDO $db;

    public function __construct() { $this->db = Database::conectar(); }

    /** READ — Lista los períodos (para el combo al crear una comisión). */
    public function listar(): array
    {
        return $this->db->query(
            "SELECT * FROM PeriodoLectivo ORDER BY id_periodo")->fetchAll();
    }

    /** CREATE — Da de alta un período (cuatrimestre). */
    public function crear(string $nombre): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO PeriodoLectivo (nombre) VALUES (:n)");
        return $stmt->execute(['n' => $nombre]);
    }
}
