<?php
/**
 * Database: crea y entrega la conexión a MySQL usando PDO.
 *
 * Patrón Singleton: la conexión se abre UNA sola vez y se reutiliza.
 * Los datos de conexión vienen de config.php (no hay credenciales aquí).
 */
class Database
{
    // Guarda la única conexión. "static" = pertenece a la clase, no a un objeto.
    private static ?PDO $conexion = null;

    // Constructor privado: impide crear objetos con "new Database()".
    private function __construct()
    {
    }

    // Impide clonar la clase (otra forma de crear una segunda instancia).
    private function __clone()
    {
    }

    /**
     * Devuelve la conexión. Si aún no existe, la crea.
     */
    public static function getConnection(): PDO
    {
        if (self::$conexion === null) {

            // DSN = "dirección" de la base de datos
            $dsn = 'mysql:host=' . DB_HOST
                 . ';dbname=' . DB_NAME
                 . ';charset=' . DB_CHARSET;

            $opciones = [
                // Si una consulta falla, lanza una excepción (no falla en silencio)
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Los resultados vienen como arreglo asociativo: $fila['nombre']
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // false = sentencias preparadas REALES en MySQL (más seguro)
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);

                // La hora de MySQL debe coincidir con la de Lima (UTC-5)
                self::$conexion->exec("SET time_zone = '-05:00'");

            } catch (PDOException $e) {
                // El detalle real se guarda en el log del servidor, no en pantalla
                error_log('Error de conexión a la BD: ' . $e->getMessage());

                http_response_code(500);

                // SEGURIDAD: al usuario final NO se le muestran datos técnicos
                if (APP_DEBUG) {
                    exit('Error de conexión: ' . htmlspecialchars($e->getMessage()));
                }
                exit('El sistema no está disponible en este momento.');
            }
        }

        return self::$conexion;
    }
}