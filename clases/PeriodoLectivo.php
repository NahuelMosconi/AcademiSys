<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase PeriodoLectivo — ABM de períodos lectivos (cuatrimestres/años).
 * Cada comisión pertenece a un período (ej: "1er Cuatrimestre 2025").
 */
class PeriodoLectivo
{
    private PDO $db;

    public function __construct() { $this->db = Database::conectar(); }

    /** READ — Lista los períodos (para el combo al crear una comisión). */
    public function listar(): array
    {
        return $this->db->query(
            "SELECT * FROM PeriodoLectivo ORDER BY anio DESC, nombre")->fetchAll();
    }

    /** CREATE — Da de alta un período lectivo. */
    public function crear(string $nombre, int $anio): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO PeriodoLectivo (nombre, anio) VALUES (:n, :a)");
        return $stmt->execute(['n' => $nombre, 'a' => $anio]);
    }
}
