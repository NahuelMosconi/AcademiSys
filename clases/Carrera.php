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
}
