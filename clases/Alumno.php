<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Usuario.php';

/**
 * Clase Alumno — CRUD con baja lógica.
 * Campos: legajo, nombre, dni, telefono, email (todos únicos salvo nombre).
 * Al dar de alta un alumno se le crea automáticamente su cuenta de acceso
 * (rol Alumno, login y contraseña iniciales = DNI), para que pueda ingresar.
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

    /**
     * CREATE — Inserta un alumno Y su cuenta de acceso, todo en una transacción.
     * Los UNIQUE (legajo, dni, tel, email) evitan duplicados. Si algo falla se
     * revierte todo y se relanza la excepción para que la vista muestre el motivo.
     */
    public function crear(string $legajo, string $nombre, string $dni, string $tel, string $email, int $idCarrera): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO Alumno (legajo, nombre, dni, telefono, email, id_carrera)
                 VALUES (:l, :n, :d, :t, :e, :c)");
            $stmt->execute(['l'=>$legajo, 'n'=>$nombre, 'd'=>$dni, 't'=>$tel, 'e'=>$email, 'c'=>$idCarrera]);
            $idAlumno = (int) $this->db->lastInsertId();
            // Cuenta de acceso del alumno (rol Alumno). Ya puede ingresar con su DNI.
            Usuario::provisionarAcceso($this->db, $nombre, $dni, $email, 'Alumno', null, $idAlumno);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;   // la vista (alumno_form.php) traduce el mensaje
        }
    }

    /**
     * UPDATE — Modifica un alumno existente y mantiene sincronizada su cuenta de
     * acceso (nombre, DNI y email), para que el login por DNI no se rompa si el
     * admin corrige esos datos. No toca la contraseña ya elegida por el alumno.
     */
    public function actualizar(int $id, string $legajo, string $nombre, string $dni, string $tel, string $email, int $idCarrera): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare(
                "UPDATE Alumno SET legajo=:l, nombre=:n, dni=:d, telefono=:t, email=:e, id_carrera=:c
                 WHERE id_alumno=:id")
                ->execute(['l'=>$legajo, 'n'=>$nombre, 'd'=>$dni, 't'=>$tel, 'e'=>$email, 'c'=>$idCarrera, 'id'=>$id]);
            $this->db->prepare(
                "UPDATE Usuario SET nombre=:n, dni=:d, email=:e WHERE id_alumno=:id")
                ->execute(['n'=>$nombre, 'd'=>$dni, 'e'=>$email, 'id'=>$id]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;   // la vista (alumno_form.php) traduce el mensaje
        }
    }

    /**
     * DELETE lógico — marca inactivo (conserva el historial) y desactiva su cuenta
     * de acceso para que no pueda seguir ingresando.
     */
    public function darDeBaja(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE Alumno SET activo = 0 WHERE id_alumno = :id")
                     ->execute(['id' => $id]);
            $this->db->prepare("UPDATE Usuario SET activo = 0 WHERE id_alumno = :id")
                     ->execute(['id' => $id]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
