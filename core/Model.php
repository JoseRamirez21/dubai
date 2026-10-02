<?php
/**
 * Model: clase base (abstracta) de todos los modelos.
 *
 * Rol del modelo en MVC: es el ÚNICO que habla con la base de datos.
 * Todas las consultas usan sentencias preparadas (evita inyección SQL).
 */
abstract class Model
{
    // Conexión PDO compartida (viene del Singleton del Paso 4)
    protected PDO $db;

    // Cada modelo hijo indica su tabla. Ejemplo: protected string $tabla = 'productos';
    // Va en el código (no la escribe el usuario), por eso es seguro usarla en el SQL.
    protected string $tabla = '';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Prepara y ejecuta una consulta con parámetros.
     * Ejemplo: $this->ejecutar('SELECT * FROM x WHERE id = :id', [':id' => 5]);
     * Los datos viajan APARTE de la consulta: así no se puede inyectar SQL.
     */
    protected function ejecutar(string $sql, array $parametros = []): PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    }

    /**
     * Devuelve todos los registros de la tabla.
     */
    public function todos(): array
    {
        return $this->ejecutar("SELECT * FROM {$this->tabla}")->fetchAll();
    }

    /**
     * Busca un registro por su id. Devuelve null si no existe.
     */
    public function buscar(int $id): ?array
    {
        $fila = $this->ejecutar(
            "SELECT * FROM {$this->tabla} WHERE id = :id",
            [':id' => $id]
        )->fetch();

        return $fila ?: null;
    }

    /**
     * Elimina un registro por su id. Devuelve true si eliminó algo.
     */
    public function eliminar(int $id): bool
    {
        $stmt = $this->ejecutar(
            "DELETE FROM {$this->tabla} WHERE id = :id",
            [':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }
}