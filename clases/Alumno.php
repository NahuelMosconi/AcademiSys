<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Alumno — CRUD con baja lógica.
 * Campos: legajo, nombre, dni, telefono, email (todos únicos salvo nombre).
 */
class Alumno
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    /**
     * READ — Lista alumnos activos. Si llega $filtro, busca por nombre, legajo,
     * dni, teléfono o email (búsqueda parcial con LIKE).
     */
    public function listar(string $filtro = ''): array
    {
        if ($filtro !== '') {
            // Usamos un placeholder por columna (PDO sin emulación no permite repetir :f).
            // JOIN con Carrera para traer el nombre de la carrera además de los datos del alumno.
            $sql = "SELECT a.*, c.nombre AS carrera
                    FROM Alumno a
                    JOIN Carrera c ON c.id_carrera = a.id_carrera
                    WHERE a.activo = 1 AND (
                        a.nombre LIKE :f1 OR a.legajo LIKE :f2 OR
                        a.dni LIKE :f3 OR a.telefono LIKE :f4 OR a.email LIKE :f5)
                    ORDER BY a.nombre LIMIT 300";
            $stmt = $this->db->prepare($sql);
            $like = "%$filtro%";
            $stmt->execute(['f1'=>$like,'f2'=>$like,'f3'=>$like,'f4'=>$like,'f5'=>$like]);
            return $stmt->fetchAll();
        }
        // Sin filtro: también traemos el nombre de la carrera con un JOIN.
        return $this->db->query(
            "SELECT a.*, c.nombre AS carrera
             FROM Alumno a
             JOIN Carrera c ON c.id_carrera = a.id_carrera
             WHERE a.activo = 1 ORDER BY a.nombre LIMIT 300")->fetchAll();
    }

    /** READ — Para los combos (solo id, legajo y nombre). */
    public function listarParaCombo(): array
    {
        return $this->db->query(
            "SELECT id_alumno, legajo, nombre FROM Alumno WHERE activo = 1 ORDER BY nombre")->fetchAll();
    }

    /** READ — Cuenta alumnos activos (dashboard). */
    public function contar(): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM Alumno WHERE activo = 1")->fetchColumn();
    }

    /** READ — Busca un alumno por id (para editar). */
    public function buscar(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Alumno WHERE id_alumno = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /** CREATE — Inserta un alumno. Los UNIQUE (legajo, dni, tel, email) evitan duplicados. */
    public function crear(string $legajo, string $nombre, string $dni, string $tel, string $email, int $idCarrera): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Alumno (legajo, nombre, dni, telefono, email, id_carrera)
             VALUES (:l, :n, :d, :t, :e, :c)");
        return $stmt->execute(['l'=>$legajo, 'n'=>$nombre, 'd'=>$dni, 't'=>$tel, 'e'=>$email, 'c'=>$idCarrera]);
    }

    /** UPDATE — Modifica un alumno existente. */
    public function actualizar(int $id, string $legajo, string $nombre, string $dni, string $tel, string $email, int $idCarrera): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Alumno SET legajo=:l, nombre=:n, dni=:d, telefono=:t, email=:e, id_carrera=:c
             WHERE id_alumno=:id");
        return $stmt->execute(['l'=>$legajo, 'n'=>$nombre, 'd'=>$dni, 't'=>$tel, 'e'=>$email, 'c'=>$idCarrera, 'id'=>$id]);
    }

    /** DELETE lógico — marca inactivo (conserva el historial). */
    public function darDeBaja(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE Alumno SET activo = 0 WHERE id_alumno = :id");
        return $stmt->execute(['id' => $id]);
    }
}
