<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Aula — ABM de aulas.
 * El campo cupo_maximo es clave: el motor de inscripciones lo usa como tope
 * para la Regla 1 (no inscribir más alumnos de los que entran).
 */
class Aula
{
    private PDO $db;

    public function __construct() { $this->db = Database::conectar(); }

    /** READ — Lista todas las aulas (para combos al crear comisiones). */
    public function listar(): array
    {
        return $this->db->query("SELECT * FROM Aula ORDER BY nombre")->fetchAll();
    }

    /** CREATE — Da de alta un aula con su cupo máximo de alumnos. */
    public function crear(string $nombre, int $cupoMaximo): bool
    {
        $stmt = $this->db->prepare("INSERT INTO Aula (nombre, cupo_maximo) VALUES (:n, :c)");
        return $stmt->execute(['n' => $nombre, 'c' => $cupoMaximo]);
    }
}
