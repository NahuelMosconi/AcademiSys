<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Docente — ABM de docentes (con DNI único) y baja lógica.
 * Un docente se vincula a un Usuario cuando se le crea cuenta de acceso (rol Profesor).
 */
class Docente
{
    private PDO $db;

    public function __construct() { $this->db = Database::conectar(); }

    /**
     * READ — Lista docentes activos. Con filtro opcional busca por nombre, DNI o email.
     * Usa un placeholder por columna (:f1, :f2, :f3) porque PDO sin emulación no
     * permite repetir el mismo nombre de placeholder.
     */
    public function listar(string $filtro = ''): array
    {
        if ($filtro !== '') {
            $stmt = $this->db->prepare(
                "SELECT * FROM Docente WHERE activo = 1
                 AND (nombre LIKE :f1 OR dni LIKE :f2 OR email LIKE :f3)
                 ORDER BY nombre");
            $like = "%$filtro%";   // %texto% = "que contenga el texto"
            $stmt->execute(['f1'=>$like,'f2'=>$like,'f3'=>$like]);
            return $stmt->fetchAll();
        }
        return $this->db->query("SELECT * FROM Docente WHERE activo = 1 ORDER BY nombre")->fetchAll();
    }

    /** CREATE — Da de alta un docente. El DNI y el email son únicos (lo valida la base). */
    public function crear(string $nombre, string $dni, string $email): bool
    {
        $stmt = $this->db->prepare("INSERT INTO Docente (nombre, dni, email) VALUES (:n, :d, :e)");
        return $stmt->execute(['n' => $nombre, 'd' => $dni, 'e' => $email]);
    }

    /** DELETE lógico — Marca el docente como inactivo (no lo borra, conserva el historial). */
    public function darDeBaja(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE Docente SET activo = 0 WHERE id_docente = :id");
        return $stmt->execute(['id' => $id]);
    }
}
