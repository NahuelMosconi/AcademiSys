<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase CicloLectivo — el AÑO académico. Un solo ciclo está ABIERTO (el actual);
 * los CERRADO son los anteriores. Cerrar el actual abre automáticamente el
 * siguiente (vía el procedimiento CerrarCicloLectivo).
 */
class CicloLectivo
{
    private PDO $db;
    public function __construct() { $this->db = Database::conectar(); }

    /** READ — Lista los ciclos con la cantidad de comisiones de cada uno. */
    public function listar(): array
    {
        return $this->db->query(
            "SELECT cl.id_ciclo, cl.anio, cl.estado, cl.fecha_apertura, cl.fecha_cierre,
                    (SELECT COUNT(*) FROM Comision c WHERE c.id_ciclo = cl.id_ciclo AND c.activo = 1) AS comisiones
             FROM CicloLectivo cl
             ORDER BY cl.anio DESC")->fetchAll();
    }

    /** READ — El ciclo actualmente abierto (o null si no hay). */
    public function actual(): ?array
    {
        $r = $this->db->query(
            "SELECT * FROM CicloLectivo WHERE estado = 'ABIERTO' ORDER BY anio DESC LIMIT 1")->fetch();
        return $r ?: null;
    }

    /**
     * Cierra el ciclo abierto y abre el del año siguiente, vía el procedimiento
     * almacenado CerrarCicloLectivo (transacción ACID). Devuelve [exito, mensaje].
     */
    public function cerrarCiclo(): array
    {
        try {
            // ===== [USA PROCEDIMIENTO ALMACENADO: CerrarCicloLectivo] =====
            $this->db->query("CALL CerrarCicloLectivo()");
            return [true, 'Ciclo lectivo cerrado. Se abrió automáticamente el año siguiente.'];
        } catch (PDOException $e) {
            $m = $e->getMessage();
            if (strpos($m, 'no hay un ciclo') !== false || strpos($m, 'No hay un ciclo') !== false) {
                return [false, 'No hay un ciclo lectivo abierto para cerrar.'];
            }
            return [false, 'No se pudo cerrar el ciclo: ' . $m];
        }
    }
}
