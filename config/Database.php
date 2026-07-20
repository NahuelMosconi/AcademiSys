<?php
/**
 * Clase Database - conexión única a MySQL usando PDO (patrón Singleton).
 * Singleton = una sola instancia de la conexión para toda la aplicación.
 */
class Database
{
    private static ?PDO $conexion = null;   // guarda la única conexión

    // Datos de conexión a MySQL (usuario con permisos sobre la base academisys).
    private const HOST = 'localhost';
    private const BASE = 'academisys';
    private const USER = 'nahuel';
    private const PASS = 'root';

    // Constructor privado: nadie puede hacer "new Database()" desde afuera
    private function __construct() {}

    /** Devuelve la conexión PDO. Si no existe, la crea una sola vez. */
    public static function conectar(): PDO
    {
        if (self::$conexion === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::BASE . ';charset=utf8mb4';
            try {
                self::$conexion = new PDO($dsn, self::USER, self::PASS, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // los errores lanzan excepción
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // filas como arrays asociativos
                    PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statements reales
                ]);
            } catch (PDOException $e) {
                die('Error de conexión: ' . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
