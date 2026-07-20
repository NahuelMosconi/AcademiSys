<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase Usuario — login por DNI, gestión de usuarios (admin) y cambio de contraseña.
 * Un usuario profesor está vinculado a un Docente (id_docente).
 */
class Usuario
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    /**
     * Login por DNI. Devuelve los datos del usuario (incluido id_docente) o null.
     */
    public function login(string $dni, string $clave): ?array
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.dni, u.email, u.password_hash,
                       u.id_docente, u.id_alumno, r.nombre AS rol
                FROM Usuario u
                JOIN Rol r ON r.id_rol = u.id_rol
                WHERE u.dni = :dni AND u.activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['dni' => $dni]);
        $usuario = $stmt->fetch();

        if (!$usuario) return null;
        if (!password_verify($clave, $usuario['password_hash'])) return null;

        unset($usuario['password_hash']);   // nunca devolvemos el hash
        return $usuario;
    }

    /** Lista todos los usuarios (para el panel admin), con su rol y docente vinculado. */
    public function listar(): array
    {
        $sql = "SELECT u.id_usuario, u.nombre, u.dni, u.email, u.activo,
                       r.nombre AS rol, d.nombre AS docente, al.nombre AS alumno
                FROM Usuario u
                JOIN Rol r ON r.id_rol = u.id_rol
                LEFT JOIN Docente d ON d.id_docente = u.id_docente
                LEFT JOIN Alumno  al ON al.id_alumno = u.id_alumno
                ORDER BY u.activo DESC, u.nombre";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * CREATE (admin) — Crea un usuario. La contraseña inicial es el DNI.
     * Si es profesor (rol 2), se vincula a un docente.
     * Devuelve [exito, mensaje].
     */
    public function crear(string $nombre, string $dni, string $email, int $idRol,
                          ?int $idDocente, ?int $idAlumno = null): array
    {
        try {
            // La contraseña inicial es el propio DNI, hasheado.
            $hash = password_hash($dni, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare(
                "INSERT INTO Usuario (nombre, dni, email, password_hash, id_rol, id_docente, id_alumno)
                 VALUES (:n, :d, :e, :h, :r, :doc, :al)");
            $stmt->execute(['n'=>$nombre, 'd'=>$dni, 'e'=>$email, 'h'=>$hash,
                            'r'=>$idRol, 'doc'=>$idDocente, 'al'=>$idAlumno]);
            return [true, 'Usuario creado. La contraseña inicial es el DNI.'];
        } catch (PDOException $e) {
            $m = $e->getMessage();
            if (strpos($m,'dni')!==false)       return [false, 'Ese DNI ya tiene usuario.'];
            if (strpos($m,'email')!==false)     return [false, 'Ese email ya está en uso.'];
            return [false, 'No se pudo crear: ' . $m];
        }
    }

    /** Alumnos que todavía NO tienen usuario (para el combo al crear usuario-alumno). */
    public function alumnosSinUsuario(): array
    {
        return $this->db->query(
            "SELECT a.id_alumno, a.legajo, a.nombre, a.dni
             FROM Alumno a
             WHERE a.activo = 1
               AND a.id_alumno NOT IN (SELECT id_alumno FROM Usuario WHERE id_alumno IS NOT NULL)
             ORDER BY a.nombre")->fetchAll();
    }

    /** Baja lógica de un usuario. */
    public function darDeBaja(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE Usuario SET activo = 0 WHERE id_usuario = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Cambio de contraseña (desde el panel del propio usuario).
     * Verifica la contraseña actual antes de cambiarla. Devuelve [exito, mensaje].
     */
    public function cambiarPassword(int $idUsuario, string $actual, string $nueva): array
    {
        // Traemos el hash actual
        $stmt = $this->db->prepare("SELECT password_hash FROM Usuario WHERE id_usuario = :id");
        $stmt->execute(['id' => $idUsuario]);
        $hashActual = $stmt->fetchColumn();

        if (!$hashActual || !password_verify($actual, $hashActual)) {
            return [false, 'La contraseña actual no es correcta.'];
        }
        if (strlen($nueva) < 4) {
            return [false, 'La nueva contraseña es demasiado corta.'];
        }
        $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
        $up = $this->db->prepare("UPDATE Usuario SET password_hash = :h WHERE id_usuario = :id");
        $up->execute(['h' => $nuevoHash, 'id' => $idUsuario]);
        return [true, 'Contraseña actualizada correctamente.'];
    }
}
