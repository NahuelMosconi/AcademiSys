<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/Usuario.php';

/**
 * Clase Docente — ABM de docentes (con DNI único) y baja lógica.
 * Al dar de alta un docente se le crea automáticamente su cuenta de acceso
 * (rol Profesor, login y contraseña iniciales = DNI), para que pueda ingresar.
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

    /**
     * CREATE — Da de alta un docente Y su cuenta de acceso, todo en una transacción.
     * El DNI y el email son únicos (lo valida la base). Si algo falla (por ejemplo
     * un DNI/email repetido en Docente o en Usuario), se revierte todo y se relanza
     * la excepción para que la vista muestre el mensaje adecuado.
     */
    public function crear(string $nombre, string $dni, string $email): bool
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO Docente (nombre, dni, email) VALUES (:n, :d, :e)");
            $stmt->execute(['n' => $nombre, 'd' => $dni, 'e' => $email]);
            $idDocente = (int) $this->db->lastInsertId();
            // Cuenta de acceso del docente (rol Profesor). Ya puede ingresar con su DNI.
            Usuario::provisionarAcceso($this->db, $nombre, $dni, $email, 'Profesor', $idDocente, null);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;   // la vista (docentes.php) traduce el mensaje
        }
    }

    /**
     * DELETE lógico — Marca el docente como inactivo (no lo borra, conserva el historial)
     * y desactiva su cuenta de acceso para que no pueda seguir ingresando.
     */
    public function darDeBaja(int $id): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE Docente SET activo = 0 WHERE id_docente = :id")
                     ->execute(['id' => $id]);
            $this->db->prepare("UPDATE Usuario SET activo = 0 WHERE id_docente = :id")
                     ->execute(['id' => $id]);
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
