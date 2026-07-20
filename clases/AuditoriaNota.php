<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Clase AuditoriaNota — solo lectura del registro de auditoría de notas.
 * La tabla la llena automáticamente el trigger tr_auditar_nota cada vez que
 * se inserta una nota en Acta. Acá solo la consultamos para mostrarla.
 */
class AuditoriaNota
{
    private PDO $db;

    public function __construct() { $this->db = Database::conectar(); }

    /** READ — Lista las últimas notas auditadas, con nombre de alumno y materia. */
    public function listar(): array
    {
        return $this->db->query(
            "SELECT an.id_auditoria, al.nombre AS alumno, m.nombre AS materia,
                    an.nota, an.fecha_registro
             FROM AuditoriaNota an
             JOIN Alumno al ON al.id_alumno = an.id_alumno
             JOIN Materia m ON m.id_materia = an.id_materia
             ORDER BY an.fecha_registro DESC LIMIT 50")->fetchAll();
    }
}
